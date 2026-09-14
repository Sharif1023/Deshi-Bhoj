> New full-page editors, language tabs, home filtering and tracking: see `UPDATE-V3-BN.md`.

# কোন পরিবর্তন কোথায় করবেন

| পরিবর্তন | Admin / ফাইল |
|---|---|
| Brand, navbar label, logo | Admin → Website settings → Brand & navigation |
| Navbar/footer layout | `resources/views/layout.php` |
| Homepage লেখা ও ছবি | Admin → Website settings → Homepage |
| Homepage layout/search/categories | `resources/views/store/home.php` |
| দেশি design-এর CSS | সংশ্লিষ্ট `resources/views/store/*.php` file-এর Tailwind class; তারপর `npm run build` |
| Product, price, ingredients, images | Admin → Menu products |
| Product cards | `resources/views/store/cards.php` |
| Product detail/gallery | `resources/views/store/product.php` |
| Categories | Admin → Categories |
| Image upload | `app/Services/UploadService.php`, `MediaService.php`, `app/Controllers/MediaController.php` |
| Image route | `routes/web.php`-এর `/media/` অংশ |
| Admin upload/preview/editor | `public/assets/admin.js`, `resources/views/admin/media-field.php` |
| Upload limit/CSRF/errors | `public/index.php`; server php.ini; `public/.user.ini` |
| About / gallery / FAQ | Admin settings ও সংশ্লিষ্ট বিভাগ; `resources/views/store/` |
| Cart ও checkout | `resources/views/store/cart.php`, `checkout.php`; `app/Services/OrderService.php` |
| Phone/address/delivery info | Admin → Website settings |
| bKash/Nagad | `.env`, `app/Services/PaymentService.php`, `docs/PAYMENTS.md` |
| বাংলা demo content | `database/source-content.json` |
| Existing database localization | `database/upgrade-bd.php` (একবার চলে) |
| Database connection | `.env`, `app/Core/DB.php` |
| Diagnostics | `php bin/console doctor` |

Live content বদলাতে Admin ব্যবহার করুন। Seed JSON বদলালে আগে install করা database নিজে থেকে বদলাবে না। নতুন Tailwind class যোগ করলে CSS rebuild করুন; compiled CSS delivery-তে দেওয়া আছে।

## Language selection

- Navbar selector: `resources/views/store/language-selector.php`
- Selector-এর style: `resources/views/store/language-selector.php`-এর Tailwind class
- English অনুবাদ: `resources/lang/en.json`
- বাংলা অনুবাদ: `resources/lang/bn.json`
- Default English, cookie ও public rendering: `app/Services/Locale.php`
- নতুন admin content-এর দুই ভাষা চাইলে JSON dictionary-তে সম্পূর্ণ লেখার translation যোগ করুন। JSON-এর key/value plain text; rich text-এর প্রতিটি text অংশের জন্য entry দিন।
- পূর্ণ নতুন setup guide: `setup.md`
