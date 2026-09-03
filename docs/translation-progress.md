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

## Validation Completed

- Frontend production build passes with `npm run build`.
- Authentication tests pass: 21 tests.
- Login page responds with HTTP 200.
- Login page renders with `lang="en"`.
- SQLite database path is configured as `database/database.sqlite` for local development.

## Remaining Work

1. Translate the POS checkout workflow and shared POS components.
2. Translate master data pages: products, categories, customers, and suppliers.
3. Translate inventory and warehouse pages.
4. Translate purchasing, receivables, and payables pages.
5. Translate CRM, loyalty, promotions, and dine-in pages.
6. Translate reports and settings pages.
7. Translate backend flash messages and API responses carefully.
8. Translate PDF and document labels.
9. Review date, number, and currency formatting separately from text translation.
10. Run a final Indonesian-text scan and full validation.

## Working Rules

- Add translation keys to both `en.json` and `id.json`.
- Use `t("...")` in React instead of replacing text globally.
- Keep route names, permission names, database fields, API keys, SQL, and status codes unchanged.
- Do not translate user-entered data, product names, customer names, or campaign content automatically.
- Preserve domain terms such as QRIS, PPN, SKU, FEFO, and NPWP unless a product decision says otherwise.
- Validate each feature slice with a frontend build and the narrowest relevant tests.
- Do not start the next feature slice until the current slice is reviewed and stable.

## Suggested Next Slice

POS checkout workflow and shared POS components. Keep this slice limited to checkout, cart, payment, and receipt UI, then build and test before continuing to master data.
