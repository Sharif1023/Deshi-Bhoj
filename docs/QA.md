> Historical report for the previous Bangladesh update. For this delivery, use `../VERIFICATION.md`.

# Bangladesh update — verification report

Executed in this update:
- `node --check public/assets/admin.js` — passed.
- `node --check public/assets/app.js` — passed.
- `node tests/upload-client.cjs` — 4 checks passed: successful upload JSON, specific server error, redirected login, HTML error fallback.
- Python HTTP test file compiles.
- Source JSON and local demo image paths checked; archive integrity checked during packaging.

Not executed: PHP syntax/lint, MySQL installation or migration, real HTTP multipart uploads, payment transactions, browser rendering/workflows. PHP is unavailable in the editing runtime; installation was blocked by environment constraints. Earlier edition test counts are historical and are not evidence for this update.

Before production: run installation and doctor, upload JPG/PNG/WebP from product and homepage forms, save, reload admin, open the public product/gallery, and verify cover reorder/removal. Test a file over the PHP limit and verify the specific message. Run the existing service and HTTP suites against a disposable MySQL database using tests/README.md. The HTTP PNG assertion now expects the validated original bytes instead of WebP conversion.

Upload implementation: authenticated, CSRF-protected endpoint; verifies PHP upload provenance, actual file size, supported image header/MIME and pixel bounds; stores random filenames outside public; serves allowlisted image extensions with nosniff. GD no longer required. Missing/writable storage and PHP upload failures produce actionable errors. Originals retain metadata; use your own publication-ready photos.

Localization migration backs up content before a one-time update. Existing order/account/customer data is retained. Legacy demo product slugs are replaced in place; custom products are retained. Review existing custom content after migration.

## English / Bangla navbar update

- Source: the supplied `bangladeshi-food-website(1).zip`.
- PASS: `node --check public/assets/app.js`.
- PASS: `node tests/language-ui.cjs` — 6 checks, including both languages, mobile open/Escape labels, and preserving the current route, search, category and URL fragment when switching.
- PASS: all Bengali demo setting paragraphs, category labels/descriptions and product names/descriptions/badges/allergens have English catalogue entries; joined ingredient lists also have translations.
- PASS: the previous `setup.md` is included in full, with language-update instructions appended.
- Original admin templates, upload services, payment services, database migrations and assets are unchanged.
- PHP regression checks are supplied in `tests/locale.php`, covering defaults, preference validation, translation and preservation of form values, CSRF tokens, scripts and links. They were NOT executed here: PHP is unavailable.
- Live PHP/MySQL, real browser rendering, uploads and payment flows were NOT exercised for this update. Run `php tests/locale.php` and follow `setup.md` locally.
- The catalogue translates supplied UI/demo copy. Newly authored CMS content remains as entered until matching translations are added to `resources/lang/en.json` and `bn.json`. No external translation provider is used.
