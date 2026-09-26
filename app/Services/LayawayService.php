<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Layaway;
use App\Models\LayawayItem;
use App\Models\LayawayPayment;
use App\Models\Product;
use App\Models\StockMutation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LayawayService
{
    public function createLayaway(User $user, Customer $customer, array $items, int $initialPayment, string $paymentMethod, ?string $paymentReference = null, ?string $notes = null): Layaway
    {
        if ($initialPayment <= 0) {
            throw new InvalidArgumentException('Initial payment must be greater than zero.');
        }

        $validatedItems = [];
        $total = 0;

        foreach ($items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $qty = (int) ($item['qty'] ?? 0);
            $unitPrice = (int) ($item['unit_price'] ?? 0);

            if ($productId <= 0 || $qty <= 0 || $unitPrice <= 0) {
                throw new InvalidArgumentException('Layaway item is invalid.');
            }

            $product = Product::query()->lockForUpdate()->findOrFail($productId);
            if ($product->stock < $qty) {
                throw new InvalidArgumentException('Insufficient stock for product: '.$product->title);
            }

            $subtotal = $qty * $unitPrice;
            $total += $subtotal;

            $validatedItems[] = [
                'product_id' => $product->id,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'notes' => $item['notes'] ?? null,
            ];
        }

        if ($total <= 0) {
            throw new InvalidArgumentException('Layaway total must be greater than zero.');
        }

        if ($initialPayment > $total) {
            throw new InvalidArgumentException('Initial payment cannot exceed the total layaway amount.');
        }

        return DB::transaction(function () use ($user, $customer, $validatedItems, $initialPayment, $paymentMethod, $paymentReference, $notes, $total) {
            $layaway = Layaway::create([
                'business_id' => $user->business_id,
                'customer_id' => $customer->id,
                'user_id' => $user->id,
                'status' => 'partial',
                'total_amount' => $total,
                'paid_amount' => 0,
                'balance_amount' => $total,
                'notes' => $notes,
            ]);

            foreach ($validatedItems as $item) {
                $layaway->items()->create($item);

                $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);
                $product->decrement('stock', $item['qty']);

                StockMutation::create([
                    'product_id' => $product->id,
                    'reference_type' => 'layaway',
                    'reference_id' => $layaway->id,
                    'mutation_type' => 'out',
                    'qty' => $item['qty'],
                    'stock_before' => (int) ($product->stock + $item['qty']),
                    'stock_after' => (int) $product->fresh()->stock,
                    'notes' => 'Layaway reservation for '.$layaway->reference,
                    'created_by' => $user->id,
                ]);
            }

            $this->addPayment(
                $layaway,
                $initialPayment,
                $paymentMethod,
                $paymentReference,
                'Initial layaway payment',
                $user
            );

            return $layaway->fresh(['items.product', 'payments.user', 'customer']);
        });
    }

    public function addPayment(Layaway $layaway, int $amount, string $paymentMethod, ?string $paymentReference = null, ?string $notes = null, ?User $user = null): LayawayPayment
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        if (! in_array($layaway->status, ['partial', 'paid'], true)) {
            throw new InvalidArgumentException('This layaway cannot accept additional payments.');
        }

        return DB::transaction(function () use ($layaway, $amount, $paymentMethod, $paymentReference, $notes, $user) {
            $lockedLayaway = Layaway::query()->lockForUpdate()->findOrFail($layaway->id);
            $remaining = max(0, $lockedLayaway->total_amount - $lockedLayaway->paid_amount);

            if ($amount > $remaining) {
                throw new InvalidArgumentException('Payment exceeds the outstanding layaway balance.');
            }

            $newPaid = $lockedLayaway->paid_amount + $amount;
            $newBalance = max(0, $lockedLayaway->total_amount - $newPaid);

            $lockedLayaway->update([
                'paid_amount' => $newPaid,
                'balance_amount' => $newBalance,
                'status' => $newBalance > 0 ? 'partial' : 'paid',
                'paid_at' => $newBalance === 0 ? now() : $lockedLayaway->paid_at,
            ]);

            $payment = $lockedLayaway->payments()->create([
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference,
                'user_id' => $user?->id ?? auth()->id(),
                'paid_at' => now(),
                'balance_after_payment' => $newBalance,
                'notes' => $notes,
            ]);

            return $payment;
        });
    }

    public function release(Layaway $layaway, User $user): Layaway
    {
        return DB::transaction(function () use ($layaway, $user) {
            $lockedLayaway = Layaway::query()->lockForUpdate()->findOrFail($layaway->id);

            if ($lockedLayaway->balance_amount > 0) {
                throw new InvalidArgumentException('Layaway cannot be released before the balance reaches zero.');
            }

            if (in_array($lockedLayaway->status, ['released', 'cancelled'], true)) {
                throw new InvalidArgumentException('This layaway is not eligible for release.');
            }

            $lockedLayaway->update([
                'status' => 'released',
                'released_at' => now(),
            ]);

            foreach ($lockedLayaway->items as $item) {
                $product = $item->product;
                if (! $product) {
                    continue;
                }

                StockMutation::create([
                    'product_id' => $product->id,
                    'reference_type' => 'layaway_release',
                    'reference_id' => $lockedLayaway->id,
                    'mutation_type' => 'out',
                    'qty' => $item->qty,
                    'stock_before' => (int) $product->stock,
                    'stock_after' => max(0, (int) $product->stock - $item->qty),
                    'notes' => 'Product released after layaway completion for '.$lockedLayaway->reference,
                    'created_by' => $user->id,
                ]);

                $product->decrement('stock', $item->qty);
            }

            return $lockedLayaway->fresh(['items.product', 'payments.user', 'customer']);
        });
    }

    public function cancel(Layaway $layaway, User $user, string $reason): Layaway
    {
        return DB::transaction(function () use ($layaway, $user, $reason) {
            $lockedLayaway = Layaway::query()->lockForUpdate()->findOrFail($layaway->id);

            if (in_array($lockedLayaway->status, ['cancelled'], true)) {
                throw new InvalidArgumentException('This layaway is already cancelled.');
            }

            foreach ($lockedLayaway->items as $item) {
                $product = $item->product;
                if (! $product) {
                    continue;
                }

                $product->increment('stock', $item->qty);

                StockMutation::create([
                    'product_id' => $product->id,
                    'reference_type' => 'layaway_cancel',
                    'reference_id' => $lockedLayaway->id,
                    'mutation_type' => 'in',
                    'qty' => $item->qty,
                    'stock_before' => (int) $product->stock - $item->qty,
                    'stock_after' => (int) $product->stock,
                    'notes' => 'Reservation released on layaway cancellation for '.$lockedLayaway->reference,
                    'created_by' => $user->id,
                ]);
            }

            $lockedLayaway->update([
                'status' => 'cancelled',
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ]);

            return $lockedLayaway->fresh(['items.product', 'payments.user', 'customer']);
        });
    }
}
