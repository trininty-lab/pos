import React from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Head, Link } from "@inertiajs/react";
import { IconArrowLeft, IconEdit, IconPhoto } from "@tabler/icons-react";
import { getProductImageUrl } from "@/Utils/imageUrl";

export default function Show({ product }) {
    const image = getProductImageUrl(product.image_variants || product.image, "large");

    return (
        <>
            <Head title={product.title} />
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <Link href={route("products.index")} className="mb-3 inline-flex items-center gap-2 text-sm text-slate-500 hover:text-primary-600">
                        <IconArrowLeft size={16} /> Back to Products
                    </Link>
                    <h1 className="text-2xl font-bold text-slate-900 dark:text-white">{product.title}</h1>
                </div>
                <Link href={route("products.edit", product.id)} className="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-600">
                    <IconEdit size={17} /> Edit product
                </Link>
            </div>
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div className="aspect-square overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 dark:border-slate-800 dark:bg-slate-900">
                    {image ? <img src={image} alt={product.title} className="h-full w-full object-cover" /> : <div className="flex h-full items-center justify-center"><IconPhoto size={56} className="text-slate-400" /></div>}
                </div>
                <div className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900 lg:col-span-2">
                    <div><p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Category</p><p className="mt-1 text-lg text-slate-900 dark:text-white">{product.category?.name || "Uncategorized"}</p></div>
                    <div><p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Description</p><p className="mt-1 whitespace-pre-wrap text-slate-700 dark:text-slate-300">{product.description || "No description"}</p></div>
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <div><p className="text-xs text-slate-500">Selling price</p><p className="font-semibold text-slate-900 dark:text-white">KSh {Number(product.sell_price || 0).toLocaleString()}</p></div>
                        <div><p className="text-xs text-slate-500">Stock</p><p className="font-semibold text-slate-900 dark:text-white">{product.stock}</p></div>
                        <div><p className="text-xs text-slate-500">SKU</p><p className="font-semibold text-slate-900 dark:text-white">{product.sku || "-"}</p></div>
                    </div>
                </div>
            </div>
        </>
    );
}

Show.layout = (page) => <DashboardLayout children={page} />;