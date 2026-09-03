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

## Validation Completed

- Frontend production build passes with `npm run build`.
- Authentication tests pass: 21 tests.
- Login page responds with HTTP 200.
- Login page renders with `lang="en"`.
- SQLite database path is configured as `database/database.sqlite` for local development.

## Remaining Work

1. Translate CRM, loyalty, promotions, and dine-in pages.
2. Translate reports and settings pages.
3. Translate backend flash messages and API responses carefully.
4. Translate PDF and document labels.
5. Review date, number, and currency formatting separately from text translation.
6. Run a final Indonesian-text scan and full validation.

## Working Rules

- Add translation keys to both `en.json` and `id.json`.
- Use `t("...")` in React instead of replacing text globally.
- Keep route names, permission names, database fields, API keys, SQL, and status codes unchanged.
- Do not translate user-entered data, product names, customer names, or campaign content automatically.
- Preserve domain terms such as QRIS, PPN, SKU, FEFO, and NPWP unless a product decision says otherwise.
- Validate each feature slice with a frontend build and the narrowest relevant tests.
- Do not start the next feature slice until the current slice is reviewed and stable.

## Suggested Next Slice

CRM, loyalty, promotions, and dine-in pages. Keep this slice limited to those screens, then build and test before continuing to reports and settings.
