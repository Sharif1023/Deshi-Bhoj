> Current complete setup: `setup.md`. Existing installation upgrade: `UPDATE-V3-BN.md`.

নতুন database তৈরি করুন

Browser-এ খুলুন: <http://localhost/phpmyadmin>

১. **New** অথবা **Databases**-এ যান।
২. Database name দিন: `deshi_bhoj`
৩. Collation নির্বাচন করুন: `utf8mb4_unicode_ci`
৪. **Create** চাপুন।

এই নামের database আগে থেকেই থাকলে নতুন নাম নিন এবং পরের ধাপে `DB_DATABASE`-এ সেই নাম দিন।

বিকল্পভাবে phpMyAdmin-এর SQL tab-এ চালাতে পারেন:

```sql
CREATE DATABASE deshi_bhoj CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

এখন কোনো SQL file import করতে হবে না। Project-এর `install` command টেবিল ও demo data তৈরি করবে।

## ৪. `.env` তৈরি করে database connection ঠিক করুন

শুধু নতুন installation-এ চালান:

```powershell
Copy-Item .env.example .env
notepad .env
```

আগে থেকে নিজের `.env` থাকলে copy করে overwrite করবেন না; সেটিই edit করুন। নাম ঠিক `.env` হতে হবে, `.env.txt` নয়।

নিচের configuration একটি local XAMPP-এর জন্য যেখানে MySQL `root` account-এর password খালি। আপনার root password থাকলে `DB_PASSWORD=`-এর পরে আসল password দিন।

```dotenv
APP_ENV=local
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Dhaka
SESSION_SECURE=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=deshi_bhoj
DB_USERNAME=root
DB_PASSWORD=

PAYMENTS_ENABLED=false
SSLCOMMERZ_SANDBOX=true
SSLCOMMERZ_STORE_ID=
SSLCOMMERZ_STORE_PASSWORD=
SSLCOMMERZ_BKASH_CHANNEL=bkash
SSLCOMMERZ_NAGAD_CHANNEL=nagad
MAIL_FROM=hello@example.com
```

প্রতিটি তথ্য লিখে Enter দিন। Password কমপক্ষে ১২ অক্ষরের দিন। এই terminal prompt-এ password দেখা যায়, তাই screenshot/share করবেন না। সফল হলে `Owner created. Sign in at /control-center` দেখাবে। এখানে দেওয়া email/password দিয়ে admin login করবেন। কোনো default admin account ধরে নেবেন না।
Database tables ও demo content install করুন

`koji-php` folder-এর terminal-এ:

```powershell
php bin/console install
```

সফল হলে এই message আসবে:

```text
Database installed. Create an owner: php bin/console user:create
```

Error এলে আগে সেটি ঠিক করুন, তারপর পরের ধাপে যান। `install` database নিজে তৈরি করে না; ধাপ ৩-এর database আগে থাকতে হবে।

## ৭. নিজের admin account তৈরি করুন

```powershell
php bin/console user:create
```

একটির পর একটি তথ্য চাইবে:

```text
Name: আপনার নাম
Email: আপনার email
Password (12+ characters; visible input): নিজের password
```

প্রতিটি তথ্য লিখে Enter দিন। Password কমপক্ষে ১২ অক্ষরের দিন। এই terminal prompt-এ password দেখা যায়, তাই screenshot/share করবেন না। সফল হলে `Owner created. Sign in at /control-center` দেখাবে। এখানে দেওয়া email/password দিয়ে admin login করবেন। কোনো default admin account ধরে নেবেন না।

## ৮. Setup check করে website চালু করুন

```powershell
php bin/console doctor
```

PHP version, modules, storage folders ও MySQL media table-এর ফলাফল দেখুন। `[CHECK]` থাকলে সংশ্লিষ্ট সমস্যা ঠিক করুন। Doctor-এর upload limit lines-ও দেখুন। এরপর চালান:

```powershell
php -d upload_max_filesize=5M -d post_max_size=6M -S localhost:8000 -t public public/router.php
```

এই terminal চালু রাখবেন। Website চলার সময় terminal command prompt ফিরে না আসা স্বাভাবিক। বন্ধ করতে `Ctrl+C` চাপুন।


- Website: <http://localhost:8000>
- Admin: <http://localhost:8000/control-center>


