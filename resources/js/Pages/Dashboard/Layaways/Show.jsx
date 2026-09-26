import React, { useEffect } from "react";
import { Head, Link, router, useForm, usePage } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { useAuthorization } from "@/Utils/authorization";
import { IconArrowLeft, IconCash, IconCheck, IconX } from "@tabler/icons-react";
import toast from "react-hot-toast";

const formatCurrency = (value = 0) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "KES",
        minimumFractionDigits: 0,
    }).format(value);

const formatDate = (value) => {
    if (!value) return "-";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
};

const statusBadge = (status) => {
    const base = "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold";

    switch (status) {
        case "paid":
            return <span className={`${base} bg-emerald-100 text-emerald-700`}>Paid</span>;
        case "released":
            return <span className={`${base} bg-sky-100 text-sky-700`}>Released</span>;
        case "cancelled":
            return <span className={`${base} bg-slate-200 text-slate-700`}>Cancelled</span>;
        case "partial":
        default:
            return <span className={`${base} bg-amber-100 text-amber-700`}>Partial</span>;
    }
};

export default function LayawayShow({ layaway }) {
    const { flash } = usePage().props;
    const { can } = useAuthorization();

    const { data, setData, post, processing, errors, reset } = useForm({
        amount: "",
        payment_method: "cash",
        payment_reference: "",
        notes: "",
    });

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
    }, [flash]);

    const handleRelease = () => {
        if (!window.confirm("Release this item once the full balance is settled?")) return;
        router.post(route("layaways.release", layaway.id));
    };

    const handleCancel = () => {
        if (!window.confirm("Cancel this layaway and restore reserved stock?")) return;
        router.post(route("layaways.cancel", layaway.id), { reason: "Cancelled by user" });
    };

    const handlePayment = (event) => {
        event.preventDefault();
        post(route("layaways.payments.store", layaway.id), {
            onSuccess: () => reset(),
        });
    };

    const items = layaway.items || [];
    const payments = layaway.payments || [];

    return (
        <>
            <Head title={`Layaway ${layaway.reference}`} />
            <div className="space-y-6">
                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div className="flex items-center gap-3">
                        <Link
                            href={route("layaways.index")}
                            className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                        >
                            <IconArrowLeft size={16} />
                            Back to layaways
                        </Link>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        {layaway.status !== "released" && layaway.status !== "cancelled" && layaway.balance_amount === 0 && can("layaways-release") && (
                            <button
                                type="button"
                                onClick={handleRelease}
                                className="inline-flex items-center gap-2 rounded-xl bg-sky-500 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-600"
                            >
                                <IconCheck size={16} />
                                Release item
                            </button>
                        )}

                        {layaway.status !== "cancelled" && can("layaways-cancel") && (
                            <button
                                type="button"
                                onClick={handleCancel}
                                className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                            >
                                <IconX size={16} />
                                Cancel
                            </button>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Reference</p>
                        <p className="mt-2 text-lg font-bold text-slate-900 dark:text-white">{layaway.reference}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Status</p>
                        <div className="mt-2">{statusBadge(layaway.status)}</div>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Total</p>
                        <p className="mt-2 text-lg font-bold text-slate-900 dark:text-white">{formatCurrency(layaway.total_amount || 0)}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Balance</p>
                        <p className="mt-2 text-lg font-bold text-slate-900 dark:text-white">{formatCurrency(layaway.balance_amount || 0)}</p>
                    </div>
                </div>

                <div className="grid gap-4 xl:grid-cols-[1.5fr_0.9fr]">
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Layaway details</h2>
                        <dl className="mt-4 space-y-4 text-sm">
                            <div className="flex justify-between gap-4 border-b border-slate-200 pb-3 dark:border-slate-800">
                                <dt className="text-slate-500">Customer</dt>
                                <dd className="text-right font-medium text-slate-800 dark:text-slate-200">{layaway.customer?.name || "-"}</dd>
                            </div>
                            <div className="flex justify-between gap-4 border-b border-slate-200 pb-3 dark:border-slate-800">
                                <dt className="text-slate-500">Sales rep</dt>
                                <dd className="text-right font-medium text-slate-800 dark:text-slate-200">{layaway.user?.name || "-"}</dd>
                            </div>
                            <div className="flex justify-between gap-4 border-b border-slate-200 pb-3 dark:border-slate-800">
                                <dt className="text-slate-500">Paid amount</dt>
                                <dd className="text-right font-medium text-slate-800 dark:text-slate-200">{formatCurrency(layaway.paid_amount || 0)}</dd>
                            </div>
                            <div className="flex justify-between gap-4 border-b border-slate-200 pb-3 dark:border-slate-800">
                                <dt className="text-slate-500">Created at</dt>
                                <dd className="text-right font-medium text-slate-800 dark:text-slate-200">{formatDate(layaway.created_at)}</dd>
                            </div>
                            <div className="flex justify-between gap-4">
                                <dt className="text-slate-500">Notes</dt>
                                <dd className="max-w-[60%] text-right font-medium text-slate-800 dark:text-slate-200">{layaway.notes || "-"}</dd>
                            </div>
                        </dl>

                        <h3 className="mt-8 text-md font-semibold text-slate-900 dark:text-white">Items</h3>
                        <div className="mt-4 space-y-3">
                            {items.length === 0 ? (
                                <p className="text-sm text-slate-500">No items in this layaway.</p>
                            ) : (
                                items.map((item) => (
                                    <div key={item.id} className="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/50">
                                        <div className="flex items-center justify-between gap-3">
                                            <div>
                                                <p className="font-medium text-slate-800 dark:text-slate-100">{item.product?.title || "Product"}</p>
                                                <p className="text-xs text-slate-500">{item.qty} x {formatCurrency(item.unit_price || 0)}</p>
                                            </div>
                                            <span className="font-semibold text-slate-800 dark:text-slate-100">{formatCurrency(item.subtotal || 0)}</span>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>

                    <div className="space-y-4">
                        {layaway.status !== "released" && layaway.status !== "cancelled" && can("layaways-pay") && (
                            <form onSubmit={handlePayment} className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Add payment</h3>
                                <div className="mt-4 space-y-3">
                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Amount</label>
                                        <input
                                            type="number"
                                            min="1"
                                            value={data.amount}
                                            onChange={(event) => setData("amount", event.target.value)}
                                            className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                            placeholder="0"
                                        />
                                        {errors.amount && <p className="mt-1 text-xs text-rose-500">{errors.amount}</p>}
                                    </div>

                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Payment method</label>
                                        <select
                                            value={data.payment_method}
                                            onChange={(event) => setData("payment_method", event.target.value)}
                                            className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        >
                                            <option value="cash">Cash</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="card">Card</option>
                                            <option value="ewallet">E-Wallet</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Reference</label>
                                        <input
                                            value={data.payment_reference}
                                            onChange={(event) => setData("payment_reference", event.target.value)}
                                            className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                            placeholder="Optional"
                                        />
                                    </div>

                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Notes</label>
                                        <textarea
                                            rows={3}
                                            value={data.notes}
                                            onChange={(event) => setData("notes", event.target.value)}
                                            className="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                            placeholder="Optional details"
                                        />
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="mt-4 inline-flex items-center gap-2 rounded-xl bg-primary-500 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600 disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    <IconCash size={16} />
                                    {processing ? "Recording..." : "Record payment"}
                                </button>
                            </form>
                        )}

                        <div className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                            <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Payment history</h3>
                            <div className="mt-4 space-y-3">
                                {payments.length === 0 ? (
                                    <p className="text-sm text-slate-500">No payments recorded yet.</p>
                                ) : (
                                    payments.map((payment) => (
                                        <div key={payment.id} className="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/50">
                                            <div className="flex items-center justify-between gap-3">
                                                <div>
                                                    <p className="font-medium text-slate-800 dark:text-slate-100">{formatCurrency(payment.amount || 0)}</p>
                                                    <p className="text-xs text-slate-500">{payment.payment_method} • {payment.user?.name || "-"}</p>
                                                </div>
                                                <span className="text-xs text-slate-500">{formatDate(payment.paid_at)}</span>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

LayawayShow.layout = (page) => <DashboardLayout>{page}</DashboardLayout>;
