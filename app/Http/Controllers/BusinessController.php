<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class BusinessController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

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

        $this->auditLogService->log(
            event: 'business.created',
            module: 'platform',
            auditable: $business,
            description: 'Business created by Super Admin.',
            after: $business->only(['name', 'owner_id', 'status']),
        );

        return to_route('platform.businesses.show', $business)->with('success', 'Business created.');
    }

    public function update(Request $request, Business $business)
    {
        $before = $business->only(['name', 'owner_id', 'status']);
        $business->update($this->validated($request, $business));

        $this->auditLogService->log(
            event: 'business.updated',
            module: 'platform',
            auditable: $business,
            description: 'Business updated by Super Admin.',
            before: $before,
            after: $business->fresh()->only(['name', 'owner_id', 'status']),
        );

        return back()->with('success', 'Business updated.');
    }

    public function status(Request $request, Business $business)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'active', 'suspended', 'inactive', 'archived'])],
        ]);

        $before = $business->only(['status', 'suspended_at', 'archived_at']);
        $business->update([
            'status' => $validated['status'],
            'suspended_at' => $validated['status'] === 'suspended' ? now() : null,
            'archived_at' => $validated['status'] === 'archived' ? now() : null,
        ]);

        $this->auditLogService->log(
            event: 'business.status_changed',
            module: 'platform',
            auditable: $business,
            description: 'Business status changed by Super Admin.',
            before: $before,
            after: $business->fresh()->only(['status', 'suspended_at', 'archived_at']),
        );

        return back()->with('success', 'Business status updated.');
    }

    private function validated(Request $request, ?Business $business = null): array
    {
        $validated = $request->validate([
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

        if (filled($validated['owner_id'] ?? null)
            && ! User::whereKey($validated['owner_id'])->whereHas('roles', fn ($query) => $query->where('name', 'owner'))->exists()
        ) {
            throw ValidationException::withMessages([
                'owner_id' => 'The selected user must have the Owner role.',
            ]);
        }

        return $validated;
    }
}
