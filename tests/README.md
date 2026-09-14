> Current verification: see `../VERIFICATION.md`. MySQL HTTP/browser scripts are provided for your disposable local test server; they were not run against live MySQL in this delivery.

# Verification

This edition uses MySQL for runtime and core/HTTP tests. Supply DB_HOST, DB_PORT, DB_USERNAME and DB_PASSWORD for a disposable MySQL test server. The account must be allowed to CREATE/DROP DATABASE. Tests generate names matching `koji_test_<12 hex digits>` and never drop a configured business database.

```bash
ALLOW_QA_WRITES=true php tests/run.php
ALLOW_QA_WRITES=true php tests/v3.php
php tests/richtext.php
php tests/locale.php
node tests/language-ui.cjs
node tests/upload-client.cjs
ALLOW_QA_WRITES=true python3 tests/http_checks.py
```

PowerShell: set `$env:ALLOW_QA_WRITES="true"` first. Python HTTP checks use standard-library modules; set PHP_BINARY when PHP is not on PATH. They start their own server and create/drop an isolated database. Uploads/logs/sessions are temporary QA side effects in storage.

Browser checks require a disposable installed application and owner account:

```bash
npm install --no-save playwright
npx playwright install chromium
ALLOW_QA_WRITES=true TEST_BASE_URL=http://localhost:8000 \
TEST_OWNER_EMAIL=owner@example.com TEST_OWNER_PASSWORD='YourQAPassword' \
node tests/browser-v2.cjs
```

QA_SCREENSHOTS specifies an output directory. BROWSER_BINARY can specify an installed Chromium executable. Browser checks modify sample products and homepage images; never run them against live business data.
