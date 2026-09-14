> **এই ZIP-এর নতুন update:** পুরোনো database রাখলে আগে `UPDATE-V3-BN.md` অনুযায়ী `php bin/console upgrade` চালান। একদম নতুন setup হলে নিচের ধাপগুলো অনুসরণ করুন।

> Tailwind update: design edit করার নির্দেশনা `TAILWIND-GUIDE-BN.md`-এ আছে। Compiled CSS দেওয়া আছে। Existing installation-এর `.env`, database ও uploads রেখে code update করুন।

# দেশি ভোজ — নতুন করে setup করার বাংলা গাইড

এই গাইডটি `food-complete-tailwind-v3.zip`-এর PHP/MySQL প্রজেক্টের জন্য। Windows + XAMPP + PowerShell ধরে ধাপগুলো লেখা হয়েছে। Website চালাতে PHP built-in server এবং database-এর জন্য XAMPP MySQL ব্যবহার করবেন।

> পুরোনো website-এর data দরকার হলে আগে phpMyAdmin থেকে database Export করুন এবং পুরোনো `.env` ও `storage` folder আলাদা করে রাখুন। নতুন installation-এর জন্য আলাদা folder ও database ব্যবহার করুন; পুরোনো database মুছতে হবে না।

## ১. PHP এবং MySQL প্রস্তুত করুন

- PHP 8.2 বা তার পরের সংস্করণ প্রয়োজন। XAMPP-এর PHP version মিলিয়ে নিন।
- XAMPP Control Panel খুলে **MySQL → Start** করুন।
- phpMyAdmin ব্যবহার করতে **Apache → Start** করুন। Website পরে 8000 port-এ চলবে।
- VS Code-এ project folder খুলে Terminal → New Terminal নিন। নিচের কমান্ডগুলো PowerShell-এর জন্য। `PS D:\...>` অংশ নিজে লিখবেন না।

```powershell
php -v
php --ini
```

`php is not recognized` হলে এবং XAMPP `C:\xampp`-এ থাকলে বর্তমান terminal-এ চালান:

```powershell
$env:Path = "C:\xampp\php;" + $env:Path
php -v
php --ini
```

XAMPP অন্য জায়গায় থাকলে পথটি বদলাবেন। এই PATH পরিবর্তন শুধু বর্তমান terminal-এর জন্য। অন্য PHP দেখালে এই একই কমান্ড দিয়ে XAMPP-এর PHP আগে আনুন।

## ২. ZIP extract করে সঠিক folder-এ যান

`food-complete-tailwind-v3.zip` extract করুন। যে `koji-php` folder-এর ভেতরে `app`, `bin`, `public`, `storage` এবং `.env.example` আছে, সেখানে terminal খুলুন। আপনার আগের path অনুযায়ী উদাহরণ:

```powershell
cd "D:\Website\Update website\bangladeshi-food-website\koji-php"
Get-ChildItem
Test-Path .\bin\console
```

শেষ কমান্ডে `True` আসতে হবে। আপনার extract করার location আলাদা হলে `cd`-এর path বদলান। Folder-এর নাম `koji-php` থাকাই স্বাভাবিক; website-এর বাংলা নামের সঙ্গে এটি মিলতে হবে না।

## ৩. নতুন database তৈরি করুন

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

Save করুন। XAMPP MySQL অন্য port-এ চললে `DB_PORT`-ও বদলান। খালি password-এর জন্য `DB_PASSWORD=` রাখুন; `null` বা `empty` লিখবেন না। Root-এর এই configuration শুধু নিজের local development-এর জন্য; hosting-এ database-এর নির্দিষ্ট user/password ব্যবহার করবেন।

### আপনার আগের Access denied error-এর কারণ

```text
SQLSTATE[HY000] [1045] Access denied for user 'koji'@'localhost' (using password: YES)
```

এটি MySQL login ব্যর্থ হওয়ার error। `.env.example`-এর `koji` user ও sample password copy করলেই MySQL-এ account তৈরি হয় না। উপরের মতো নিজের MySQL-এর আসল username/password বসাতে হবে। phpMyAdmin-এর login password-ই MySQL password হতে পারে; website admin-এর password এখানে দেবেন না। `.env`-এর `MYSQL_ROOT_PASSWORD` বদলালেও আগে থেকে installed XAMPP-এর MySQL password বদলায় না।

## ৫. PHP extensions এবং image upload settings

```powershell
php --ini
php -m
```

`php --ini`-তে **Loaded Configuration File** যে `php.ini` দেখায়, সেটি খুলুন। সাধারণত XAMPP-এ `C:\xampp\php\php.ini`। প্রয়োজনীয় modules: `pdo_mysql`, `mbstring`, `dom`, `fileinfo`; payment-এর জন্য `curl`-ও রাখুন।

নিচের extension lines থাকলে এবং সামনে `;` থাকলে সেটি সরান। আগে থেকেই active থাকলে নতুন করে duplicate line যোগ করবেন না:

```ini
extension=pdo_mysql
extension=mbstring
extension=fileinfo
extension=curl
```

`dom` module `php -m`-এ থাকতে হবে; এটি না থাকলে আপনার PHP installation-এ DOM/XML support ঠিক করতে হবে। Image upload-এর জন্য GD বাধ্যতামূলক নয়। একই `php.ini`-তে settings দিন:

```ini
file_uploads=On
upload_max_filesize=5M
post_max_size=6M
```

Save করে চলমান PHP server বন্ধ করে আবার চালান। Apache দিয়ে PHP চালালে Apache restart করুন। Project-এর `config/php.ini` edit করলেই local PHP সেটি স্বয়ংক্রিয়ভাবে load করবে না।

## ৬. Database tables ও demo content install করুন

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

এই গাইডের command ব্যবহার করলে `localhost/koji-php/public` খুলবেন না। Project `htdocs`-এর মধ্যে থাকা বাধ্যতামূলক নয়।

## ৯. Image upload ঠিকভাবে পরীক্ষা করুন

১. Admin-এ login করুন।
২. **Products → Edit** খুলুন।
৩. **Upload images** দিয়ে প্রথমে একটি ছোট JPG/PNG/WebP বাছুন, যেমন 500 KB।
৪. Upload শেষ হওয়া ও preview আসা পর্যন্ত অপেক্ষা করুন।
৫. প্রয়োজন হলে **Make cover** দিন।
৬. Product form-এর **Save** চাপুন। শুধু upload করলেই product-এর সঙ্গে ছবিটি save হয়েছে ধরে নেবেন না।
৭. Public menu/product page refresh করে ছবিটি দেখুন।

প্রতি product-এ সর্বোচ্চ ১২টি ছবি; প্রতি ছবি সর্বোচ্চ 5 MB এবং 24 megapixels। SVG/HEIC-এর বদলে JPG, PNG বা WebP ব্যবহার করুন।

ছবি `storage/uploads`-এ থাকে এবং `/media/...` route দিয়ে দেখায়। `storage/uploads`, `storage/sessions`, `storage/logs`, `storage/backups` folder-এ PHP-এর write access থাকতে হবে। Doctor এগুলো পরীক্ষা করে এবং প্রয়োজন হলে তৈরি করার চেষ্টা করে।

Upload ব্যর্থ হলে প্রদর্শিত error লিখে রাখুন। Preview আসে কিন্তু public page-এ দেখা যায় না হলে Save, cover selection এবং ছবির `/media/...` URL পরীক্ষা করুন। CLI doctor এবং Apache-এর PHP আলাদা settings ব্যবহার করতে পারে; এই গাইডের built-in server ব্যবহার করলে একই `php` executable ব্যবহৃত হবে।

## ১০. পরের দিন আবার কীভাবে চালাবেন

Install ও admin create প্রতিদিন করতে হবে না। শুধু:

১. XAMPP-এ MySQL Start করুন।
২. `koji-php` folder-এ terminal খুলুন।
৩. PHP server চালান:

```powershell
php -d upload_max_filesize=5M -d post_max_size=6M -S localhost:8000 -t public public/router.php
```

৪. <http://localhost:8000> খুলুন।

## সাধারণ error ও সমাধান

| Error / সমস্যা | কী করবেন |
| --- | --- |
| `php is not recognized` | ধাপ ১ অনুযায়ী PHP PATH ঠিক করুন। |
| `Could not open input file: bin/console` | Terminal সঠিক `koji-php` folder-এ নেই; ধাপ ২ দেখুন। |
| `Access denied ... 1045` | `.env`-এর username/password MySQL account-এর সঙ্গে মিলিয়ে Save করুন; চলমান server restart করুন। |
| পরিবর্তনের পরও error-এ user `koji` দেখায় | সঠিক project-এর `.env` edit হয়েছে কি না ও filename `.env.txt` হয়েছে কি না দেখুন। PowerShell-এ আগে সেট করা `DB_*` environment variables থাকলে সেগুলো `.env`-এর চেয়ে অগ্রাধিকার পায়। |
| `Unknown database ... 1049` | ধাপ ৩-এ database তৈরি করে `.env`-এ একই নাম দিন। |
| `Connection refused` / `2002` | MySQL Start করুন; host/port মিলিয়ে নিন। |
| `could not find driver` | সক্রিয় PHP-তে `pdo_mysql` enable করে server restart করুন। |
| `Call to undefined function ... mb_...` | `mbstring` enable করুন। |
| `Class DOMDocument not found` | PHP DOM/XML support ঠিক করুন। |
| `Failed to listen on localhost:8000` | Port ব্যবহৃত হচ্ছে। অন্য server বন্ধ করুন, অথবা 8001 ব্যবহার করে `APP_URL=http://localhost:8001` দিন ও server command-এও 8001 দিন। |
| Upload size/session-related error | ধাপ ৫-এর limits ও ধাপ ৮-এর server command ব্যবহার করুন; আবার login করে ছোট ছবি দিয়ে দেখুন। |
| Admin email already exists / duplicate entry | একই account আবার create করবেন না; আগের account দিয়ে login বা নিচের reset command ব্যবহার করুন। |
| CSS/ছবি/route 404 | `-t public public/router.php`-সহ server চালিয়েছেন কি না দেখুন। |

Admin password ভুলে গেলে:

```powershell
php bin/console user:reset
```

নিজের বিদ্যমান admin email এবং নতুন কমপক্ষে ১২ অক্ষরের password দিন।

## Design edit, payment ও hosting

Compiled Tailwind CSS দেওয়া আছে, তাই প্রথমবার চালাতে npm বা CSS build লাগবে না। এই project নিজস্ব PHP autoloader ব্যবহার করে; setup-এর জন্য Composer install command নেই। Tailwind classes বা CSS পরিবর্তন করলে Node.js/npm দিয়ে চালান:

```powershell
npm ci
npm run build
```

কোন file edit করবেন: `WHERE-TO-EDIT-BN.md` দেখুন। Website চালু হলে admin থেকে brand, ফোন, ঠিকানা, delivery fee/area, opening hours এবং খাবারের দাম নিজের মতো সেট করুন।

শুরুতে `PAYMENTS_ENABLED=false` রাখুন এবং cash checkout ব্যবহার করুন। bKash/Nagad চালু করতে SSLCOMMERZ merchant credentials ও wallet channels প্রয়োজন; `docs/PAYMENTS.md` অনুসরণ করুন। এই setup guide তৈরি করার সময় বাস্তব payment পরীক্ষা করা হয়নি।

Live hosting-এ document root হবে `koji-php/public`; পুরো project root public করবেন না। Domain অনুযায়ী `APP_URL`, HTTPS-এ `SESSION_SECURE=true`, এবং hosting-এর database credentials দিন। PHP built-in server local development-এর জন্য।

## যাচাইয়ের সীমা

নির্দেশনার environment keys, command names, success messages ও upload limits বর্তমান project code-এর সঙ্গে মিলিয়ে লেখা হয়েছে। আপনার Windows/XAMPP-এ installation এবং বাস্তব image upload এই পরিবেশ থেকে চালিয়ে পরীক্ষা করা হয়নি।

## English / বাংলা language selector (এই আপডেট)

- প্রথমবার public website English-এ খুলবে। Navbar-এর dropdown থেকে বাংলা বেছে নিতে পারবেন। JavaScript বন্ধ থাকলে পাশের ↵ button চাপুন।
- একই browser-এ ভাষার পছন্দ ১ বছর মনে থাকে; English-এ ফিরতে dropdown ব্যবহার করুন। Cookie মুছে দিলে default English হবে।
- নতুন করে database install বা migration প্রয়োজন নেই। পুরোনো `.env` এবং `storage` রেখে নতুন code copy করে PHP server restart করুন। Fresh setup-এর জন্য উপরের ধাপ অনুসরণ করুন।
- `resources/lang/en.json`-এ বাংলা মূল লেখা → English অনুবাদ; `resources/lang/bn.json`-এ English মূল লেখা → বাংলা অনুবাদ আছে।
- Admin থেকে নতুন নিজস্ব content লিখলে তা যেভাবে লিখেছেন সেভাবেই থাকবে। দুই ভাষায় দেখাতে সংশ্লিষ্ট JSON file-এ সেই লেখার অনুবাদ যোগ করুন। এটি স্বয়ংক্রিয় অনলাইন অনুবাদ নয়।
- অনুবাদ শুধু public page-এ দেখানো হয়; database-এর content, admin input, দাম, cart, order ও payment data বদলায় না।
- Optional language regression check: `php tests/locale.php` (PHP 8.2+, DOM ও mbstring প্রয়োজন; MySQL প্রয়োজন নেই)।
