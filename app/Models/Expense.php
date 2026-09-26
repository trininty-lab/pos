<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'store_id',
        'warehouse_id',
        'cashier_shift_id',
        'expense_category_id',
        'expense_number',
        'created_by',
        'submitted_by',
        'approved_by',
        'rejected_by',
        'cancelled_by',
        'currency',
        'amount',
        'payment_method',
        'payment_reference',
        'description',
        'expense_date',
        'status',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'cancelled_at',
        'cancellation_reason',
        'notes',
        'cash_effect',
    ];

    protected $casts = [
        'business_id' => 'integer',
        'store_id' => 'integer',
        'warehouse_id' => 'integer',
        'cashier_shift_id' => 'integer',
        'expense_category_id' => 'integer',
        'created_by' => 'integer',
        'submitted_by' => 'integer',
        'approved_by' => 'integer',
        'rejected_by' => 'integer',
        'cancelled_by' => 'integer',
        'amount' => 'integer',
        'cash_effect' => 'integer',
        'expense_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $expense): void {
            if (empty($expense->expense_number)) {
                $year = now()->format('Y');
                $latest = self::query()->whereYear('created_at', $year)->max('id') ?? 0;
                $expense->expense_number = 'EXP-'.$year.'-'.str_pad((string) ($latest + 1), 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function cashierShift(): BelongsTo
    {
        return $this->belongsTo(CashierShift::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'entity');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
