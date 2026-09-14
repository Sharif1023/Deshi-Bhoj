# আপডেটের ব্যবহার ও পুরোনো প্রজেক্ট upgrade

## পুরোনো website থাকলে

1. phpMyAdmin থেকে database Export করুন। `.env` ও পুরো `storage` folder-এর backup রাখুন।
2. ZIP খুলে `food` folder-এর code পুরোনো project folder-এ বসান। আপনার `.env`, `storage/uploads`, session ও backup file মুছবেন না।
3. XAMPP থেকে MySQL চালু করুন। VS Code terminal-এ project root-এ যান—যেখানে `bin`, `app`, `public` folder আছে।
4. পুরোনো `.env`-এর database তথ্য রেখেই চালান:

```powershell
php bin/console upgrade
php bin/console doctor
php -S localhost:8000 -t public public/router.php
```

`upgrade` নতুন translation table, order history table এবং receipt-এর language snapshot column যোগ করে। পুরোনো order-এর বর্তমান status history-তে যোগ হয়। আগের status পরিবর্তনের সময় জানা না থাকায় সেগুলো বানিয়ে দেখানো হয় না। Existing content, products, users, orders এবং images অক্ষত থাকে। কমান্ডটি পুনরায় চালানো যায়।

পুরোনো database-এ `install` চালিয়ে upgrade করবেন না। নতুন installation-এর জন্য `setup.md` অনুসরণ করুন; নতুন installation-এ `install` নিজেই এই update চালায়।

## Tailwind দিয়ে design পরিবর্তন

- Page-এর style সেই PHP view-এর `class` attribute-এ আছে।
- Public: `resources/views/store/`; admin: `resources/views/admin/`।
- Navbar/footer/shared layout: `resources/views/layout.php`।
- Rich-text editor এবং image picker-এর তৈরি component: `public/assets/admin.js`।
- Checkout ও order tracking interaction: `public/assets/app.js`।
- `resources/css/app.css`-এ শুধু Tailwind directives আছে। Editor-এর vendor stylesheet `quill.snow.css` রাখা হয়েছে।
- Generated `public/assets/app.css` সরাসরি edit করবেন না।

প্রথমবার design পরিবর্তনের আগে:

```powershell
npm ci
npm run build
```

কাজ করার সময়:

```powershell
npm run watch
```

ZIP-এ compiled CSS আছে। Website চালানো বা admin থেকে লেখা/ছবি বদলাতে Node.js বা CSS rebuild প্রয়োজন নেই। শুধু Tailwind class বদলালে rebuild করুন।

## Home ও menu category

Home-এর category bar শুধু homepage-এর featured খাবারগুলো filter করে; আলাদা menu page-এ যায় না। সর্বোচ্চ ৬টি featured item দেখানো হয়। Featured না থাকলে প্রথম ৬টি item দেখায়। কোনো বিভাগের featured item না থাকলে empty result দেখায়। সব খাবার দেখতে **View all dishes / সব খাবার দেখুন** ব্যবহার করুন। Home category link reload করলেও একই home menu section-এ রাখে; JavaScript বন্ধ থাকলেও filter কাজ করে।

Menu page-এর category navigation সব menu item filter করে, search এবং sorting ধরে রাখে। Admin-এর Menu products এবং Categories-এর উপরের category bar দিয়েও filter করা যায়।

## বাংলা ও ইংরেজিতে লেখা

1. Admin → Menu products → Edit item খুলুন। আলাদা পূর্ণ পেজ খুলবে।
2. **Content language → English** বেছে ইংরেজি title/description লিখুন।
3. **বাংলা** বেছে বাংলা লেখা দিন। Tab বদলালে unsaved লেখা হারাবে না।
4. **Save changes** চাপলে দুই ভাষার লেখাই একসঙ্গে save হবে।
5. Public navbar-এ English/বাংলা বেছে ফল দেখুন। Default English; নির্বাচিত ভাষা browser-এ মনে থাকে।

Category, gallery, reviews, FAQs, promises এবং Website settings-এর content-ও একইভাবে লেখা যায়। ছবি, মূল্য, slug, availability এবং category দুই ভাষার জন্য একই থাকে। এই selector content লেখার ভাষা বাছাই করে; admin-এর management labels ইংরেজিতে থাকে।

পুরোনো data-এর লেখাগুলো হারায় না। কোনো ভাষায় লেখা না দিলে পুরোনো/base content fallback হিসেবে দেখায়; arbitrary নতুন লেখা স্বয়ংক্রিয়ভাবে অনুবাদ করা হয় না। দুই ভাষায় নির্ভুল লেখা চাইলে দুই tab-এই লিখুন।

Website settings-এ আগে group/page বাছুন, তারপর সেই পেজের content edit করুন। Team add/edit এবং order/reservation/payment/message details-ও পূর্ণ পেজে খুলবে। Image নির্বাচন ও zoom-এর মতো ছোট utility dialog একইভাবে থাকে।

## অর্ডার ও tracking

- Minimum order amount তুলে দেওয়া হয়েছে। একটি কমদামি খাবারও order করা যায়।
- Pickup বা delivery বোতাম বাছুন। Delivery-তে ঠিকানা বাধ্যতামূলক, delivery fee total-এ যোগ হয়; pickup-এ fee শূন্য।
- Receipt-এ editor-এর `<span ...>` code দেখা যাবে না। নতুন receipt-এ বাংলা/ইংরেজি নাম snapshot হিসেবে থাকে; product পরে বদলালেও receipt-এর নাম/দাম বদলায় না।
- Checkout-এর পর tracking receipt খুলবে। Navbar-এর **Track order** থেকেও order number ও একই মোবাইল নম্বর দিয়ে পাওয়া যায়।
- Receipt খোলা থাকলে ১৫ সেকেন্ড অন্তর status refresh হয়। Refresh link-ও আছে।
- Admin → Orders → Open order থেকে status পরিবর্তন করুন: Pending → Confirmed → Preparing → Ready → Out for delivery → Delivered। Pickup-এ delivery ধাপ নেই, completed label হলো Collected। পুরোনো workflow-এর জন্য Preparing থেকে Delivered-ও করা যায়।
- Cash order delivered/collected হলে cash collected record করুন। Online payment enabled থাকলে verified payment ছাড়া fulfillment এগোয় না।
- Online payment provider configuration আগের মতোই প্রয়োজন; এই update payment gateway credentials সরবরাহ করে না।

## যাচাই

`VERIFICATION.md`-এ এই delivery-তে করা checks এবং live MySQL যাচাইয়ের সীমাবদ্ধতা দেওয়া আছে। Live যাওয়ার আগে নিজের MySQL setup-এ একটি test order দিয়ে admin status change ও receipt refresh পরীক্ষা করুন।
