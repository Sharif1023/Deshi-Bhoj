# Complete Tailwind + bilingual content + order tracking update

Start with `setup.md` for a new installation. For an existing installation, follow `UPDATE-V3-BN.md` and run `php bin/console upgrade`. See `VERIFICATION.md` for validation scope.

# Tailwind conversion update

Page-local Tailwind utility styling, navbar-style menu categories, bundled production CSS. Read `TAILWIND-GUIDE-BN.md` for editing and `setup.md` for PHP/MySQL setup. Quill retains its vendor stylesheet.

# দেশি ভোজ — Bangladesh restaurant edition

PHP 8.2+ MVC, MySQL, bundled Tailwind CSS, Bengali storefront, menu/cart/checkout, reservations, role-based admin, rich text, multiple product photos and SSLCOMMERZ payment integration.

Start with **SETUP-BN.md**. Existing installations must keep `.env` and `storage`, then run `php bin/console install` for the one-time content update. Run `php bin/console doctor` for diagnostics. **WHERE-TO-EDIT-BN.md** maps editable features to files.

This update removes the GD requirement from uploads, preserves validated JPG/PNG/WebP originals and reports PHP limits/storage errors clearly. Demo image assets are AI-generated Bangladeshi food photography. PHP/MySQL runtime validation was unavailable in this editing environment; see **docs/QA.md** for the checks performed and remaining checks.
