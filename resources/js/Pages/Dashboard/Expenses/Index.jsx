import React, { useEffect, useMemo, useState } from "react";
import { Head, Link, router, useForm, usePage } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import Pagination from "@/Components/Dashboard/Pagination";
import { useAuthorization } from "@/Utils/authorization";
import {
    IconCalendar,
    IconCashBanknote,
    IconCheck,
    IconFileInvoice,
    IconPlus,
    IconSearch,
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
        case "approved":
            return <span className={`${base} bg-emerald-100 text-emerald-700`}>Approved</span>;
        case "rejected":
            return <span className={`${base} bg-rose-100 text-rose-700`}>Rejected</span>;
        case "cancelled":
            return <span className={`${base} bg-slate-200 text-slate-700`}>Cancelled</span>;
        default:
            return <span className={`${base} bg-amber-100 text-amber-700`}>Submitted</span>;
    }
};

export default function ExpensesIndex({ expenses, categories = [], warehouses = [], filters = {}, summary = {} }) {
    const { flash } = usePage().props;
    const { can } = useAuthorization();

    const [search, setSearch] = useState(filters.search || "");
    const [status, setStatus] = useState(filters.status || "");
    const [categoryId, setCategoryId] = useState(filters.category_id || "");
    const [paymentMethod, setPaymentMethod] = useState(filters.payment_method || "");
    const [warehouseId, setWarehouseId] = useState(filters.warehouse_id || "");

    const { data, setData, post, processing, errors, reset } = useForm({
        expense_category_id: "",
        warehouse_id: "",
        amount: "",
        expense_date: new Date().toISOString().slice(0, 10),
        payment_method: "cash",
        description: "",
        reference_number: "",
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
            category_id: categoryId,
            payment_method: paymentMethod,
            warehouse_id: warehouseId,
        }),
        [search, status, categoryId, paymentMethod, warehouseId]
    );

    const applyFilters = (event) => {
        event.preventDefault();
        router.get(route("expenses.index"), filtersPayload, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const submitExpense = (event) => {
        event.preventDefault();
        post(route("expenses.store"), {
            onSuccess: () => reset(),
        });
    };

    const rows = expenses?.data || [];
    const canCreateExpense = can("expenses-create") || can("expenses-submit");

    return (
        <>
            <Head title="Business Expenses" />
            <div className="space-y-6">
                <div className="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Business Expenses</h1>
                        <p className="text-sm text-slate-500 dark:text-slate-400">
                            Track daily and operational expenses for the business.
                        </p>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Today</p>
                        <p className="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{formatCurrency(summary.today || 0)}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Pending</p>
                        <p className="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">{summary.pending || 0}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Approved Today</p>
                        <p className="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{formatCurrency(summary.approved_today || 0)}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">This Month</p>
                        <p className="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{formatCurrency(summary.this_month || 0)}</p>
                    </div>
                </div>

                {canCreateExpense && (
                    <form onSubmit={submitExpense} className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <div className="mb-4 flex items-center gap-2">
                            <IconPlus size={18} className="text-primary-500" />
                            <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Submit Expense</h2>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Category</label>
                                <select
                                    value={data.expense_category_id}
                                    onChange={(event) => setData("expense_category_id", event.target.value)}
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    <option value="">Select category</option>
                                    {categories.map((category) => (
                                        <option key={category.id} value={category.id}>{category.name}</option>
                                    ))}
                                </select>
                                {errors.expense_category_id && <p className="mt-1 text-xs text-rose-500">{errors.expense_category_id}</p>}
                            </div>

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
                                <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Expense Date</label>
                                <input
                                    type="date"
                                    value={data.expense_date}
                                    onChange={(event) => setData("expense_date", event.target.value)}
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                />
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Payment Method</label>
                                <select
                                    value={data.payment_method}
                                    onChange={(event) => setData("payment_method", event.target.value)}
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="mpesa">M-Pesa</option>
                                    <option value="card">Card</option>
                                    <option value="petty_cash">Petty Cash</option>
                                </select>
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Warehouse</label>
                                <select
                                    value={data.warehouse_id}
                                    onChange={(event) => setData("warehouse_id", event.target.value)}
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    <option value="">Select warehouse</option>
                                    {warehouses.map((warehouse) => (
                                        <option key={warehouse.id} value={warehouse.id}>{warehouse.name}</option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Reference</label>
                                <input
                                    value={data.reference_number}
                                    onChange={(event) => setData("reference_number", event.target.value)}
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    placeholder="Optional"
                                />
                            </div>

                            <div className="md:col-span-2 xl:col-span-2">
                                <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Description</label>
                                <input
                                    value={data.description}
                                    onChange={(event) => setData("description", event.target.value)}
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    placeholder="What was purchased or spent on?"
                                />
                                {errors.description && <p className="mt-1 text-xs text-rose-500">{errors.description}</p>}
                            </div>

                            <div className="md:col-span-2 xl:col-span-4">
                                <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Notes</label>
                                <textarea
                                    rows={2}
                                    value={data.notes}
                                    onChange={(event) => setData("notes", event.target.value)}
                                    className="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    placeholder="Optional details"
                                />
                            </div>
                        </div>

                        <div className="mt-4 flex justify-end">
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center gap-2 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-600 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <IconCashBanknote size={16} />
                                {processing ? "Submitting..." : "Submit Expense"}
                            </button>
                        </div>
                    </form>
                )}

                <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                    <form onSubmit={applyFilters} className="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                        <div className="relative">
                            <IconSearch size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                            <input
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Search by code or description"
                                className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-3 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            />
                        </div>

                        <select
                            value={status}
                            onChange={(event) => setStatus(event.target.value)}
                            className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        >
                            <option value="">All statuses</option>
                            <option value="submitted">Submitted</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </select>

                        <select
                            value={categoryId}
                            onChange={(event) => setCategoryId(event.target.value)}
                            className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        >
                            <option value="">All categories</option>
                            {categories.map((category) => (
                                <option key={category.id} value={category.id}>{category.name}</option>
                            ))}
                        </select>

                        <select
                            value={paymentMethod}
                            onChange={(event) => setPaymentMethod(event.target.value)}
                            className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        >
                            <option value="">All methods</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="card">Card</option>
                            <option value="petty_cash">Petty Cash</option>
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
                                    setCategoryId("");
                                    setPaymentMethod("");
                                    setWarehouseId("");
                                    router.get(route("expenses.index"), {}, { preserveState: true, replace: true });
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
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Expense</th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Description</th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Category</th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Amount</th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                    <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-200 dark:divide-slate-800">
                                {rows.length === 0 ? (
                                    <tr>
                                        <td colSpan={7} className="px-4 py-10 text-center text-sm text-slate-500">
                                            No business expenses found for the current filters.
                                        </td>
                                    </tr>
                                ) : (
                                    rows.map((expense) => (
                                        <tr key={expense.id} className="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                            <td className="px-4 py-3 text-sm font-medium text-slate-800 dark:text-slate-100">
                                                <div className="flex flex-col">
                                                    <span>{expense.expense_number}</span>
                                                    <span className="text-xs text-slate-500">{expense.payment_method}</span>
                                                </div>
                                            </td>
                                            <td className="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{expense.description}</td>
                                            <td className="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{expense.category?.name || "-"}</td>
                                            <td className="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{formatDate(expense.expense_date)}</td>
                                            <td className="px-4 py-3 text-sm font-semibold text-slate-800 dark:text-slate-100">{formatCurrency(expense.amount)}</td>
                                            <td className="px-4 py-3">{statusBadge(expense.status)}</td>
                                            <td className="px-4 py-3 text-right">
                                                <Link
                                                    href={route("expenses.show", expense.id)}
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

                    {expenses?.links && <Pagination links={expenses.links} className="border-t border-slate-200 dark:border-slate-800" />}
                </div>
            </div>
        </>
    );
}

ExpensesIndex.layout = (page) => <DashboardLayout>{page}</DashboardLayout>;
