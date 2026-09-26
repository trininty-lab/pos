import React, { useEffect, useState } from "react";
import { Head, Link, router, useForm, usePage } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { useAuthorization } from "@/Utils/authorization";
import { IconArrowLeft, IconCheck, IconX } from "@tabler/icons-react";
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

export default function ExpenseShow({ expense }) {
    const { flash } = usePage().props;
    const { can } = useAuthorization();
    const [rejectReason, setRejectReason] = useState("");

    const { data, setData, post, processing, errors, reset } = useForm({ reason: "" });

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
    }, [flash]);

    const handleApprove = () => router.post(route("expenses.approve", expense.id));

    const handleReject = (event) => {
        event.preventDefault();
        post(route("expenses.reject", expense.id), {
            onSuccess: () => reset(),
        });
    };

    const handleCancel = () => {
        if (!window.confirm("Cancel this expense?")) return;
        router.post(route("expenses.cancel", expense.id), { reason: "Cancelled by user" });
    };

    return (
        <>
            <Head title={`Expense ${expense.expense_number}`} />
            <div className="space-y-6">
                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div className="flex items-center gap-3">
                        <Link
                            href={route("expenses.index")}
                            className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                        >
                            <IconArrowLeft size={16} />
                            Back to expenses
                        </Link>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        {expense.status === "submitted" && can("expenses-approve") && (
                            <button
                                type="button"
                                onClick={handleApprove}
                                className="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600"
                            >
                                <IconCheck size={16} />
                                Approve
                            </button>
                        )}

                        {expense.status === "submitted" && can("expenses-reject") && (
                            <button
                                type="button"
                                onClick={() => document.getElementById("reject-form")?.scrollIntoView({ behavior: "smooth" })}
                                className="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100 dark:border-rose-800 dark:bg-rose-950/20 dark:text-rose-300"
                            >
                                <IconX size={16} />
                                Reject
                            </button>
                        )}

                        {expense.status !== "cancelled" && can("expenses-cancel") && (
                            <button
                                type="button"
                                onClick={handleCancel}
                                className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                            >
                                Cancel
                            </button>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Expense number</p>
                        <p className="mt-2 text-lg font-bold text-slate-900 dark:text-white">{expense.expense_number}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Status</p>
                        <div className="mt-2">{statusBadge(expense.status)}</div>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Amount</p>
                        <p className="mt-2 text-lg font-bold text-slate-900 dark:text-white">{formatCurrency(expense.amount)}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p className="text-sm text-slate-500">Date</p>
                        <p className="mt-2 text-lg font-bold text-slate-900 dark:text-white">{formatDate(expense.expense_date)}</p>
                    </div>
                </div>

                <div className="grid gap-4 xl:grid-cols-[1.5fr_0.9fr]">
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Expense details</h2>
                        <dl className="mt-4 space-y-4 text-sm">
                            <div className="flex justify-between gap-4 border-b border-slate-200 pb-3 dark:border-slate-800">
                                <dt className="text-slate-500">Description</dt>
                                <dd className="text-right font-medium text-slate-800 dark:text-slate-200">{expense.description}</dd>
                            </div>
                            <div className="flex justify-between gap-4 border-b border-slate-200 pb-3 dark:border-slate-800">
                                <dt className="text-slate-500">Category</dt>
                                <dd className="text-right font-medium text-slate-800 dark:text-slate-200">{expense.category?.name || "-"}</dd>
                            </div>
                            <div className="flex justify-between gap-4 border-b border-slate-200 pb-3 dark:border-slate-800">
                                <dt className="text-slate-500">Payment method</dt>
                                <dd className="text-right font-medium text-slate-800 dark:text-slate-200">{expense.payment_method}</dd>
                            </div>
                            <div className="flex justify-between gap-4 border-b border-slate-200 pb-3 dark:border-slate-800">
                                <dt className="text-slate-500">Warehouse</dt>
                                <dd className="text-right font-medium text-slate-800 dark:text-slate-200">{expense.warehouse?.name || "-"}</dd>
                            </div>
                            <div className="flex justify-between gap-4 border-b border-slate-200 pb-3 dark:border-slate-800">
                                <dt className="text-slate-500">Reference</dt>
                                <dd className="text-right font-medium text-slate-800 dark:text-slate-200">{expense.payment_reference || "-"}</dd>
                            </div>
                            <div className="flex justify-between gap-4 border-b border-slate-200 pb-3 dark:border-slate-800">
                                <dt className="text-slate-500">Submitted by</dt>
                                <dd className="text-right font-medium text-slate-800 dark:text-slate-200">{expense.creator?.name || "-"}</dd>
                            </div>
                            <div className="flex justify-between gap-4">
                                <dt className="text-slate-500">Notes</dt>
                                <dd className="max-w-[60%] text-right font-medium text-slate-800 dark:text-slate-200">{expense.notes || "-"}</dd>
                            </div>
                        </dl>
                    </div>

                    <div className="space-y-4">
                        <div className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                            <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Approval trail</h3>
                            <ul className="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                                <li><span className="font-medium text-slate-800 dark:text-slate-100">Created:</span> {expense.creator?.name || "-"}</li>
                                <li><span className="font-medium text-slate-800 dark:text-slate-100">Approved by:</span> {expense.approved_by?.name || "-"}</li>
                                <li><span className="font-medium text-slate-800 dark:text-slate-100">Rejected by:</span> {expense.rejected_by?.name || "-"}</li>
                            </ul>
                        </div>

                        {expense.status === "rejected" && (
                            <div className="rounded-2xl border border-rose-200 bg-rose-50 p-5 dark:border-rose-800 dark:bg-rose-950/20">
                                <h3 className="text-lg font-semibold text-rose-700 dark:text-rose-300">Rejection reason</h3>
                                <p className="mt-2 text-sm text-rose-700 dark:text-rose-300">{expense.rejection_reason || "-"}</p>
                            </div>
                        )}

                        {expense.status === "submitted" && can("expenses-reject") && (
                            <form id="reject-form" onSubmit={handleReject} className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Reject expense</h3>
                                <textarea
                                    value={data.reason}
                                    onChange={(event) => setData("reason", event.target.value)}
                                    rows={4}
                                    className="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:border-primary-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    placeholder="Explain why this expense is rejected"
                                />
                                {errors.reason && <p className="mt-1 text-xs text-rose-500">{errors.reason}</p>}

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="mt-3 inline-flex items-center gap-2 rounded-xl bg-rose-500 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-600 disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    <IconX size={16} />
                                    {processing ? "Rejecting..." : "Reject"}
                                </button>
                            </form>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}

ExpenseShow.layout = (page) => <DashboardLayout>{page}</DashboardLayout>;
