import { Head } from "@inertiajs/react";
import PublicLayout from "@/Layouts/PublicLayout";
import {
    IconShoppingCart,
    IconBuildingWarehouse,
    IconTruckDelivery,
    IconReportMoney,
    IconUsers,
    IconChartBar,
    IconShieldLock,
    IconCreditCard,
    IconCheck,
    IconArrowRight,
} from "@tabler/icons-react";

const GITHUB_URL = "https://github.com/aryadwiputra/point-of-sales";

const modules = [
    {
        icon: IconShoppingCart,
        title: "POS & Transactions",
        desc: "The heart of Dikasir — a fast, flexible, and reliable cashier system for everyday use.",
        screenshot: "/screenshots/02-pos-checkout.png",
        features: [
            "Product search by barcode / keyword",
            "Barcode scanning with camera (PWA)",
            "Multi-item cart with hold & resume",
            "Multi-payment checkout: cash, transfer, Midtrans, Xendit, pay later",
            "Multi-unit products (pcs, box, kg, carton) with stock conversion",
            "Promo engine: discounts, qty break, bundle, buy-x-get-y",
            "Discount approval workflow",
            "Thermal receipt printing 58/80mm (WebUSB)",
            "Offline mode — transactions continue without internet",
        ],
    },
    {
        icon: IconBuildingWarehouse,
        title: "Inventory & Multi-Warehouse",
        desc: "Full control over stock across all warehouses and branches.",
        screenshot: "/screenshots/07-warehouses.png",
        features: [
            "Products, categories, and barcode management",
            "Separate stock per warehouse / branch",
            "Stock transfer between warehouses (draft → send → receive)",
            "Stock opname per warehouse",
            "Complete stock mutation history",
            "Batch & expiry tracking (FEFO)",
            "Composite products / kits",
            "Reorder point + automatic PO recommendations",
            "Low-stock notifications",
        ],
    },
    {
        icon: IconTruckDelivery,
        title: "Purchasing & Supplier",
        desc: "A clean procurement flow from PO to supplier payables.",
        screenshot: "/screenshots/09-purchase-orders.png",
        features: [
            "Purchase Order (draft → ordered → partial → completed)",
            "Goods Receiving with batch input",
            "Supplier Returns",
            "Supplier master data management",
            "Supplier payables with aging analysis",
        ],
    },
    {
        icon: IconReportMoney,
        title: "Finance & Receivables",
        desc: "Cash flow stays under control — receivables, payables, and taxes in one place.",
        screenshot: "/screenshots/12-receivables.png",
        features: [
            "Customer receivables with partial payment",
            "Aging analysis + collection notes",
            "Supplier payables & payment tracking",
            "11% VAT (exclusive/inclusive) with NPWP data",
            "Customer portal: customers view invoices and pay online",
        ],
    },
    {
        icon: IconUsers,
        title: "CRM & Loyalty",
        desc: "Grow your business with customers who come back again and again.",
        screenshot: "/screenshots/19-members.png",
        features: [
            "Customer management + Indonesian region support",
            "Member tiers (regular, silver, gold, platinum)",
            "Loyalty points (earn & redeem)",
            "Customer vouchers",
            "Automated segmentation (manual & rule-based)",
            "Campaign automation: reminders & promos",
            "WhatsApp Gateway: receipts, reminders, and automated promos",
        ],
    },
    {
        icon: IconChartBar,
        title: "Reports & Insight",
        desc: "Business decisions based on data, not gut feeling.",
        screenshot: "/screenshots/15-sales-report.png",
        features: [
            "Sales reports with filters & summaries",
            "Profit & margin analysis",
            "Advanced insights: peak hours, cashier performance, repeat customers",
            "PDF invoice, receipt (80/58mm), shipping label",
            "PDF receivables & payables",
            "Excel export (products, customers, transactions)",
        ],
    },
    {
        icon: IconShieldLock,
        title: "Admin & Security",
        desc: "Clear visibility on who can access what and who changed what.",
        screenshot: "/screenshots/31-audit-logs.png",
        features: [
            "Full RBAC: users, roles, permissions",
            "Audit log with before/after snapshots",
            "Step-up authentication for sensitive actions",
            "Excel import for products & customers",
            "Centralized app versioning (APP_VERSION)",
            "Cashier shift management",
        ],
    },
    {
        icon: IconCreditCard,
        title: "Payments & Settings",
        desc: "Accept whatever payment method your customers use.",
        screenshot: "/screenshots/24-payment-settings.png",
        features: [
            "Payment gateways: Midtrans & Xendit",
            "Bank accounts for manual transfers",
            "Multiple price lists per customer group",
            "Sales targets & store profile",
            "Printer & tax settings",
            "Multi-language: Indonesia & English",
        ],
    },
];

export default function Features() {
    return (
        <PublicLayout active="/fitur">
            <Head title="Full Features — Dikasir" />

            <section className="pt-20 pb-14 px-6 bg-gradient-to-b from-primary-50 dark:from-primary-950/40 to-transparent">
                <div className="max-w-7xl mx-auto text-center">
                    <h1 className="text-4xl md:text-5xl font-extrabold text-slate-900 dark:text-white">
                        Full Features for Real Businesses
                    </h1>
                    <p className="mt-5 text-lg text-slate-600 dark:text-slate-400 max-w-3xl mx-auto">
                        44+ integrated modules across 8 areas — from daily cashier tasks to advanced analytics, all free and open source.
                    </p>
                    <div className="mt-8 flex flex-wrap justify-center gap-3">
                        {modules.map((m) => (
                            <a
                                key={m.title}
                                href={`#${m.title.toLowerCase().replace(/[^a-z0-9]+/g, "-")}`}
                                className="px-4 py-2 text-sm font-medium bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-300 hover:border-primary-300 dark:hover:border-primary-700 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                            >
                                {m.title}
                            </a>
                        ))}
                    </div>
                </div>
            </section>

            <section className="pb-20 px-6">
                <div className="max-w-7xl mx-auto space-y-20">
                    {modules.map((mod, idx) => (
                        <div
                            key={mod.title}
                            id={mod.title.toLowerCase().replace(/[^a-z0-9]+/g, "-")}
                            className={`flex flex-col ${idx % 2 === 1 ? "lg:flex-row-reverse" : "lg:flex-row"} gap-10 items-center`}
                        >
                            <div className="flex-1">
                                <div className="flex items-center gap-3 mb-4">
                                    <div className="w-11 h-11 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center">
                                        <mod.icon size={22} className="text-white" />
                                    </div>
                                    <h2 className="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white">
                                        {mod.title}
                                    </h2>
                                </div>
                                <p className="text-slate-600 dark:text-slate-400 mb-5">
                                    {mod.desc}
                                </p>
                                <ul className="space-y-2.5">
                                    {mod.features.map((f) => (
                                        <li key={f} className="flex items-start gap-2.5">
                                            <IconCheck
                                                size={18}
                                                className="text-emerald-500 mt-0.5 shrink-0"
                                            />
                                            <span className="text-sm text-slate-700 dark:text-slate-300">
                                                {f}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            </div>

                            <div className="flex-1 w-full">
                                <div className="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-xl bg-white dark:bg-slate-900">
                                    <img
                                        src={mod.screenshot}
                                        alt={`Screenshot ${mod.title}`}
                                        className="w-full"
                                        loading="lazy"
                                    />
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </section>

            <section className="pb-20 px-6">
                <div className="max-w-4xl mx-auto text-center">
                    <h2 className="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-4">
                        Built to Grow With Your Business
                    </h2>
                    <p className="text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
                        Dikasir combines cashier speed, warehouse control, customer loyalty, and financial insight in one platform.
                    </p>
                    <div className="mt-8 flex justify-center">
                        <a
                            href={GITHUB_URL}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex items-center gap-2 px-6 py-3 font-semibold rounded-xl bg-primary-500 text-white hover:bg-primary-600 transition-colors"
                        >
                            View on GitHub
                            <IconArrowRight size={16} />
                        </a>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
