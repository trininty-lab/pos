<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        return Transaction::with(['customer:id,name', 'cashier:id,name', 'details:id,transaction_id,price,discount_total'])
            ->when($this->request->start_date, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($this->request->end_date, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($this->request->warehouse_id, fn ($q, $id) => $q->where('warehouse_id', $id))
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return ['Invoice', 'Tanggal', 'Kasir', 'Pelanggan', 'Metode', 'Status', 'Subtotal', 'Diskon', 'Ongkir', 'PPN', 'Grand Total'];
    }

    public function map($transaction): array
    {
        $subtotal = $transaction->details->sum(fn ($detail) =>
            (int) $detail->price + (int) ($detail->discount_total ?? 0)
        );

        if ($transaction->details->isEmpty()) {
            $subtotal = (int) $transaction->grand_total
                - (int) ($transaction->shipping_cost ?? 0)
                - (int) ($transaction->tax_total ?? 0);
        }

        return [
            $transaction->invoice,
            $transaction->created_at->format('Y-m-d H:i:s'),
            $transaction->cashier?->name ?? '',
            $transaction->customer?->name ?? 'Umum',
            $transaction->payment_method ?? '',
            $transaction->payment_status ?? '',
            $subtotal
                + (int) ($transaction->discount ?? 0)
                + (int) ($transaction->loyalty_discount_total ?? 0)
                + (int) ($transaction->customer_voucher_discount ?? 0),
            (int) ($transaction->discount ?? 0),
            (int) ($transaction->shipping_cost ?? 0),
            (int) ($transaction->tax_total ?? 0),
            (int) $transaction->grand_total,
        ];
    }
}
