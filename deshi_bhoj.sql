-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 14, 2026 at 11:19 PM
-- Server version: 10.11.19-MariaDB-cll-lve-log
-- PHP Version: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sharuuco_yummy_sharuu_com`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` text NOT NULL,
  `created_at` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `user_id`, `action`, `created_at`) VALUES
(1, NULL, 'Bangladesh demo menu installed. Review sample prices and contact details before launch.', '2026-09-14 22:38:44'),
(2, NULL, 'Bangladesh content update applied; orders, accounts, uploaded media and custom products preserved.', '2026-09-14 22:38:44'),
(3, 1, 'Staff signed in', '2026-09-14 22:46:21'),
(4, 1, 'Staff signed in', '2026-09-14 22:51:12'),
(5, 1, 'Products saved', '2026-09-14 22:55:25'),
(6, 1, 'Website settings updated', '2026-09-14 22:56:02'),
(7, 1, 'Website settings updated', '2026-09-14 22:56:16'),
(8, 1, 'Website settings updated', '2026-09-14 22:56:30'),
(9, 1, 'Website settings updated', '2026-09-14 22:56:50'),
(10, 1, 'Website settings updated', '2026-09-14 22:57:53');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `slug` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `image` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `sort_order`) VALUES
(1, 'বিরিয়ানি', 'biryani', 'সুগন্ধি চাল ও মাংসের আয়োজন', '/assets/kacchi.png', 0),
(2, 'মাংস', 'meat', 'দেশি মসলায় রান্না', '/assets/kala-bhuna.png', 1),
(3, 'মাছ', 'fish', 'বাংলার মাছের পদ', '/assets/ilish.png', 2);

-- --------------------------------------------------------

--
-- Table structure for table `content_translations`
--

CREATE TABLE `content_translations` (
  `entity` varchar(32) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `locale` varchar(2) NOT NULL,
  `field` varchar(100) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `content_translations`
--

INSERT INTO `content_translations` (`entity`, `entity_id`, `locale`, `field`, `value`) VALUES
('products', 7, 'bn', 'allergens', '<p>মাছ ও সরিষা</p>'),
('products', 7, 'bn', 'badge', '২ পিস মাছ'),
('products', 7, 'bn', 'description', '<p>সরিষার ঝোল ও কাঁচা মরিচে রান্না ইলিশ। ভাত আলাদা। ২ পিস মাছ।</p>'),
('products', 7, 'bn', 'ingredients', '<p>ইলিশ, সরিষা, সরিষার তেল, কাঁচা মরিচ</p>'),
('products', 7, 'bn', 'name', 'সর্ষে ইলিশ — দুজনের'),
('products', 7, 'en', 'allergens', '<p>Fish and mustard</p>'),
('products', 7, 'en', 'badge', '2 pieces of fish'),
('products', 7, 'en', 'description', '<p>Hilsa cooked in mustard gravy with green chilli. Rice sold separately. 2 pieces of fish.</p>'),
('products', 7, 'en', 'ingredients', '<p>Hilsa, mustard, mustard oil, green chilli</p>'),
('products', 7, 'en', 'name', 'Mustard hilsa for two'),
('settings', 0, 'bn', 'announcement', 'আপনার পছন্দের দেশি খাবার • ডেলিভারি ও পিকআপ'),
('settings', 0, 'bn', 'brand.name', 'দেশি ভোজ'),
('settings', 0, 'bn', 'brand.tagline', 'বাংলার স্বাদ, আপন আয়োজনে'),
('settings', 0, 'bn', 'navigation.about', 'আমাদের গল্প'),
('settings', 0, 'bn', 'navigation.contact', 'যোগাযোগ'),
('settings', 0, 'bn', 'navigation.gallery', 'গ্যালারি'),
('settings', 0, 'bn', 'navigation.home', 'হোম'),
('settings', 0, 'bn', 'navigation.menu', 'খাবারের মেনু'),
('settings', 0, 'en', 'announcement', 'Your favourite Bangladeshi food • Delivery &amp;amp;amp;amp;amp; pickup'),
('settings', 0, 'en', 'brand.name', 'Deshi Bhoj'),
('settings', 0, 'en', 'brand.tagline', 'The taste of <strong class=\"ql-color-red\"><em><u>Bengal</u></em></strong>, made for you'),
('settings', 0, 'en', 'navigation.about', 'Our story'),
('settings', 0, 'en', 'navigation.contact', 'Contact'),
('settings', 0, 'en', 'navigation.gallery', 'Gallery'),
('settings', 0, 'en', 'navigation.home', 'Home'),
('settings', 0, 'en', 'navigation.menu', 'Food menu');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `title`, `answer`, `sort_order`) VALUES
(1, 'পিকআপ অর্ডার করা যাবে?', 'হ্যাঁ। Checkout-এ পিকআপ নির্বাচন করুন এবং অর্ডার নম্বর রাখুন। সংগ্রহের সময় আমাদের দল নিশ্চিত করবে।', 0),
(2, 'টেবিল বুকিং কীভাবে করব?', 'যোগাযোগ পাতায় তারিখ, সময় ও অতিথির সংখ্যা দিয়ে অনুরোধ পাঠান। আমাদের নিশ্চিতকরণের পর বুকিং চূড়ান্ত হবে।', 0),
(3, 'অ্যালার্জি থাকলে কী করব?', 'অর্ডারের আগে যোগাযোগ করুন। রান্নাঘরে সাধারণ অ্যালার্জেন ব্যবহৃত হয়; অ্যালার্জেনমুক্ত খাবারের নিশ্চয়তা দেওয়া হয় না।', 0),
(4, 'কীভাবে পেমেন্ট করব?', 'নগদে পেমেন্ট করা যাবে। অনলাইন পেমেন্ট চালু থাকলে payment partner-এর মাধ্যমে bKash/Nagad পাওয়া যাবে।', 0),
(5, 'অর্ডার পরিবর্তন করা যাবে?', 'অর্ডার নম্বরসহ দ্রুত যোগাযোগ করুন। রান্না শুরু হয়েছে কি না তার ওপর পরিবর্তনের সুযোগ নির্ভর করে।', 0);

-- --------------------------------------------------------

--
-- Table structure for table `features`
--

CREATE TABLE `features` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `features`
--

INSERT INTO `features` (`id`, `title`, `description`, `sort_order`) VALUES
(1, 'চেনা দেশি পদ', 'কাচ্চি, কালা ভুনা ও ইলিশের আয়োজন।', 0),
(2, 'সহজ অর্ডার', 'খাবার বেছে cart-এ যোগ করুন, তারপর checkout।', 0),
(3, 'আপনার সুবিধামতো', 'ডেলিভারি অথবা রেস্টুরেন্ট থেকে পিকআপ।', 0);

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `image` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `title`, `image`, `sort_order`) VALUES
(1, 'কাচ্চি বিরিয়ানি', '/assets/kacchi.png', 0),
(2, 'কালা ভুনা', '/assets/kala-bhuna.png', 0),
(3, 'সর্ষে ইলিশ', '/assets/ilish.png', 0);

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `original_name` varchar(190) NOT NULL,
  `alt` text NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `created_at` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `url`, `original_name`, `alt`, `width`, `height`, `created_at`) VALUES
(1, '/assets/kacchi.png', 'কাচ্চি বিরিয়ানি (AI demo)', 'কাচ্চি বিরিয়ানি', 1536, 1024, '2026-09-14 22:38:44'),
(2, '/assets/kala-bhuna.png', 'কালা ভুনা (AI demo)', 'কালা ভুনা', 1536, 1024, '2026-09-14 22:38:44'),
(3, '/assets/ilish.png', 'সর্ষে ইলিশ (AI demo)', 'সর্ষে ইলিশ', 1536, 1024, '2026-09-14 22:38:44'),
(4, '/media/8348d1ade690c5faaa0dcbd1894122867dc47809.jpg', 'images.jpeg', '', 365, 547, '2026-09-14 22:54:46');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `email` varchar(190) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Unread',
  `created_at` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `number` varchar(40) NOT NULL,
  `access_token` varchar(64) NOT NULL,
  `idempotency_key` varchar(64) NOT NULL,
  `customer_name` text NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `order_type` varchar(20) NOT NULL,
  `notes` text NOT NULL,
  `subtotal` int(11) NOT NULL,
  `delivery_fee` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `payment_method` varchar(20) NOT NULL,
  `payment_status` varchar(30) NOT NULL DEFAULT 'unpaid',
  `created_at` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_events`
--

CREATE TABLE `order_events` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(30) NOT NULL,
  `created_at` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `name` text NOT NULL,
  `price` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `name_translations` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(190) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `transaction_id` varchar(80) NOT NULL,
  `provider_reference` varchar(190) DEFAULT NULL,
  `session_key` varchar(190) DEFAULT NULL,
  `gateway_url` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `amount` int(11) NOT NULL,
  `currency` varchar(5) NOT NULL DEFAULT 'BDT',
  `created_at` varchar(30) NOT NULL,
  `updated_at` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `slug` varchar(190) NOT NULL,
  `description` text NOT NULL,
  `price` int(11) NOT NULL,
  `images` text NOT NULL,
  `ingredients` text NOT NULL,
  `preparation_minutes` int(11) NOT NULL DEFAULT 15,
  `available` int(11) NOT NULL DEFAULT 1,
  `featured` int(11) NOT NULL DEFAULT 0,
  `badge` varchar(100) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `allergens` text DEFAULT NULL,
  `dietary` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `images`, `ingredients`, `preparation_minutes`, `available`, `featured`, `badge`, `sort_order`, `allergens`, `dietary`) VALUES
(1, 1, 'কাচ্চি বিরিয়ানি', 'kacchi-biryani', 'সুগন্ধি চাল, খাসির মাংস, আলু ও মসলায় দমে রান্না। ১ জনের জন্য।', 45000, '[\"\\/assets\\/kacchi.png\"]', 'চাল, খাসির মাংস, আলু, দই, ঘি, মসলা', 30, 1, 1, '১ জনের জন্য', 0, 'দুধ ও দুগ্ধজাত উপাদান', ''),
(2, 2, 'কালা ভুনা', 'kala-bhuna', 'পেঁয়াজ ও মসলায় ধীরে রান্না করা গরুর মাংস। ভাত আলাদা। ১ জনের জন্য।', 38000, '[\"\\/assets\\/kala-bhuna.png\"]', 'গরুর মাংস, পেঁয়াজ, তেল, মসলা', 30, 1, 1, '১ জনের জন্য', 1, 'একই রান্নাঘরে সাধারণ অ্যালার্জেন ব্যবহার হয়', ''),
(3, 3, 'সর্ষে ইলিশ', 'shorshe-ilish', 'সরিষার ঝোল ও কাঁচা মরিচে রান্না ইলিশ। ভাত আলাদা। ১ পিস মাছ।', 55000, '[\"\\/assets\\/ilish.png\"]', 'ইলিশ, সরিষা, সরিষার তেল, কাঁচা মরিচ', 30, 1, 1, '১ পিস মাছ', 2, 'মাছ ও সরিষা', ''),
(4, 1, 'কাচ্চি — দুজনের আয়োজন', 'kacchi-for-two', 'সুগন্ধি চাল, খাসির মাংস, আলু ও মসলায় দমে রান্না। ২ জনের জন্য।', 85000, '[\"\\/assets\\/kacchi.png\"]', 'চাল, খাসির মাংস, আলু, দই, ঘি, মসলা', 30, 1, 0, '২ জনের জন্য', 3, 'দুধ ও দুগ্ধজাত উপাদান', ''),
(5, 1, 'ফ্যামিলি কাচ্চি', 'family-kacchi', 'সুগন্ধি চাল, খাসির মাংস, আলু ও মসলায় দমে রান্না। ৪ জনের জন্য।', 165000, '[\"\\/assets\\/kacchi.png\"]', 'চাল, খাসির মাংস, আলু, দই, ঘি, মসলা', 30, 1, 0, '৪ জনের জন্য', 4, 'দুধ ও দুগ্ধজাত উপাদান', ''),
(6, 2, 'কালা ভুনা — শেয়ারিং', 'sharing-kala-bhuna', 'পেঁয়াজ ও মসলায় ধীরে রান্না করা গরুর মাংস। ভাত আলাদা। ২ জনের জন্য।', 72000, '[\"\\/assets\\/kala-bhuna.png\"]', 'গরুর মাংস, পেঁয়াজ, তেল, মসলা', 30, 1, 0, '২ জনের জন্য', 5, 'একই রান্নাঘরে সাধারণ অ্যালার্জেন ব্যবহার হয়', ''),
(7, 3, 'Mustard hilsa for two', 'ilish-for-two', '<p>Hilsa cooked in mustard gravy with green chilli. Rice sold separately. 2 pieces of fish.</p>', 105000, '[\"\\/media\\/8348d1ade690c5faaa0dcbd1894122867dc47809.jpg\",\"\\/assets\\/ilish.png\"]', '<p>Hilsa, mustard, mustard oil, green chilli</p>', 30, 1, 0, '2 pieces of fish', 6, '<p>Fish and mustard</p>', ''),
(8, 3, 'ফ্যামিলি ইলিশ', 'family-ilish', 'সরিষার ঝোল ও কাঁচা মরিচে রান্না ইলিশ। ভাত আলাদা। ৪ পিস মাছ।', 205000, '[\"\\/assets\\/ilish.png\"]', 'ইলিশ, সরিষা, সরিষার তেল, কাঁচা মরিচ', 30, 1, 0, '৪ পিস মাছ', 7, 'মাছ ও সরিষা', '');

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `bucket` varchar(190) NOT NULL,
  `hits` int(11) NOT NULL,
  `expires_at` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`bucket`, `hits`, `expires_at`) VALUES
('4de976b881ca7b2bf28457f77d72bc86ab4794580bd52b0c7fd9f82a7d995f67', 4, 1789404991),
('ab19f61dfbe00c428accf6cf793253871a0520744ffe4cc4cb65c0ec477878fa', 5, 1789404991),
('c4a6c538d78e1a2d9ac32d165db25ba3c14082ac6e173e2085d0e9b68ada679f', 1, 1789408486),
('dbfd9d2b9630c9d203ab56dd1f45b046c6da4422dfc54b76e756d5d6f33c8c8c', 1, 1789405281);

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `reserved_at` varchar(30) NOT NULL,
  `guests` int(11) NOT NULL,
  `notes` text NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `created_at` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `comment` text NOT NULL,
  `rating` int(11) NOT NULL,
  `published` int(11) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `value`) VALUES
('about.chef', '\"আমাদের রান্নাঘর\"'),
('about.chef_image', '\"\\/assets\\/kala-bhuna.png\"'),
('about.description', '\"পুরান ঢাকার কাচ্চি থেকে চট্টগ্রামের কালা ভুনা—বাংলাদেশের পরিচিত স্বাদগুলো এক টেবিলে আনার আয়োজন দেশি ভোজ। পরিবার ও বন্ধুদের সঙ্গে খাবারের সময়টুকু হোক আরও আপন।\"'),
('about.image', '\"\\/assets\\/kacchi.png\"'),
('about.second', '\"পদের বিবরণ, উপকরণ ও পরিবেশনের পরিমাণ দেখে অর্ডার করুন। ঝালের পছন্দ বা অ্যালার্জির কথা অর্ডারের নোটে জানান; বিশেষ অনুরোধ পূরণ করা যাবে কি না আমাদের দল নিশ্চিত করবে।\"'),
('about.title', '\"প্রতিটি পদে বাংলার গল্প।\"'),
('allergen.notice', '\"আমাদের রান্নাঘরে মাছ, দুধ, সরিষা, বাদাম, ডিম ও গম ব্যবহার হয়। অ্যালার্জি থাকলে অর্ডারের আগে যোগাযোগ করুন। অ্যালার্জেনমুক্ত রান্নাঘরের নিশ্চয়তা দেওয়া হয় না।\"'),
('analytics.views', '22'),
('announcement', '\"Your favourite Bangladeshi food • Delivery &amp;amp;amp;amp;amp;amp; pickup\"'),
('brand.logo', '\"\"'),
('brand.name', '\"Deshi Bhoj\"'),
('brand.tagline', '\"The taste of <strong class=\\\"ql-color-red\\\"><em><u>Bengal<\\/u><\\/em><\\/strong>, made for you\"'),
('contact.address', '\"আপনার রেস্টুরেন্টের ঠিকানা দিন, বাংলাদেশ\"'),
('contact.email', '\"hello@example.com\"'),
('contact.hours', '\"প্রতিদিন দুপুর ১২টা – রাত ১০টা\"'),
('contact.phone', '\"+8801700000000\"'),
('delivery.body', '\"<p>Checkout-এ ডেলিভারি বা পিকআপ বেছে নিন। ডেলিভারি চার্জ ও মোট মূল্য অর্ডার নিশ্চিত করার আগেই দেখা যাবে।<\\/p><p>বাসা, রোড, এলাকা ও প্রয়োজনীয় নির্দেশনাসহ ঠিকানা দিন। ডেলিভারি এলাকা ও সময় আমাদের দল নিশ্চিত করবে।<\\/p><p>Cash on delivery বা পিকআপে নগদ দিতে পারবেন। অনলাইন পেমেন্ট চালু থাকলে bKash\\/Nagad payment partner-এর মাধ্যমে পাওয়া যাবে।<\\/p>\"'),
('delivery.title', '\"রান্নাঘর থেকে আপনার টেবিলে।\"'),
('footer.description', '\"বাংলাদেশের পরিচিত খাবার, একসঙ্গে খাওয়ার আনন্দ। দেশি ভোজে আপনাকে স্বাগতম।\"'),
('hero.accent', '\"দেশি স্বাদে।\"'),
('hero.description', '\"সুগন্ধি কাচ্চি, চট্টগ্রামের কালা ভুনা আর সর্ষে ইলিশ। পছন্দের খাবার বেছে নিন—বাসায় ডেলিভারি অথবা রেস্টুরেন্ট থেকে সংগ্রহ করুন।\"'),
('hero.eyebrow', '\"বাংলাদেশের চেনা স্বাদ\"'),
('hero.image', '\"\\/assets\\/kacchi.png\"'),
('hero.title', '\"মন ভরে খাই,\"'),
('home.menu_description', '\"জনপ্রিয় দেশি পদ থেকে বেছে নিন আপনার পছন্দ। প্রতিটি পদের দাম বাংলাদেশি টাকায়।\"'),
('home.menu_title', '\"আজ কী খেতে ইচ্ছে করছে?\"'),
('home.reserve_description', '\"পরিবারের আয়োজন বা বন্ধুদের আড্ডা—আপনার পছন্দের সময় জানিয়ে টেবিলের অনুরোধ পাঠান।\"'),
('home.reserve_title', '\"আড্ডা জমুক, খাবারের টেবিলে।\"'),
('navigation.about', '\"Our story\"'),
('navigation.contact', '\"Contact\"'),
('navigation.gallery', '\"Gallery\"'),
('navigation.home', '\"Home\"'),
('navigation.menu', '\"Food menu\"'),
('ordering.delivery_enabled', 'true'),
('ordering.delivery_fee', '6000'),
('ordering.delivery_time', '\"৪৫–৬০ মিনিট\"'),
('ordering.minimum', '0'),
('ordering.pickup_time', '\"২০–৩০ মিনিট\"'),
('ordering.service_area', '\"ডেলিভারি এলাকা নিশ্চিত করতে অর্ডারের আগে যোগাযোগ করুন। সম্পূর্ণ ঠিকানা এবং সচল বাংলাদেশি মোবাইল নম্বর দিন। সময় যানজট ও অর্ডারের চাপ অনুযায়ী পরিবর্তিত হতে পারে।\"'),
('privacy.body', '\"<p>অর্ডার, টেবিল বুকিং ও যোগাযোগের জন্য আপনার নাম, ফোন, ইমেইল ও ঠিকানা ব্যবহার করা হয়।<\\/p><p>Cart ও login সচল রাখতে session cookie ব্যবহৃত হয়। পেমেন্ট সেবাদাতা অনলাইন লেনদেন পরিচালনা করে; এই সাইট wallet PIN বা OTP সংগ্রহ করে না।<\\/p><p>আপনার তথ্য সম্পর্কে জানতে যোগাযোগ পাতার মাধ্যমে রেস্টুরেন্টের সঙ্গে যোগাযোগ করুন।<\\/p>\"'),
('privacy.title', '\"গোপনীয়তা নীতি\"'),
('reservation.max_guests', '12'),
('seo.description', '\"কাচ্চি বিরিয়ানি, কালা ভুনা ও সর্ষে ইলিশ। দেশি ভোজের মেনু দেখুন, অনলাইনে অর্ডার করুন অথবা টেবিলের অনুরোধ পাঠান।\"'),
('seo.title', '\"দেশি ভোজ | বাংলাদেশি খাবার, ডেলিভারি ও পিকআপ\"'),
('social.facebook', '\"\"'),
('social.instagram', '\"\"'),
('system.bangladesh', 'true'),
('system.edition2', 'true'),
('terms.body', '\"<p>অর্ডার ও টেবিলের অনুরোধ স্টকের প্রাপ্যতা এবং রেস্টুরেন্টের নিশ্চিতকরণের ওপর নির্ভরশীল। মূল্য বাংলাদেশি টাকায়; ডেলিভারি চার্জ checkout-এ দেখা যায়।<\\/p><p>পরিবর্তন বা বাতিলের জন্য অর্ডার নম্বরসহ দ্রুত যোগাযোগ করুন। রান্না শুরু হয়ে গেলে পরিবর্তন সম্ভব নাও হতে পারে। অনুমোদিত refund মূল পেমেন্ট মাধ্যমেই প্রক্রিয়া করা হয়।<\\/p><p>ছবিগুলো illustrative demo; পরিবেশনে পার্থক্য হতে পারে। অ্যালার্জি থাকলে আগে জানান।<\\/p>\"'),
('terms.title', '\"ব্যবহারের শর্তাবলি\"');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `session_version` int(11) NOT NULL DEFAULT 1,
  `created_at` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `session_version`, `created_at`) VALUES
(1, 'sharuu', 'sharuu@gmail.com', '$2y$12$uPlRbwXefR2v/ZtUOjuMRu0ooJQADDLWY1XR9RH9acdfm67yJDZ.q', 'owner', 1, '2026-09-14 22:39:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `content_translations`
--
ALTER TABLE `content_translations`
  ADD PRIMARY KEY (`entity`,`entity_id`,`locale`,`field`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `features`
--
ALTER TABLE `features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `url` (`url`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD UNIQUE KEY `access_token` (`access_token`),
  ADD UNIQUE KEY `idempotency_key` (`idempotency_key`);

--
-- Indexes for table `order_events`
--
ALTER TABLE `order_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD UNIQUE KEY `provider_reference` (`provider_reference`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`bucket`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `features`
--
ALTER TABLE `features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_events`
--
ALTER TABLE `order_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
