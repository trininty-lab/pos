<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Inertia\Inertia;

class PlatformController extends Controller
{
    public function index()
    {
        $now = now();
        $businessesByStatus = Business::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $salesByBusiness = Transaction::query()
            ->join('users', 'users.id', '=', 'transactions.cashier_id')
            ->leftJoin('businesses', 'businesses.id', '=', 'users.business_id')
            ->select(
                DB::raw("COALESCE(businesses.name, 'Unassigned') as business_name"),
                DB::raw('COUNT(transactions.id) as transactions_count'),
                DB::raw('SUM(transactions.grand_total) as sales_total')
            )
            ->groupBy('businesses.id', 'businesses.name')
            ->orderByDesc('sales_total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'business' => $row->business_name,
                'transactions' => (int) $row->transactions_count,
                'sales' => (int) $row->sales_total,
            ]);

        $health = [
            'database' => $this->databaseHealth(),
            'cache' => $this->cacheHealth(),
            'storage' => $this->storageHealth(),
            'backup' => [
                'status' => filled(config('backup.destination')) ? 'configured' : 'not_configured',
                'label' => filled(config('backup.destination')) ? 'Configured' : 'Not configured',
            ],
            'integrations' => [
                'payment' => config('services.midtrans.server_key') || config('services.xendit.secret_key') ? 'configured' : 'not_configured',
                'whatsapp' => filled(env('WA_SERVICE_URL')) ? 'configured' : 'not_configured',
            ],
        ];

        return Inertia::render('Platform/Index', [
            'summary' => [
                'businesses' => Business::count(),
                'active_businesses' => (int) ($businessesByStatus['active'] ?? 0),
                'suspended_businesses' => (int) ($businessesByStatus['suspended'] ?? 0),
                'pending_businesses' => (int) ($businessesByStatus['pending'] ?? 0),
                'new_businesses' => Business::where('created_at', '>=', $now->copy()->subDays(30))->count(),
                'users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'online_users' => User::where('is_active', true)->where('last_login_at', '>=', $now->copy()->subMinutes(15))->count(),
                'sales' => (int) Transaction::sum('grand_total'),
                'transactions' => Transaction::count(),
            ],
            'salesByBusiness' => $salesByBusiness,
            'recentRegistrations' => Business::with('owner:id,name,email')->latest()->limit(6)->get(),
            'recentActions' => AuditLog::with('user:id,name,email')->latest('created_at')->limit(8)->get(),
            'failedOperations' => AuditLog::where(function ($query) {
                $query->where('event', 'like', '%failed%')
                    ->orWhere('event', 'like', '%error%')
                    ->orWhereJsonContains('meta->severity', 'error');
            })->latest('created_at')->limit(8)->get(),
            'health' => $health,
        ]);
    }

    private function databaseHealth(): array
    {
        try {
            DB::connection()->getPdo();

            return ['status' => 'ok', 'label' => 'Connected'];
        } catch (\Throwable) {
            return ['status' => 'error', 'label' => 'Unavailable'];
        }
    }

    private function cacheHealth(): array
    {
        try {
            Redis::connection()->ping();

            return ['status' => 'ok', 'label' => 'Connected'];
        } catch (\Throwable) {
            return ['status' => 'warning', 'label' => 'Unavailable'];
        }
    }

    private function storageHealth(): array
    {
        $path = storage_path();
        $free = @disk_free_space($path);
        $total = @disk_total_space($path);

        return [
            'status' => $free !== false ? 'ok' : 'warning',
            'label' => $free !== false ? round(($total - $free) / 1024 / 1024 / 1024, 1).' GB used' : 'Unknown',
        ];
    }
}
