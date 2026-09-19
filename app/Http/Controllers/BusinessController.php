<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->input('search')),
            'status' => $request->input('status'),
        ];

        $businesses = Business::query()
            ->with('owner:id,name,email')
            ->withCount('users')
            ->when($filters['search'], function ($query, $search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Platform/Businesses/Index', [
            'businesses' => $businesses,
            'filters' => $filters,
            'statuses' => ['pending', 'active', 'suspended', 'inactive', 'archived'],
        ]);
    }

    public function show(Business $business)
    {
        $business->load([
            'owner:id,name,email,last_login_at,is_active',
            'users:id,business_id,name,email,is_active,last_login_at',
        ]);

        $userIds = $business->users->pluck('id');

        return Inertia::render('Platform/Businesses/Show', [
            'business' => $business,
            'stats' => [
                'users' => $business->users->count(),
                'active_users' => $business->users->where('is_active', true)->count(),
                'transactions' => Transaction::whereIn('cashier_id', $userIds)->count(),
                'sales' => (int) Transaction::whereIn('cashier_id', $userIds)->sum('grand_total'),
            ],
            'activity' => $business->users->flatMap(fn ($user) => $user->auditLogs()->latest()->limit(5)->get())->sortByDesc('created_at')->values(),
        ]);
    }

    public function store(Request $request)
    {
        $business = Business::create($this->validated($request));

        return to_route('platform.businesses.show', $business)->with('success', 'Business created.');
    }

    public function update(Request $request, Business $business)
    {
        $business->update($this->validated($request, $business));

        return back()->with('success', 'Business updated.');
    }

    public function status(Request $request, Business $business)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'active', 'suspended', 'inactive', 'archived'])],
        ]);

        $business->update([
            'status' => $validated['status'],
            'suspended_at' => $validated['status'] === 'suspended' ? now() : null,
            'archived_at' => $validated['status'] === 'archived' ? now() : null,
        ]);

        return back()->with('success', 'Business status updated.');
    }

    private function validated(Request $request, ?Business $business = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:100', Rule::unique('businesses', 'registration_number')->ignore($business?->id)],
            'tax_pin' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'country' => ['required', 'string', 'size:2'],
            'currency' => ['required', 'string', 'size:3'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', Rule::in(['pending', 'active', 'suspended', 'inactive', 'archived'])],
        ]);
    }
}
