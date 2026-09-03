# Translation Progress

Last updated: 2026-09-03

This document tracks the Indonesian-to-English translation work. Translation changes must be completed one feature area at a time and validated before moving on.

## Completed

### Baseline and language configuration

- English is the default Laravel locale.
- English is the React i18n fallback language.
- Login and sidebar navigation already use translation keys.
- Indonesian remains available through the language switcher.
- The PHP 8.5 MySQL SSL constant warning was fixed.
- Email verification is not required for login or dashboard access.
- The seeded admin account uses `cybertrinity6@gmail.com`.
- The earlier baseline changes were committed in `667e033` (`Prepare POS app for English login`).

### Authentication screens

- Login was already covered by the existing translation keys.
- Register now uses scoped translation keys.
- Forgot password now uses scoped translation keys.
- Reset password now uses scoped translation keys.
- Confirm password now uses scoped translation keys.
- Email verification now uses scoped translation keys.
- Both `en.json` and `id.json` contain the new authentication keys.

### Dashboard home and access

- Dashboard widgets now use translation keys.
- Dashboard access cards now use translation keys.
- Dashboard routes, permissions, and card destinations were unchanged.
- Both `en.json` and `id.json` contain the dashboard access keys.
- The core dashboard home view was translated to English for key KPI cards, charts, alerts, and summary sections.
- The public-facing roadmap, documentation, and contribution pages were translated to English for the main user experience flow.

### POS checkout and shared components

- Cart labels and empty-cart states now use translation keys.
- Product grid stock, add, promotion, and search labels now use translation keys.
- Shared POS search placeholder now uses translation keys while preserving custom placeholders.
- Payment panel labels, checkout states, voucher, loyalty, bank, cash, and payment-link text now use translation keys.
- Product names, payment values, currency formatting, callbacks, and transaction logic were unchanged.

### Products

- Product list, create, and edit screens now use English visible text.
- Product field names, routes, permissions, API payloads, and product data were unchanged.
- Product integration tests and the frontend build pass.

### Categories

- Category list, create, and edit screens now use English visible text.
- Category CRUD behavior, routes, permissions, and upload handling were unchanged.
- Category pages compile successfully with the rest of the frontend.

### Customers and suppliers

- Customer list, create, edit, and detail screens now use English visible text.
- Supplier management labels, confirmation, empty state, and form actions now use English visible text.
- Customer and supplier routes, permissions, payloads, and server-provided data were unchanged.

### Warehouses

- Warehouse settings now use English visible text, including form labels, type names, confirmation, and empty state.
- Warehouse routes, permissions, data fields, and form behavior were unchanged.

### Stock mutations

- Stock mutation filters, table headings, description, and empty state now use English visible text.
- Stock mutation types, filters, routes, data fields, and date formatting were unchanged.

### Stock opname

- Stock opname list, creation, and detail workflows now use English visible text.
- Stock opname validation messages, audit statuses, routes, submitted fields, and calculation behavior were unchanged.

### Stock transfers

- Stock transfer list, creation, and detail workflows now use English visible text.
- Stock transfer statuses, confirmation actions, routes, submitted fields, and warehouse/product data were unchanged.

### Purchase orders

- Purchase order list, creation, and detail workflows now use English visible text.
- Purchase order statuses, routes, submitted fields, receiving actions, payable links, and calculations were unchanged.

### Receivables, payables, and goods receiving

- Receivables list, aging, detail, payment, collection notes, and preview screens now use English visible text.
- Payables list, detail, payment, and preview screens now use English visible text.
- Goods receiving list, creation, and detail screens now use English visible text.
- Financial and receiving routes, statuses, submitted fields, calculations, and action behavior were unchanged.

### CRM campaigns

- CRM campaign list, creation/edit form, detail, audience, delivery logs, and action feedback now use English visible text.
- Campaign content, audience option data, statuses, routes, permissions, payloads, and WhatsApp actions were unchanged.

### Loyalty members

- Member list, registration/edit form, detail, summaries, reward history, and empty states now use English visible text.
- Member CRUD routes, permissions, regional data, tier values, reward data, and form behavior were unchanged.

### Customer vouchers

- Customer voucher list, create/edit form, statuses, filters, and empty state now use English visible text.
- Voucher CRUD routes, discount values, dates, customer data, and form behavior were unchanged.

### Pricing rules

- Pricing rule list and complex promotion form now use English visible text.
- Rule types, target/scope values, quantity and bundle arrays, preview requests, calculations, and CRUD routes were unchanged.

### Dine-in tables

- Dine-in table grid/list views, filters, CRUD modal, statuses, confirmations, and controls now use English visible text.
- Table drag/drop layout, QR actions, filters, permissions, routes, and table data were unchanged.

### Dine-in areas and orders

- Dine-in area CRUD and dine-in order processing screens now use English visible text.
- Area and order routes, permissions, status values, payment options, prompts, and data behavior were unchanged.

### Reports and payment settings

- Sales report, profit report, and advanced insights visible labels, filters, summaries, tables, and empty states now use English text.
- Payment gateway settings headings, helper text, gateway labels, and save controls now use English text.
- Report calculations, chart data, filters, payment fields, credentials, routes, and configuration behavior were unchanged.

### Bank, loyalty, printer, and store settings

- Bank account management, loyalty settings, printer settings, and store profile screens now use English visible text.
- Settings routes, permissions, toggles, uploads, credentials, payloads, and update behavior were unchanged.

### Price lists, sales target, and WhatsApp settings

- Price list management, price-list items, sales target, and WhatsApp connection/settings screens now use English visible text.
- Price updates, target values, WhatsApp connection polling, test messages, routes, and configuration behavior were unchanged.

### Remaining settings forms

- Bank account create/edit form and remaining payment/store settings labels now use English visible text.
- Multipart submission, method spoofing, permissions, credentials, and settings behavior were unchanged.

### PDF and document labels

- Invoice, receivable, payable, and 58mm receipt templates now use English static labels.
- PDF calculations, document data, formatting, and receipt behavior were unchanged.

### Shared POS API messages

- Shared API fallback messages and POS cart, stock, shift, hold, and checkout messages now use English text.
- API response shapes, HTTP status codes, validation keys, route contracts, and business logic were unchanged.

### Application flash messages

- User-facing flash, JSON, and validation messages for customers, suppliers, warehouses, inventory, purchasing, receivables, payables, receiving, and settings now use English text.
- Message keys, response shapes, HTTP status codes, redirects, validation behavior, and business logic were unchanged.

## Validation Completed

- Frontend production build passes with `npm run build`.
- Authentication tests pass: 21 tests.
- Login page responds with HTTP 200.
- Login page renders with `lang="en"`.
- SQLite database path is configured as `database/database.sqlite` for local development.

## Remaining Work

1. Translate the remaining backend flash messages and API responses carefully.
2. Review date, number, and currency formatting separately from text translation.
3. Run a final Indonesian-text scan and full validation.
4. Continue the English pass for secondary admin pages and less critical dialogs.

## Current Progress Snapshot

- Public landing/marketing pages are translated and build cleanly.
- The main operational dashboard overview is translated to English.
- Translation documentation is kept updated as work progresses.
- Remaining tasks are mostly backend/user-facing validation copy and lower-priority secondary screens.

## Working Rules

- Add translation keys to both `en.json` and `id.json`.
- Use `t("...")` in React instead of replacing text globally.
- Keep route names, permission names, database fields, API keys, SQL, and status codes unchanged.
- Do not translate user-entered data, product names, customer names, or campaign content automatically.
- Preserve domain terms such as QRIS, PPN, SKU, FEFO, and NPWP unless a product decision says otherwise.
- Validate each feature slice with a frontend build and the narrowest relevant tests.
- Do not start the next feature slice until the current slice is reviewed and stable.

## Suggested Next Slice

Remaining settings pages. Keep each feature as a separate checkpoint, then build and test before final backend/PDF review.
