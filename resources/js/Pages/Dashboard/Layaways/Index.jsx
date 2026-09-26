import React, { useEffect, useMemo, useState } from "react";
import { Head, Link, router, useForm, usePage } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import Pagination from "@/Components/Dashboard/Pagination";
import { useAuthorization } from "@/Utils/authorization";
import {
    IconCash,
    IconCheck,
    IconPlus,
    IconSearch,
    IconShoppingCart,
    IconX,
} from "@tabler/icons-react";
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

export default function LayawaysIndex({ layaways, customers = [], products = [], filters = {}, summary = {} }) {
    const { flash, activeCashierShift } = usePage().props;
    const { can } = useAuthorization();

    const [search, setSearch] = useState(filters.search || "");
    const [status, setStatus] = useState(filters.status || "");
    const [showCreateForm, setShowCreateForm] = useState(false);

    const { data, setData, post, processing, errors, reset, transform } = useForm({
        customer_id: "",
        product_id: "",
        qty: 1,
        unit_price: "",
        initial_payment: "",
        payment_method: "cash",
        payment_reference: "",
        notes: "",
    });

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
    }, [flash]);

    const filtersPayload = useMemo(
        () => ({
            search,
            status,
        }),
        [search, status]
    );

    const applyFilters = (event) => {
        event.preventDefault();
        router.get(route("layaways.index"), filtersPayload, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const selectedProduct = products.find((product) => String(product.id) === String(data.product_id));

    const handleProductChange = (event) => {
        const product = products.find((item) => String(item.id) === event.target.value);
        setData((current) => ({
            ...current,
            product_id: event.target.value,
            unit_price: product?.sell_price || "",
        }));
    };

    const submitLayaway = (event) => {
        event.preventDefault();
        transform(() => ({
            customer_id: data.customer_id,
            items: [{
                product_id: data.product_id,
                qty: Number(data.qty),
                unit_price: Number(data.unit_price),
            }],
            initial_payment: Number(data.initial_payment),
            payment_method: data.payment_method,
            payment_reference: data.payment_reference,
            notes: data.notes,
        }));
        post(route("layaways.store"), {
            onSuccess: () => {
                reset();
                setShowCreateForm(false);
            },
        });
    };

    const rows = layaways?.data || [];

    return (
        <>
            <Head title="Layaways" />
            <div className="space-y-6">
                <div className="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Layaway Management</h1>
                        <p className="text-sm text-slate-500 dark:text-slate-400">
                            Track installment sales, outstanding balances, and release readiness.
                        </p>
                    </div>
                    {can("layaways-create") && (
                        <button
                            type="button"
                            onClick={() => setShowCreateForm((visible) => !visible)}
                            className="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-600"
                        >
                            <IconPlus size={17} />
                            Add Layaway
                        </button>
                    )}
                </div>

                {showCreateForm && can("layaways-create") && (
                    <div className="rounded-2xl border border-primary-100 bg-white p-5 shadow-sm dark:border-primary-900/40 dark:bg-slate-900">
                        <div className="mb-4 flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2 className="text-lg font-bold text-slate-900 dark:text-white">Create layaway</h2>
                                <p className="text-sm text-slate-500 dark:text-slate-400">Cashier records the customer and amount received.</p>
                            </div>
                            {!activeCashierShift && (
                                <span className="rounded-lg bg-amber-50 px-3 py-2 text-xs font-medium text-amber-700 dark:bg-amber-900/20 dark:text-amber-300">
                                    Open a cashier shift before saving.
                                </span>
                            )}
                        </div>
                        <form onSubmit={submitLayaway} className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <label className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                Customer
                                <select required value={data.customer_id} onChange={(event) => setData("customer_id", event.target.value)} className="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 dark:border-slate-700 dark:bg-slate-800">
                                    <option value="">Select customer</option>
                                    {customers.map((customer) => <option key={customer.id} value={customer.id}>{customer.name}{customer.no_telp ? ` - ${customer.no_telp}` : ""}</option>)}
                                </select>
                                {errors.customer_id && <span className="text-xs text-rose-600">{errors.customer_id}</span>}
                            </label>
                            <label className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                Product
                                <select required value={data.product_id} onChange={handleProductChange} className="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 dark:border-slate-700 dark:bg-slate-800">
                                    <option value="">Select product</option>
                                    {products.map((product) => <option key={product.id} value={product.id}>{product.title} - stock {product.stock}</option>)}
                                </select>
                                {errors["items.0.product_id"] && <span className="text-xs text-rose-600">{errors["items.0.product_id"]}</span>}
                            </label>
                            <label className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                Quantity
                                <input required min="1" max={selectedProduct?.stock || undefined} type="number" value={data.qty} onChange={(event) => setData("qty", event.target.value)} className="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 dark:border-slate-700 dark:bg-slate-800" />
                            </label>
                            <label className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                Unit price
                                <input required min="1" type="number" value={data.unit_price} onChange={(event) => setData("unit_price", event.target.value)} className="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 dark:border-slate-700 dark:bg-slate-800" />
                            </label>
                            <label className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                Initial payment received
                                <input required min="1" type="number" value={data.initial_payment} onChange={(event) => setData("initial_payment", event.target.value)} className="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 dark:border-slate-700 dark:bg-slate-800" />
                            </label>
                            <label className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                Payment method
                                <select value={data.payment_method} onChange={(event) => setData("payment_method", event.target.value)} className="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 dark:border-slate-700 dark:bg-slate-800">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                            </label>
                            <label className="text-sm font-medium text-slate-700 dark:text-slate-300 md:col-span-2">
                                Notes
                                <input value={data.notes} onChange={(event) => setData("notes", event.target.value)} placeholder="Optional notes" className="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 dark:border-slate-700 dark:bg-slate-800" />
                            </label>
                            <div className="flex items-end justify-end gap-2 md:col-span-2 xl:col-span-1">
                                <button type="button" onClick={() => setShowCreateForm(false)} className="h-11 rounded-xl border border-slate-200 px-4 text-sm font-medium text-slate-600 dark:border-slate-700 dark:text-slate-300">Cancel</button>
                                <button disabled={processing || !activeCashierShift} type="submit" className="h-11 rounded-xl bg-primary-500 px-4 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50">{processing ? "Saving..." : "Save layaway"}</button>
                            </div>
                        </form>
                    </div>
                )}

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Total</p>
                        <p className="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{summary.total || 0}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Partial</p>
                        <p className="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">{summary.partially_paid || 0}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Ready to release</p>
                        <p className="mt-2 text-2xl font-bold text-sky-600 dark:text-sky-400">{summary.ready_for_release || 0}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Completed</p>
                        <p className="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{summary.completed || 0}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Cancelled</p>
                        <p className="mt-2 text-2xl font-bold text-slate-600 dark:text-slate-300">{summary.cancelled || 0}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Outstanding</p>
                        <p className="mt-2 text-2xl font-bold text-rose-600 dark:text-rose-400">{formatCurrency(summary.outstanding_balance || 0)}</p>
                    </div>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                    <form onSubmit={applyFilters} className="grid gap-3 md:grid-cols-3 xl:grid-cols-4">
                        <div className="relative">
                            <IconSearch size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                            <input
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Search by reference or customer"
                                className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-3 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            />
                        </div>

                        <select
                            value={status}
                            onChange={(event) => setStatus(event.target.value)}
                            className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        >
                            <option value="">All statuses</option>
                            <option value="partial">Partial</option>
                            <option value="paid">Paid</option>
                            <option value="released">Released</option>
                            <option value="cancelled">Cancelled</option>
                        </select>

                        <div className="flex gap-2">
                            <button
                                type="submit"
                                className="inline-flex h-11 items-center justify-center rounded-xl bg-primary-500 px-4 text-sm font-semibold text-white hover:bg-primary-600"
                            >
                                Filter
                            </button>
                            <button
                                type="button"
                                onClick={() => {
                                    setSearch("");
                                    setStatus("");
                                    router.get(route("layaways.index"), {}, { preserveState: true, replace: true });
                                }}
                                className="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                Reset
                            </button>
                        </div>
                    </form>
                </div>

                <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                            <thead className="bg-slate-50 dark:bg-slate-800/60">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Reference</th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Total</th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Paid</th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Balance</th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Created</th>
                                    <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-200 dark:divide-slate-800">
                                {rows.length === 0 ? (
                                    <tr>
                                        <td colSpan={8} className="px-4 py-10 text-center text-sm text-slate-500">
                                            No layaways found for the current filters.
                                        </td>
                                    </tr>
                                ) : (
                                    rows.map((layaway) => (
                                        <tr key={layaway.id} className="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                            <td className="px-4 py-3 text-sm font-medium text-slate-800 dark:text-slate-100">
                                                <div className="flex flex-col">
                                                    <span>{layaway.reference}</span>
                                                    <span className="text-xs text-slate-500">{layaway.user?.name || "-"}</span>
                                                </div>
                                            </td>
                                            <td className="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                                {layaway.customer?.name || "-"}
                                            </td>
                                            <td className="px-4 py-3 text-sm font-semibold text-slate-800 dark:text-slate-100">
                                                {formatCurrency(layaway.total_amount || 0)}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                                {formatCurrency(layaway.total_paid || 0)}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                                {formatCurrency(layaway.balance_amount || 0)}
                                            </td>
                                            <td className="px-4 py-3">{statusBadge(layaway.status)}</td>
                                            <td className="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                                {formatDate(layaway.created_at)}
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                <Link
                                                    href={route("layaways.show", layaway.id)}
                                                    className="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                                                >
                                                    View
                                                </Link>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {layaways?.links && <Pagination links={layaways.links} className="border-t border-slate-200 dark:border-slate-800" />}
                </div>
            </div>
        </>
    );
}

LayawaysIndex.layout = (page) => <DashboardLayout>{page}</DashboardLayout>;
