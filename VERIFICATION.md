# Verification — Tailwind v3 complete update

Validation performed on 11 September 2026. This extends the existing project and its earlier Tailwind conversion.

## Executed successfully

- Tailwind production build: compiled `public/assets/app.css` included. Page styles remain in PHP/JS utility classes; bundled Bengali fonts are registered through Tailwind config. Quill's vendor CSS is retained.
- PHP parsing: all 66 `.php` files plus `bin/console` passed syntax parsing with PHP WebAssembly.
- 52 pages rendered using the actual PHP controllers/services, with strict error reporting and an isolated SQLite fixture. No PHP warnings. Covered public pages, category filtering, admin lists, add/edit pages, settings groups, order detail, reservation/message/payment detail and tracking JSON.
- 23 v3 regression checks passed: bilingual persistence and selection, sanitization, search in either language, receipt name snapshots, historical HTML stripping, a delivery order below the former minimum, pickup/delivery pricing, idempotent checkout, full delivery event history, terminal state restrictions, phone normalization and repeated additive upgrade preservation.
- Existing PHP language checks: 24 passed. Raw script/style content is preserved during HTML localization.
- Existing rich-text safety/formatting checks passed.
- Existing JavaScript language controls: 6 checks passed.
- Existing upload response handling: 4 checks passed.
- Chromium: 20 pages at 1440, 768, 390 and 320 pixels. No horizontal overflow or uncaught JavaScript errors. Includes Bengali pages with bundled font loading.
- Browser interactions: home category links remain on the home section, delivery buttons update fee/total and required address, language tabs retain both unsaved drafts, editor color controls have correct dimensions, media selection updates form values, tracking updates status/progress and cancellation display.
- `app.js`/`admin.js` syntax checks and Python HTTP test compilation passed.
- Final ZIP integrity and essential source/build/setup files verified.

## Boundaries of these checks

No live MySQL server or live restaurant database was connected. SQL-backed service tests and controller renders used an isolated SQLite test adapter; this is not proof of MySQL DDL execution. Production code still requires MySQL. The additive MySQL schema changes were reviewed, but actual `install` / `upgrade` on MySQL remain a local environment check.

The browser media-upload test used a simulated successful upload response; tracking refresh used simulated status responses. Real PHP multipart handling, filesystem permissions, login/CSRF over HTTP, tracking lookup throttling on MySQL and payment gateway transactions were not tested end-to-end in this environment. Existing upload code and error handling were preserved.

## Run on a disposable MySQL test server

From the project root, configure DB_HOST/DB_PORT/DB_USERNAME/DB_PASSWORD for a test server whose account can create/drop isolated test databases. Never use production data for browser tests.

```powershell
$env:ALLOW_QA_WRITES = "true"
php tests/run.php
php tests/v3.php
php tests/richtext.php
php tests/locale.php
node tests/upload-client.cjs
node tests/language-ui.cjs
python tests/http_checks.py
```

See `tests/README.md` for browser test prerequisites. For normal installation, follow `setup.md`; for an existing database, follow `UPDATE-V3-BN.md`.
