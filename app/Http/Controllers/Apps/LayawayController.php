<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Layaway;
use App\Services\LayawayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LayawayController extends Controller
{
    public function __construct(
        protected readonly LayawayService $layawayService,
    ) {}

    public function index(Request $request)
    {
        $businessId = $request->user()?->business_id;

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
            'customer_id' => $request->query('customer_id'),
            'user_id' => $request->query('user_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'balance_filter' => $request->query('balance_filter'),
        ];

        $query = Layaway::query()
            ->when($businessId, fn ($q) => $q->where('business_id', $businessId))
            ->with(['customer:id,name,no_telp', 'user:id,name', 'items.product:id,title,sku,barcode'])
            ->withSum('payments as total_paid', 'amount')
            ->orderByDesc('created_at');

        $query->when($filters['search'], function ($q, $search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('reference', 'like', '%'.$search.'%')
                    ->orWhereHas('customer', fn ($customerQuery) => $customerQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('no_telp', 'like', '%'.$search.'%'))
                    ->orWhereHas('items.product', fn ($productQuery) => $productQuery
                        ->where('title', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhere('barcode', 'like', '%'.$search.'%'));
            });
        })->when($filters['status'], function ($q, $status) {
            if ($status === 'partially_paid') {
                $q->where('status', 'partial');
            } else {
                $q->where('status', $status);
            }
        })->when($filters['customer_id'], fn ($q, $customerId) => $q->where('customer_id', $customerId))
          ->when($filters['user_id'], fn ($q, $userId) => $q->where('user_id', $userId))
          ->when($filters['date_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
          ->when($filters['date_to'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
          ->when($filters['balance_filter'], function ($q, $balanceFilter) {
              if ($balanceFilter === 'has_balance') {
                  $q->where('balance_amount', '>', 0);
              }
              if ($balanceFilter === 'fully_paid') {
                  $q->where('balance_amount', 0)->where('status', 'paid');
              }
          });

        $layaways = $query->paginate($this->perPage())->withQueryString();

        $summary = [
            'total' => Layaway::query()->when($businessId, fn ($q) => $q->where('business_id', $businessId))->count(),
            'partially_paid' => Layaway::query()->when($businessId, fn ($q) => $q->where('business_id', $businessId))->where('status', 'partial')->count(),
            'ready_for_release' => Layaway::query()->when($businessId, fn ($q) => $q->where('business_id', $businessId))->where('status', 'paid')->count(),
            'completed' => Layaway::query()->when($businessId, fn ($q) => $q->where('business_id', $businessId))->where('status', 'released')->count(),
            'cancelled' => Layaway::query()->when($businessId, fn ($q) => $q->where('business_id', $businessId))->where('status', 'cancelled')->count(),
            'outstanding_balance' => Layaway::query()->when($businessId, fn ($q) => $q->where('business_id', $businessId))->whereIn('status', ['partial', 'paid'])->sum('balance_amount'),
        ];

        return Inertia::render('Dashboard/Layaways/Index', [
            'layaways' => $layaways,
            'filters' => $filters,
            'summary' => $summary,
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name', 'no_telp']),
            'products' => \App\Models\Product::query()
                ->where('stock', '>', 0)
                ->orderBy('title')
                ->get(['id', 'title', 'sku', 'sell_price', 'stock']),
        ]);
    }

    public function show(Request $request, Layaway $layaway)
    {
        if ($request->user()?->business_id && $layaway->business_id !== $request->user()->business_id) {
            abort(403, 'You do not have access to this layaway.');
        }

        $layaway->load(['customer:id,name,no_telp', 'user:id,name', 'items.product:id,title,sku,barcode', 'payments.user:id,name']);

        return Inertia::render('Dashboard/Layaways/Show', [
            'layaway' => $layaway,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'integer', 'min:1'],
            'initial_payment' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        try {
            $layaway = $this->layawayService->createLayaway(
                $request->user(),
                $customer,
                $validated['items'],
                (int) $validated['initial_payment'],
                $validated['payment_method'],
                $validated['payment_reference'] ?? null,
                $validated['notes'] ?? null,
            );

            return redirect()->route('layaways.show', $layaway)->with('success', 'Layaway berhasil dibuat.');
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function storePayment(Request $request, Layaway $layaway): RedirectResponse
    {
        if ($request->user()?->business_id && $layaway->business_id !== $request->user()->business_id) {
            abort(403, 'You do not have access to this layaway.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $payment = $this->layawayService->addPayment(
                $layaway,
                (int) $validated['amount'],
                $validated['payment_method'],
                $validated['payment_reference'] ?? null,
                $validated['notes'] ?? null,
                $request->user(),
            );

            return redirect()->route('layaways.show', $layaway)->with('success', 'Pembayaran layaway berhasil dicatat.');
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function release(Request $request, Layaway $layaway): RedirectResponse
    {
        if ($request->user()?->business_id && $layaway->business_id !== $request->user()->business_id) {
            abort(403, 'You do not have access to this layaway.');
        }

        try {
            $this->layawayService->release($layaway, $request->user());

            return redirect()->route('layaways.show', $layaway)->with('success', 'Barang layaway telah dirilis.');
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function cancel(Request $request, Layaway $layaway): RedirectResponse
    {
        if ($request->user()?->business_id && $layaway->business_id !== $request->user()->business_id) {
            abort(403, 'You do not have access to this layaway.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->layawayService->cancel($layaway, $request->user(), $validated['reason']);

            return redirect()->route('layaways.show', $layaway)->with('success', 'Layaway dibatalkan.');
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}
