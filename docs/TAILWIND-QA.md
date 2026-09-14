# Tailwind conversion verification

Verified in this update:

- Production Tailwind build completes using the existing pinned npm lockfile.
- All 57 PHP files parse using PHP 8.5 WebAssembly's `token_get_all(..., TOKEN_PARSE)`.
- Existing rich-text formatting/sanitization checks pass.
- Existing upload response-handling checks pass (4 checks).
- Existing language/mobile-navigation JavaScript checks pass (6 checks).
- PHP fixture rendering exercises storefront and admin templates without touching MySQL.
- Chromium fixture checks passed on home, menu, product, about, contact, media admin and settings at 1440, 768, 390 and 320px; no document horizontal overflow.
- Browser assertions passed for selected category state, query/sort/language preservation in category links, mobile menu open/Escape, product thumbnail/lightbox, rich-text colors, editor initialization and swatch dimensions. Bengali font was supplied to the QA browser because that runtime lacks Bengali system fonts.

The original `tests/locale.php` reports `FAIL: Script unchanged` in this WebAssembly runtime. The identical failure also occurs on the unmodified uploaded project's files; the locale service and that test were not changed by this conversion.

Database-backed order/payment/reservation writes and real authenticated image uploads were not rerun because no MySQL service or application database was provided. Their controllers/services/routes remain unchanged. Use the existing `tests/README.md` instructions against a disposable MySQL installation for integration checks.

The website's custom design now lives in Tailwind classes in PHP templates and JS-generated components. `resources/css/app.css` contains only build directives. `quill.snow.css` remains the third-party editor theme; Quill-specific overrides and color swatches use Tailwind classes in `admin.js`.
