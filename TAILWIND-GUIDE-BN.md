# Tailwind CSS দিয়ে পেজ edit করার নিয়ম

এই সংস্করণে website-এর নিজস্ব design Tailwind utility class-এ আছে। আলাদা `.btn`, `.card` বা Bangladesh edition CSS rule edit করতে হবে না। Layout, responsive size, hover, selected state এবং animation-এর class সংশ্লিষ্ট PHP view বা JavaScript component-এ পাবেন।

| কী বদলাবেন | কোথায় বদলাবেন |
|---|---|
| Homepage-এর sections, spacing, ছবি, responsive design | `resources/views/store/home.php` |
| Menu-এর category navigation, search ও sort | `resources/views/store/menu.php` |
| একাধিক পেজে ব্যবহৃত food card | `resources/views/store/cards.php` |
| Navbar, footer, shared accessibility ও text colors | `resources/views/layout.php` |
| Navbar-এর English/বাংলা selector | `resources/views/store/language-selector.php` |
| Product details ও thumbnail styling | `resources/views/store/product.php` |
| About, gallery, contact, cart, checkout | `resources/views/store/`-এর সংশ্লিষ্ট PHP file |
| Admin page design | `resources/views/admin/`-এর সংশ্লিষ্ট PHP file |
| Login ও password forms | `resources/views/auth/` |
| Dynamic image picker, editor, media dialog | `public/assets/admin.js` |
| Mobile menu, reveal animation, lightbox interactions | `public/assets/app.js` |
| Shared colors, fonts ও animation keyframes | `tailwind.config.js` |

## প্রথমবার চালানো

Compiled `public/assets/app.css` ZIP-এ দেওয়া আছে। তাই website চালানোর জন্য Node.js প্রয়োজন নেই। PHP ও MySQL setup-এর জন্য `setup.md` অনুসরণ করুন।

আগের project আপডেট করলে নিজের `.env`, database এবং `storage/uploads` রেখে নতুন code বসান। নতুন করে install বা database seed করার দরকার নেই; নতুন translation ও tracking schema যোগ করতে `php bin/console upgrade` চালাতে হবে। `UPDATE-V3-BN.md` দেখুন। নতুন installation হলে `.env.example` থেকে `.env` তৈরি করুন।

## Design edit করার সময়

Project root-এ terminal খুলুন:

```bash
npm ci
npm run watch
```

Watch চলতে থাকা অবস্থায় PHP file-এর `class` পরিবর্তন করে save করুন। Tailwind প্রয়োজনীয় CSS তৈরি করবে। Browser refresh করুন। কাজ শেষে terminal-এ Ctrl+C চাপুন।

Deploy/ZIP তৈরি করার আগে:

```bash
npm run build
```

উদাহরণ:

```html
<section class="mx-auto max-w-7xl px-4 py-10 md:py-16">
    <h1 class="text-4xl font-bold text-[#173d2e] md:text-6xl">খাবারের মেনু</h1>
</section>
```

- `md:` / `lg:` দিয়ে বড় screen-এর design বদলান। `max-[851px]:` ছোট screen-এর সীমা নির্দেশ করে।
- `[&_h2]:text-white` মানে ওই section-এর ভেতরের h2 সাদা হবে। এটি Tailwind arbitrary variant; style একই view-তে আছে।
- `[padding-block:54px]`-ও Tailwind arbitrary utility। প্রয়োজন হলে সাধারণ `py-14` class ব্যবহার করতে পারেন।
- পূর্ণ class name লিখুন। PHP/JS দিয়ে `bg-` ও রং জোড়া দিয়ে class তৈরি করবেন না; দুই অবস্থার পূর্ণ class string লিখুন।
- `public/assets/app.css` generated file; সরাসরি edit করলে পরের build-এ পরিবর্তন মুছে যাবে।
- `resources/css/app.css` শুধু Tailwind-এর তিনটি build directive রাখে।
- পুরোনো কিছু class name এখন selector/interaction hook হিসেবে রাখা আছে; সেগুলোর আলাদা custom CSS definition নেই।

## Category navigation

Menu-এর category dropdown সরিয়ে navbar-এর মতো গাঢ় সবুজ navigation রাখা হয়েছে। নির্বাচিত category cream রঙে highlight হয়। ছোট screen-এ categories অনুভূমিকভাবে scroll করা যায়; বড় screen-এ wrap করে। বিভাগ বদলালে search, sort ও language URL-এ সংরক্ষিত থাকে। Search submit করলেও নির্বাচিত category বজায় থাকে। JavaScript ছাড়াও category links ও search কাজ করে।

## Editor-এর stylesheet

Admin-এর Quill editor একটি third-party component। তার নিজস্ব `quill.snow.css` রাখা হয়েছে, যাতে toolbar ও editor-এর built-in আচরণ ঠিক থাকে। Project-এর editor customization ও color swatch এখন `admin.js`-এর Tailwind class-এ আছে।

Tailwind documentation: https://v3.tailwindcss.com/docs/content-configuration
