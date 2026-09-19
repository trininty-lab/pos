import React from "react";
import { Head, Link } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
    IconActivity,
    IconBuildingStore,
    IconDatabase,
    IconHeartbeat,
    IconServer,
    IconUsers,
} from "@tabler/icons-react";

const currency = (value) =>
    new Intl.NumberFormat("en-KE", { style: "currency", currency: "KES", maximumFractionDigits: 0 }).format(value || 0);

function Metric({ label, value, icon: Icon, tone = "indigo" }) {
    const tones = {
        indigo: "bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300",
        emerald: "bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300",
        amber: "bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300",
        rose: "bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300",
    };

    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
            <div className="flex items-start justify-between gap-4">
                <div>
                    <p className="text-sm text-slate-500 dark:text-slate-400">{label}</p>
                    <p className="mt-2 text-2xl font-bold text-slate-950 dark:text-white">{value}</p>
                </div>
                <span className={`rounded-xl p-3 ${tones[tone]}`}><Icon size={21} /></span>
            </div>
        </div>
    );
}

function HealthRow({ label, item }) {
    const ok = item?.status === "ok" || item?.status === "configured";
    return (
        <div className="flex items-center justify-between border-b border-slate-100 py-3 last:border-0 dark:border-slate-800">
            <span className="text-sm text-slate-600 dark:text-slate-300">{label}</span>
            <span className={`text-xs font-semibold ${ok ? "text-emerald-600" : "text-amber-600"}`}>{item?.label}</span>
        </div>
    );
}

export default function Index({ summary, salesByBusiness = [], recentRegistrations = [], recentActions = [], failedOperations = [], health }) {
    return (
        <DashboardLayout>
            <Head title="Super Admin Dashboard" />
            <div className="space-y-6">
                <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-widest text-indigo-600">Platform control</p>
                        <h1 className="mt-1 text-3xl font-bold text-slate-950 dark:text-white">Super Admin Dashboard</h1>
                        <p className="mt-2 text-sm text-slate-500 dark:text-slate-400">A platform-wide view of businesses, people, sales, and system health.</p>
                    </div>
                    <Link href={route("platform.businesses.index")} className="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-white dark:text-slate-950">Manage businesses</Link>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <Metric label="Registered businesses" value={summary.businesses} icon={IconBuildingStore} />
                    <Metric label="Active businesses" value={summary.active_businesses} icon={IconBuildingStore} tone="emerald" />
                    <Metric label="System users" value={`${summary.active_users} / ${summary.users}`} icon={IconUsers} tone="amber" />
                    <Metric label="Online in last 15 min" value={summary.online_users} icon={IconActivity} tone="rose" />
                    <Metric label="Platform sales" value={currency(summary.sales)} icon={IconServer} tone="emerald" />
                    <Metric label="Transactions" value={summary.transactions} icon={IconActivity} />
                    <Metric label="New businesses, 30 days" value={summary.new_businesses} icon={IconBuildingStore} tone="amber" />
                    <Metric label="Pending businesses" value={summary.pending_businesses} icon={IconBuildingStore} tone="rose" />
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
                    <section className="rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                        <div className="flex items-center justify-between border-b border-slate-100 p-5 dark:border-slate-800"><div><h2 className="font-semibold text-slate-900 dark:text-white">Sales by business</h2><p className="text-sm text-slate-500">Attributed through cashier accounts.</p></div><IconBuildingStore className="text-indigo-500" /></div>
                        <div className="overflow-x-auto"><table className="w-full text-left text-sm"><thead className="text-xs uppercase text-slate-400"><tr><th className="px-5 py-3">Business</th><th className="px-5 py-3">Transactions</th><th className="px-5 py-3 text-right">Sales</th></tr></thead><tbody>{salesByBusiness.map((row) => <tr key={row.business} className="border-t border-slate-100 dark:border-slate-800"><td className="px-5 py-3 font-medium text-slate-800 dark:text-slate-200">{row.business}</td><td className="px-5 py-3 text-slate-500">{row.transactions}</td><td className="px-5 py-3 text-right font-semibold text-slate-800 dark:text-slate-200">{currency(row.sales)}</td></tr>)}</tbody></table></div>
                    </section>
                    <section className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><div className="flex items-center gap-3"><span className="rounded-xl bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-950/40"><IconHeartbeat /></span><div><h2 className="font-semibold text-slate-900 dark:text-white">System health</h2><p className="text-sm text-slate-500">Live infrastructure signals</p></div></div><div className="mt-4"><HealthRow label="Database" item={health.database} /><HealthRow label="Cache" item={health.cache} /><HealthRow label="Storage" item={health.storage} /><HealthRow label="Backups" item={health.backup} /><HealthRow label="Payments" item={health.integrations.payment === "configured" ? { label: "Configured", status: "configured" } : { label: "Not configured", status: "warning" }} /></div></section>
                </div>

                <div className="grid gap-6 lg:grid-cols-3"><section className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><h2 className="font-semibold text-slate-900 dark:text-white">Recent registrations</h2><div className="mt-4 space-y-3">{recentRegistrations.map((business) => <div key={business.id} className="flex items-center justify-between gap-3"><div><p className="font-medium text-slate-800 dark:text-slate-200">{business.name}</p><p className="text-xs text-slate-500">{business.owner?.email || "No owner"}</p></div><span className="rounded-full bg-slate-100 px-2 py-1 text-xs capitalize text-slate-600 dark:bg-slate-800 dark:text-slate-300">{business.status}</span></div>)}</div></section><section className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><h2 className="font-semibold text-slate-900 dark:text-white">Administrative activity</h2><div className="mt-4 space-y-3">{recentActions.slice(0, 5).map((action) => <div key={action.id}><p className="text-sm text-slate-700 dark:text-slate-300">{action.description}</p><p className="text-xs text-slate-400">{action.user?.name || "System"} · {action.created_at}</p></div>)}</div></section><section className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><h2 className="font-semibold text-slate-900 dark:text-white">Failed operations</h2><div className="mt-4 space-y-3">{failedOperations.length ? failedOperations.map((item) => <div key={item.id}><p className="text-sm text-rose-600">{item.event}</p><p className="text-xs text-slate-500">{item.description}</p></div>) : <p className="text-sm text-emerald-600">No recent failures recorded.</p>}</div></section></div>
            </div>
        </DashboardLayout>
    );
}
