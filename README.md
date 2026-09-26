# Point of Sales

A complete retail management platform built with Laravel, Inertia.js, and React. The application connects checkout, inventory, purchasing, finance, customer management, loyalty, reporting, and operational controls in one system.

It is designed for retail stores, supermarkets, and businesses with multiple cashiers or warehouse locations. Users can manage the full business workflow from purchasing stock through selling products, collecting payments, engaging customers, and reviewing performance.

> 200+ GitHub stars • Open-source • MIT License

## What This Application Provides

- **Sales and checkout:** barcode/product search, camera scanning, held carts, multiple payment methods, sales returns, cashier shifts, tax calculation, and offline transaction sync.
- **Inventory and warehouses:** product and category management, stock opname, stock history, batch and expiry tracking, unit conversion, composite products, low-stock alerts, and transfers between warehouses.
- **Purchasing and suppliers:** purchase orders, goods receiving, supplier returns, and supplier payable aging.
- **Finance:** customer receivables, partial payments, aging analysis, collection notes, PPN/PPh settings, and online customer payment through the portal.
- **CRM and loyalty:** customer profiles, member tiers, points, vouchers, price lists, promotions, customer segments, campaigns, reminders, and WhatsApp messaging.
- **Reports and documents:** sales, profit, and advanced insight reports; PDF invoices and receipts; shipping labels; and Excel/CSV import and export.
- **Administration and security:** role-based access control, permission management, discount approvals, audit logs, store settings, payment gateways, bank accounts, and printer configuration.

## Business Workflow

```text
Purchase stock → Receive goods → Manage warehouse inventory
  → Sell products → Collect payment → Engage customers
  → Review reports and business performance
```

The platform supports multi-warehouse operations, Indonesian tax requirements, thermal printers, payment gateways, a mobile-friendly PWA, and an optional WhatsApp Gateway for automated customer communication.

## Pages by User Type

This application is designed for several kinds of users. The main pages are organized around the work each role performs.

### 1) Store Owner / Business Owner

These users need a high-level overview of the business and operational health.

- Dashboard: summary of sales, inventory, cash flow, recent activity, and alerts
- Sales Reports: daily, weekly, and monthly sales performance
- Profit Reports: margin and financial overview by item, category, and time period
- Receivables and Payables: customer debts and supplier obligations
- Cashier Shifts: open/close cashier sessions, cash reconciliation, and shift reports
- Warehouses: location overview and stock distribution
- Settings: business profile, payment configuration, store preferences, app versioning
- Audit Logs: system activity for accountability and review

### 2) Cashier / Frontline Sales Staff

These users focus on the point-of-sale flow and transaction completion.

- POS Checkout: add products, search by barcode or keyword, apply discounts, choose payment method
- Held Carts: save carts temporarily and resume later
- Transactions History: view recent sales and receipts
- Customer Lookup: add or check customer information during checkout
- Cashier Shift: open shift, record cash, and close at the end of the day
- Sales Returns: handle product returns and refund processing
- Transaction Printing: print invoices and receipts via thermal printer

### 3) Inventory / Warehouse Staff

These users manage stock movement and product availability.

- Products: create, edit, price, inventory details, and product images
- Categories: organize products into groups
- Stock Opnames: count physical inventory and reconcile stock levels
- Stock Mutations: review stock movement and adjustments
- Stock Transfers: move items between warehouses or branches
- Purchase Orders: create and track supplier orders
- Goods Receiving: receive stock from suppliers and update inventory
- Supplier Returns: return damaged or incorrect items to suppliers

### 4) Finance / Accounting Team

These users review money movement and control approval flows.

- Receivables and Aging: track customer credit and payment timelines
- Payables: monitor supplier debts and due dates
- Bank Accounts: reconcile payment destinations and accounts
- Expense Management: submit, review, approve, reject, and cancel business expenses
- Reports: profit, sales, and financial summaries by period
- Settings and payment gateways: validate payment methods and platform configuration

### 5) CRM / Marketing / Customer Engagement Team

These users work on customer retention and campaigns.

- Customers and Members: customer profiles, membership records, and loyalty tracking
- Customer Segments: group customers by behavior or attributes
- Customer Vouchers: manage customer discounts or credits
- CRM Campaigns: schedule customer programs and outreach
- CRM Reminders: follow up on customer touchpoints
- WhatsApp Gateway: send automated messages and reminders through WhatsApp

### 6) Admin / System Manager

These users manage permissions, business configuration, and access control.

- Users and Roles: create users, assign roles, and define permission access
- Permissions: manage authorization rules and access matrix
- Audit Logs: track important changes and user actions
- Business Settings: configure payment settings, language, integrations, and operational rules
- Warehouse and Store Setup: configure locations and operational structure
- Import / Export: manage bulk product and customer data operations

### 7) Developers / Integrators

These users work on technical integrations and platform extension.

- API Documentation: OpenAPI and interactive endpoint documentation
- Public Routes: portal and document sharing pages
- Health Check: system and service status endpoint
- WhatsApp Service: separate Node service for message automation
- Business Rules: inventory, pricing, payments, and reporting logic in Laravel services
- Integration Points: midtrans, xendit, payment settings, and document export flows

---

## Cuplikan Layar

| POS Checkout | Dashboard | Stock Opname |
|:---:|:---:|:---:|
| ![POS](public/screenshots/02-pos-checkout.png) | ![Dashboard](public/screenshots/01-dashboard.png) | ![Stock Opname](public/screenshots/06-stock-opnames.png) |
| **Sales Report** | **Receivables** | **Multi-Warehouse** |
| ![Sales Report](public/screenshots/15-sales-report.png) | ![Receivables](public/screenshots/12-receivables.png) | ![Warehouses](public/screenshots/07-warehouses.png) |

📸 **[Lihat galeri lengkap (33 screenshot)](docs/screenshots.md)**

---

## Fitur Utama

### POS & Transaksi
- Pencarian produk via barcode / keyword
- Barcode scanner via kamera (PWA)
- Cart multi-item dengan hold/resume
- Checkout multi-metode: tunai, transfer bank, Midtrans, Xendit, pay later
- Multi-satuan produk (pcs, box, kg, karton) dengan konversi stok otomatis
- Multi-price list: harga berbeda per kelompok pelanggan
- Promo engine: diskon, qty break, bundle, buy-x-get-y
- Diskon dengan approval workflow
- PPN 11% (exclusive/inclusive)
- Thermal printer support (WebUSB)
- Offline mode (queue transaksi saat offline, sync saat online)

### Inventory & Multi-Warehouse
- Manajemen produk + kategori + barcode
- Stok terpisah per gudang/cabang
- Transfer stok antar warehouse (draft → send → receive)
- Stock opname per warehouse
- Stock mutation history
- Batch/expiry date tracking (FEFO)
- Composite products / kits
- Reorder point + auto-PO suggestion
- Low stock notification

### Purchasing
- Purchase Order (draft → ordered → partial → completed)
- Goods Receiving (dengan input batch)
- Supplier Returns
- Payables (hutang supplier) dengan aging

### Finance
- Receivables (piutang pelanggan) dengan partial payment
- Aging analysis + collection notes
- PPN/PPh tax management
- Customer portal: lihat invoice + bayar piutang online

### CRM & Loyalty
- Customer management + wilayah Indonesia
- Member tiers (regular, silver, gold, platinum)
- Poin loyalty (earn/redeem)
- Voucher customer
- Customer segments (manual & auto)
- Campaign automation (reminder, promo broadcast)
- **WhatsApp Gateway** — kirim pesan otomatis via whatsapp-web.js (QR scan, session persistent)

### Reports & Documents
- Sales report + filter + summary
- Profit report + margin analysis
- Advanced sales insights (hourly, cashier performance, repeat customer)
- PDF invoice, receipt (80mm/58mm), shipping label
- PDF receivable/payable
- Export ke Excel (produk, customer, transaksi)

### Admin
- Full RBAC (users, roles, permissions)
- Audit log (before/after snapshot)
- Import produk & customer dari Excel
- **App Versioning** — versi aplikasi terpusat (`APP_VERSION`), tampil di sidebar + POS navbar

### Integrasi
- **WhatsApp Gateway** — terhubung via Node.js service (`whatsapp-service/`)
- **Payment Gateways** — Midtrans, Xendit

---

## Quick Start

### Docker

The supported container workflow runs Laravel behind Nginx with MySQL, Redis, a separate queue worker, and a separate scheduler. It does not require PHP, Composer, Node, MySQL, or Redis on the host.

```powershell
Copy-Item .env.example .env
docker compose -f compose.yaml -f compose.dev.yaml run --rm app php artisan key:generate --show
# Set the displayed value as APP_KEY in .env.
docker compose -f compose.yaml -f compose.dev.yaml up -d --build
docker compose -f compose.yaml -f compose.dev.yaml exec app php artisan migrate --seed
docker compose -f compose.yaml -f compose.dev.yaml exec app php artisan storage:link
```

Open `http://localhost:8080`. Use `docker compose -f compose.yaml -f compose.dev.yaml logs -f` for logs and see [DOCKER.md](DOCKER.md) for production deployment, backups, health checks, and troubleshooting.

### Host development

```bash
git clone https://github.com/aryadwiputra/point-of-sales.git
cd point-of-sales
cp .env.example .env
composer install && npm install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# Dev servers — jalankan semua di terminal terpisah
npm run dev          # Vite HMR
php artisan serve    # Laravel

# WhatsApp Gateway (opsional) — untuk kirim WA otomatis
cd whatsapp-service
npm install && npm start
```

## Default Login

- Admin: `cybertrinity6@gmail.com` / `password`
- Kasir: `cashier@gmail.com` / `Cheri@2026` (Charity Mainda)

## Dokumentasi Detail

| Dokumen | Isi |
|---------|-----|
| `docs/getting-started.md` | Setup lengkap |
| `docs/configuration.md` | Konfigurasi environment, payment, pajak, printer, WhatsApp |
| `docs/architecture-overview.md` | Arsitektur, middleware, service layer, Node service |
| `docs/feature-index.md` | Indeks semua modul (44 fitur) |

## REST API (OpenAPI)

Dikasir menyediakan REST API untuk integrasi mobile app / pihak ketiga. Dokumentasi interaktif otomatis (Scramble) tersedia di:

- **UI docs:** `/docs/api` — coba endpoint langsung dari browser (Try It)
- **OpenAPI spec:** `/docs/api.json` — untuk generate client (Postman, OpenAPI Generator, Swagger Codegen)

Semua endpoint (kecuali `auth/login`, `auth/register`, webhooks) memerlukan **Bearer token**:

```bash
# 1. Login → dapat token
curl -X POST https://dikasir.web.id/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
# → {"token": "1|abc123...", "user": {...}}

# 2. Panggil API dengan token
curl https://dikasir.web.id/api/v1/products \
  -H "Authorization: Bearer 1|abc123..."
```

**Modul yang tersedia** (`/api/v1/`):

| Modul | Endpoint | Keterangan |
|-------|----------|------------|
| Auth | `auth/login`, `auth/logout`, `auth/me`, `auth/register` | Token-based (Sanctum) |
| Produk | `products` | CRUD + search + kategori |
| Pelanggan | `customers` | CRUD + loyalty member |
| Kategori | `categories` | CRUD |
| Gudang | `warehouses` | CRUD (multi-warehouse) |
| Supplier | `suppliers` | CRUD |
| POS | `pos/shift`, `pos/products`, `pos/cart`, `pos/hold`, `pos/checkout`, `pos/transactions` | Alur kasir lengkap (mobile) |

Base URL: `https://dikasir.web.id/api/v1` (dev: `http://localhost:8000/api/v1`)

### Per Modul

- POS & Transaksi, Sales Return, Cashier Shift
- Inventory: produk, stock opname, mutation, warehouse, stock transfer, batch, composite
- Purchasing: PO, goods receiving, supplier return, payables
- Finance: receivables, PPN
- Pricing: pricing rules, price list, loyalty, vouchers
- CRM: member, segments, campaigns, **WhatsApp Gateway**
- Reports: sales, profit, insights, dokumen PDF
- Tools: import/export, mobile POS, thermal printer, offline mode

## Troubleshooting Umum

1. **Permission cache stale setelah seeding** — logout lalu login lagi
2. **Webhook Midtrans/Xendit tidak bekerja** — pastikan `APP_URL` public, bukan `localhost`
3. **Gambar produk tidak tampil** — jalankan `php artisan storage:link`
4. **Route error 500** — jalankan `php artisan migrate` untuk modul baru
5. **Test gagal karena PPN** — pastikan `tax_rate=0` di test Product::create
6. **Vite tidak jalan** — pastikan `npm run dev` berjalan, jangan hanya `php artisan serve`
7. **WhatsApp QR tidak muncul** — pastikan `whatsapp-service/` sudah jalan (`npm start`)
8. **WhatsApp terputus** — klik "Hubungkan Ulang" di Settings > WhatsApp, scan ulang QR

## Kontribusi

1. Branch dari `development`: `git checkout -b feature/nama-fitur development`
2. Buat PR ke `development`
3. PR ke `main` hanya dari `development` via branching release

Pastikan `php artisan test` lulus sebelum PR.

## Lisensi

MIT License
