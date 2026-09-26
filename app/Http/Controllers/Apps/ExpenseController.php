<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $businessId = $request->user()?->business_id;

        $query = Expense::query()
            ->when($businessId, fn ($q) => $q->where('business_id', $businessId))
            ->with(['category', 'creator:id,name', 'approvedBy:id,name', 'rejectedBy:id,name'])
            ->orderByDesc('expense_date')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('expense_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('payment_reference', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $expenses = $query->paginate($this->perPage())->withQueryString();

        return Inertia::render('Dashboard/Expenses/Index', [
            'expenses' => $expenses,
            'categories' => ExpenseCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['search', 'status', 'category_id', 'payment_method', 'warehouse_id']),
            'summary' => [
                'today' => Expense::whereDate('expense_date', today())->sum('amount'),
                'pending' => Expense::where('status', 'submitted')->count(),
                'approved_today' => Expense::whereDate('expense_date', today())->where('status', 'approved')->sum('amount'),
                'this_month' => Expense::whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->sum('amount'),
            ],
        ]);
    }

    public function show(Request $request, Expense $expense)
    {
        if ($request->user()?->business_id && $expense->business_id !== $request->user()->business_id) {
            abort(403, 'You do not have access to this expense.');
        }

        $expense->load(['category', 'creator', 'submittedBy', 'approvedBy', 'rejectedBy', 'warehouse']);

        return Inertia::render('Dashboard/Expenses/Show', [
            'expense' => $expense,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'in:cash,bank_transfer,mpesa,card,petty_cash'],
            'description' => ['required', 'string', 'max:500'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $category = ExpenseCategory::query()->findOrFail($validated['expense_category_id']);

        if ($request->user()?->business_id && $category->business_id && $category->business_id !== $request->user()->business_id) {
            return back()->withErrors(['expense_category_id' => 'Kategori pengeluaran tidak sesuai dengan bisnis Anda.'])->withInput();
        }

        if (! $category->is_active) {
            return back()->withErrors(['expense_category_id' => 'Kategori pengeluaran tidak aktif.'])->withInput();
        }

        $expense = DB::transaction(function () use ($request, $validated, $category) {
            $amount = (int) $validated['amount'];

            $expense = Expense::create([
                'business_id' => $request->user()->business_id,
                'store_id' => null,
                'warehouse_id' => $validated['warehouse_id'] ?? null,
                'cashier_shift_id' => null,
                'expense_category_id' => $category->id,
                'created_by' => $request->user()->id,
                'amount' => $amount,
                'currency' => 'KES',
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['reference_number'] ?? null,
                'description' => $validated['description'],
                'expense_date' => $validated['expense_date'],
                'status' => 'submitted',
                'submitted_by' => $request->user()->id,
                'submitted_at' => now(),
                'cash_effect' => $validated['payment_method'] === 'cash' ? $amount : 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            return $expense;
        });

        return redirect()->route('expenses.show', $expense)->with('success', 'Expense berhasil disubmit untuk review.');
    }

    public function approve(Request $request, Expense $expense): RedirectResponse
    {
        if ($request->user()?->business_id && $expense->business_id !== $request->user()->business_id) {
            abort(403, 'You do not have access to this expense.');
        }

        if ($request->user()->id === $expense->created_by) {
            return back()->with('error', 'Anda tidak dapat menyetujui pengeluaran sendiri.');
        }

        $expense->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => null,
            'rejected_by' => null,
            'rejected_at' => null,
        ]);

        return redirect()->route('expenses.show', $expense)->with('success', 'Pengeluaran disetujui.');
    }

    public function reject(Request $request, Expense $expense): RedirectResponse
    {
        if ($request->user()?->business_id && $expense->business_id !== $request->user()->business_id) {
            abort(403, 'You do not have access to this expense.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $expense->update([
            'status' => 'rejected',
            'rejected_by' => $request->user()->id,
            'rejected_at' => now(),
            'rejection_reason' => $validated['reason'],
        ]);

        return redirect()->route('expenses.show', $expense)->with('success', 'Pengeluaran ditolak.');
    }

    public function cancel(Request $request, Expense $expense): RedirectResponse
    {
        if ($request->user()?->business_id && $expense->business_id !== $request->user()->business_id) {
            abort(403, 'You do not have access to this expense.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $expense->update([
            'status' => 'cancelled',
            'cancelled_by' => $request->user()->id,
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['reason'],
        ]);

        return redirect()->route('expenses.show', $expense)->with('success', 'Pengeluaran dibatalkan.');
    }
}
