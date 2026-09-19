import React, { useState } from "react";
import { Head, Link, useForm, usePage } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { IconBuildingStore, IconPlus, IconSearch } from "@tabler/icons-react";

const statuses = ["pending", "active", "suspended", "inactive", "archived"];

export default function Index({ businesses, filters, statuses: statusOptions = statuses }) {
    const { url } = usePage();
    const [showForm, setShowForm] = useState(false);
    const { data, setData, post, processing, reset } = useForm({
        name: "",
        registration_number: "",
        tax_pin: "",
        phone: "",
        email: "",
        address: "",
        country: "KE",
        currency: "KES",
        status: "pending",
    });

    const submit = (event) => {
        event.preventDefault();
        post(route("platform.businesses.store"), { onSuccess: () => { reset(); setShowForm(false); } });
    };

    const filter = (key, value) => {
        const params = new URLSearchParams(window.location.search);
        if (value) params.set(key, value); else params.delete(key);
        window.location.href = `${url.split("?")[0]}?${params.toString()}`;
    };

    return (
        <DashboardLayout>
            <Head title="Businesses" />
            <div className="space-y-6">
                <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p className="text-sm font-semibold uppercase tracking-widest text-indigo-600">Platform administration</p><h1 className="mt-1 text-3xl font-bold text-slate-950 dark:text-white">Businesses</h1><p className="mt-2 text-sm text-slate-500">Manage organizations, lifecycle status, and ownership.</p></div><button onClick={() => setShowForm(!showForm)} className="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-white dark:text-slate-950"><IconPlus size={17} /> Create business</button></div>
                {showForm && <form onSubmit={submit} className="grid gap-4 rounded-2xl border border-indigo-100 bg-indigo-50/60 p-5 dark:border-indigo-900 dark:bg-indigo-950/20 sm:grid-cols-2"><input required placeholder="Business name" value={data.name} onChange={(e) => setData("name", e.target.value)} className="rounded-xl border-slate-200 bg-white px-3 py-2 text-sm" /><input placeholder="Registration number" value={data.registration_number} onChange={(e) => setData("registration_number", e.target.value)} className="rounded-xl border-slate-200 bg-white px-3 py-2 text-sm" /><input placeholder="Tax PIN" value={data.tax_pin} onChange={(e) => setData("tax_pin", e.target.value)} className="rounded-xl border-slate-200 bg-white px-3 py-2 text-sm" /><input type="email" placeholder="Business email" value={data.email} onChange={(e) => setData("email", e.target.value)} className="rounded-xl border-slate-200 bg-white px-3 py-2 text-sm" /><input placeholder="Phone" value={data.phone} onChange={(e) => setData("phone", e.target.value)} className="rounded-xl border-slate-200 bg-white px-3 py-2 text-sm" /><input placeholder="Currency" maxLength="3" value={data.currency} onChange={(e) => setData("currency", e.target.value.toUpperCase())} className="rounded-xl border-slate-200 bg-white px-3 py-2 text-sm" /><textarea placeholder="Address" value={data.address} onChange={(e) => setData("address", e.target.value)} className="rounded-xl border-slate-200 bg-white px-3 py-2 text-sm sm:col-span-2" /><div className="flex justify-end gap-2 sm:col-span-2"><button type="button" onClick={() => setShowForm(false)} className="rounded-xl px-4 py-2 text-sm text-slate-600">Cancel</button><button disabled={processing} className="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Create business</button></div></form>}

                <div className="flex flex-col gap-3 sm:flex-row"><label className="relative flex-1"><IconSearch size={17} className="absolute left-3 top-2.5 text-slate-400" /><input defaultValue={filters.search} onKeyDown={(e) => e.key === "Enter" && filter("search", e.currentTarget.value)} placeholder="Search businesses" className="w-full rounded-xl border-slate-200 pl-10 text-sm dark:border-slate-700 dark:bg-slate-900" /></label><select value={filters.status || ""} onChange={(e) => filter("status", e.target.value)} className="rounded-xl border-slate-200 text-sm dark:border-slate-700 dark:bg-slate-900"><option value="">All statuses</option>{statusOptions.map((status) => <option key={status} value={status}>{status}</option>)}</select></div>

                <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"><div className="overflow-x-auto"><table className="w-full min-w-[720px] text-left text-sm"><thead className="bg-slate-50 text-xs uppercase text-slate-400 dark:bg-slate-800/50"><tr><th className="px-5 py-3">Business</th><th className="px-5 py-3">Owner</th><th className="px-5 py-3">Users</th><th className="px-5 py-3">Status</th><th className="px-5 py-3 text-right">Action</th></tr></thead><tbody>{businesses.data.map((business) => <tr key={business.id} className="border-t border-slate-100 dark:border-slate-800"><td className="px-5 py-4"><div className="flex items-center gap-3"><span className="rounded-xl bg-indigo-50 p-2 text-indigo-600 dark:bg-indigo-950/40"><IconBuildingStore size={18} /></span><div><p className="font-semibold text-slate-800 dark:text-slate-200">{business.name}</p><p className="text-xs text-slate-400">{business.registration_number || "No registration number"}</p></div></div></td><td className="px-5 py-4"><p className="text-slate-700 dark:text-slate-300">{business.owner?.name || "Unassigned"}</p><p className="text-xs text-slate-400">{business.owner?.email}</p></td><td className="px-5 py-4 text-slate-600 dark:text-slate-300">{business.users_count}</td><td className="px-5 py-4"><span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold capitalize text-slate-600 dark:bg-slate-800 dark:text-slate-300">{business.status}</span></td><td className="px-5 py-4 text-right"><Link href={route("platform.businesses.show", business.id)} className="font-semibold text-indigo-600 hover:text-indigo-800">View</Link></td></tr>)}</tbody></table></div></div>
            </div>
        </DashboardLayout>
    );
}
