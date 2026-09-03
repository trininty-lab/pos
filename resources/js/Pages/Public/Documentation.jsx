import { Head } from "@inertiajs/react";
import PublicLayout from "@/Layouts/PublicLayout";
import { IconBook2, IconArrowRight, IconBrandGithub } from "@tabler/icons-react";

const GITHUB_URL = "https://github.com/aryadwiputra/point-of-sales";
const DOCS_BASE = `${GITHUB_URL}/blob/main/docs`;

const categories = [
    {
        title: "Quick Start",
        docs: [
            { file: "getting-started.md", title: "Getting Started", desc: "Complete setup guide from scratch to logging in and accessing the dashboard." },
            { file: "configuration.md", title: "Configuration", desc: "Environment, payment gateways, tax, thermal printer setup, and WhatsApp." },
            { file: "architecture-overview.md", title: "Architecture", desc: "Code structure, service layer, middleware, and Node service overview." },
            { file: "feature-index.md", title: "Feature Index", desc: "List of all 44+ modules and their current status." },
        ],
    },
    {
        title: "POS & Transactions",
        docs: [
            { file: "features/pos-transactions.md", title: "POS Transactions", desc: "Cart flow, checkout, hold/resume, and multi-payment handling." },
            { file: "features/cashier-shifts.md", title: "Cashier Shifts", desc: "Open/close shifts and cash recap by shift." },
            { file: "features/sales-returns.md", title: "Sales Returns", desc: "Process returns from completed transactions." },
            { file: "features/mobile-pos.md", title: "Mobile POS (PWA)", desc: "Use the cashier from your phone — installable and offline-ready." },
            { file: "features/thermal-printer.md", title: "Thermal Printer", desc: "Print 58/80mm receipts via WebUSB." },
        ],
    },
    {
        title: "Inventory & Warehouse",
        docs: [
            { file: "features/inventory-stock.md", title: "Inventory & Stock", desc: "Products, categories, stock opname, and stock mutations." },
            { file: "features/multi-warehouse.md", title: "Multi-Warehouse", desc: "Warehouse stock management and inter-warehouse transfers." },
            { file: "features/unit-conversion.md", title: "Multi-Unit", desc: "Product unit conversion (pcs, box, kg, carton)." },
        ],
    },
    {
        title: "Purchasing & Finance",
        docs: [
            { file: "features/purchasing-chain.md", title: "Procurement Chain", desc: "Purchase orders, goods receiving, and supplier returns." },
            { file: "features/payables-suppliers.md", title: "Suppliers & Payables", desc: "Manage suppliers and debts." },
            { file: "features/receivables.md", title: "Receivables", desc: "Customer receivables and partial payments." },
            { file: "features/tax-management.md", title: "Tax Management", desc: "VAT, NPWP, and NIB." },
            { file: "features/customer-portal.md", title: "Customer Portal", desc: "Self-service portal: view invoices and pay online." },
        ],
    },
    {
        title: "CRM & Loyalty",
        docs: [
            { file: "features/crm-segments.md", title: "Segments & Campaigns", desc: "Automatic segmentation and marketing campaigns." },
            { file: "features/member-management.md", title: "Member Management", desc: "Member tiers and loyalty points." },
            { file: "features/promotions-loyalty.md", title: "Promotions & Loyalty", desc: "Pricing rules, vouchers, and loyalty programs." },
        ],
    },
    {
        title: "Admin & Tools",
        docs: [
            { file: "features/rbac-users-roles.md", title: "RBAC", desc: "Users, roles, and permissions." },
            { file: "features/audit-logs.md", title: "Audit Log", desc: "Before/after change history." },
            { file: "features/settings-payments.md", title: "Payment Settings", desc: "Midtrans, Xendit, and bank accounts." },
            { file: "features/import-export.md", title: "Import/Export", desc: "Products and customers via Excel." },
            { file: "features/reports-documents.md", title: "Reports & Documents", desc: "Reports and PDF documents." },
            { file: "features/whatsapp-gateway.md", title: "WhatsApp Gateway", desc: "whatsapp-web.js integration." },
        ],
    },
];

export default function Documentation() {
    return (
        <PublicLayout active="/dokumentasi">
            <Head title="Documentation — Dikasir" />

            <section className="pt-20 pb-14 px-6 bg-gradient-to-b from-primary-50 dark:from-primary-950/40 to-transparent">
                <div className="max-w-4xl mx-auto text-center">
                    <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 text-sm font-medium mb-5 border border-primary-100 dark:border-primary-900">
                        <IconBook2 size={16} />
                        Documentation
                    </div>
                    <h1 className="text-4xl md:text-5xl font-extrabold text-slate-900 dark:text-white">
                        Complete Documentation
                    </h1>
                    <p className="mt-5 text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
                        All guides are available in the GitHub repository — always current and evolving with the code.
                    </p>
                </div>
            </section>

            <section className="pb-20 px-6">
                <div className="max-w-5xl mx-auto space-y-14">
                    {categories.map((cat) => (
                        <div key={cat.title}>
                            <h2 className="text-xl font-bold text-slate-900 dark:text-white mb-5 flex items-center gap-3">
                                <span className="w-8 h-1 rounded-full bg-gradient-to-r from-primary-500 to-primary-600" />
                                {cat.title}
                            </h2>
                            <div className="grid sm:grid-cols-2 gap-4">
                                {cat.docs.map((doc) => (
                                    <a
                                        key={doc.file}
                                        href={`${DOCS_BASE}/${doc.file}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="group p-5 rounded-xl bg-white dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-md transition-all"
                                    >
                                        <div className="flex items-center justify-between mb-2">
                                            <h3 className="font-semibold text-slate-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                                {doc.title}
                                            </h3>
                                            <IconArrowRight size={16} className="text-slate-400 group-hover:text-primary-500 group-hover:translate-x-0.5 transition-all" />
                                        </div>
                                        <p className="text-sm text-slate-500 dark:text-slate-400">
                                            {doc.desc}
                                        </p>
                                    </a>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            </section>

            <section className="pb-20 px-6">
                <div className="max-w-3xl mx-auto">
                    <div className="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 p-8 text-center">
                        <h2 className="text-xl font-bold text-slate-900 dark:text-white mb-2">
                            Documentation unclear?
                        </h2>
                        <p className="text-slate-600 dark:text-slate-400 mb-6">
                            Documentation is also open source — improve it and open a PR, or ask in GitHub Discussions.
                        </p>
                        <div className="flex flex-col sm:flex-row items-center justify-center gap-3">
                            <a
                                href={`${GITHUB_URL}/blob/main/docs/README.md`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all"
                            >
                                <IconBrandGithub size={18} />
                                View all docs on GitHub
                            </a>
                            <a
                                href={`${GITHUB_URL}/discussions`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl hover:border-primary-300 transition-colors"
                            >
                                Ask in Discussions
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
