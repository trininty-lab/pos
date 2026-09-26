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

    public function index()
    {
        $layaways = Layaway::with(['customer:id,name,no_telp', 'user:id,name', 'items.product:id,title'])
            ->latest('created_at')
            ->paginate(20);

        return Inertia::render('Dashboard/Layaways/Index', [
            'layaways' => $layaways,
        ]);
    }

    public function show(Layaway $layaway)
    {
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
        try {
            $this->layawayService->release($layaway, $request->user());

            return redirect()->route('layaways.show', $layaway)->with('success', 'Barang layaway telah dirilis.');
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function cancel(Request $request, Layaway $layaway): RedirectResponse
    {
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
