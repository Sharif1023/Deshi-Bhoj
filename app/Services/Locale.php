<?php
declare(strict_types=1);
namespace App\Services;

/** Presentation-only translations. Never rewrites stored content or form values. */
final class Locale
{
    private static string $current = 'en';
    private static array $catalogues = [];
    private static array $protected = [];

    public static function boot(): void
    {
        $choice = $_COOKIE['site_language'] ?? 'en';
        if (!is_string($choice) || !in_array($choice, ['en', 'bn'], true)) $choice = 'en';
        $requested = $_GET['lang'] ?? null;
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && is_string($requested) && in_array($requested, ['en', 'bn'], true)) {
            $choice = $requested;
            setcookie('site_language', $choice, [
                'expires' => time() + 365 * 86400, 'path' => '/',
                'secure' => env('SESSION_SECURE', 'false') === 'true',
                'httponly' => true, 'samesite' => 'Lax',
            ]);
        }
        self::$current = $choice;
    }

    public static function current(): string { return self::$current; }

    public static function catalogue(string $language): array
    {
        return self::$catalogues[$language] ??= json_decode(
            file_get_contents(ROOT . '/resources/lang/' . ($language === 'bn' ? 'bn' : 'en') . '.json'),
            true, 512, JSON_THROW_ON_ERROR
        );
    }

    public static function text(string $text): string
    {
        $key = trim($text);
        if ($key === '' || isset(self::$protected[$key])) return $text;
        $map = self::catalogue(self::$current);
        $value = $map[$key] ?? null;
        if ($value === null) {
            // Only these UI compositions contain live values; do not translate arbitrary substrings.
            $patterns = self::$current === 'en' ? [
                '/^কার্ট \((\d+)\)$/u' => 'Cart ($1)',
                '/^(চালু আছে|এখন পাওয়া যাচ্ছে না) · (.+)$/u' => fn($m) => self::text($m[1]).' · '.self::text($m[2]),
                '/^Quantity for (.+)$/u' => fn($m) => 'Quantity for '.self::text($m[1]),
                '/^(.+) × (\d+)$/u' => fn($m) => self::text($m[1]).' × '.$m[2],
                '/^পেমেন্ট করুন: (bKash|Nagad) →$/u' => 'Pay with: $1 →',
                '/^Pickup: (.+)\. Delivery: (.+)\. Delivery fee (.+); minimum (.+)\.$/u' => fn($m) => 'Pickup: '.self::text($m[1]).'. Delivery: '.self::text($m[2]).'. Delivery fee '.$m[3].'; minimum '.$m[4].'.',
                '/^(\d+) মিনিট প্রস্তুতি$/u' => '$1 min preparation',
                '/^আনুমানিক (.+)$/u' => fn($m) => 'Approx. ' . self::text($m[1]),
                '/^ডেলিভারি \(\+(.+)\)$/u' => 'Delivery (+$1)',
                '/^ডেলিভারির সর্বনিম্ন অর্ডার: (.+)। প্রযোজ্য কর মূল্যের অন্তর্ভুক্ত। ডেলিভারি আমাদের দল নিশ্চিত করবে।$/u' => 'Minimum delivery order: $1. Applicable taxes are included. Our team will confirm delivery.',
                '/^চার্জ (.+) · সর্বনিম্ন (.+)$/u' => 'Fee $1 · Minimum $2',
                '/^© (\d+) (.+)\. সর্বস্বত্ব সংরক্ষিত।$/u' => fn($m) => '© '.$m[1].' '.self::text($m[2]).'. All rights reserved.',
                '/^(.+) · দেশি ভোজ$/u' => fn($m) => self::text($m[1]).' · Deshi Bhoj',
                '/^(.+) ↗$/u' => fn($m) => self::text($m[1]).' ↗',
                '/^\/ (.+)$/u' => fn($m) => '/ '.self::text($m[1]),
                '/^(.+), image (\d+)$/u' => fn($m) => self::text($m[1]).', image '.$m[2],
                '/^(.+) home$/u' => fn($m) => self::text($m[1]).' home',
                '/^(.+) · এখন পাওয়া যাচ্ছে না; এগোতে এটি সরান$/u' => fn($m) => self::text($m[1]).' · Unavailable; remove it to continue',
            ] : [
                '/^Pickup: (.+)\. Delivery: (.+)\. Delivery fee (.+); minimum (.+)\.$/u' => fn($m) => 'পিকআপ: '.self::text($m[1]).'। ডেলিভারি: '.self::text($m[2]).'। ডেলিভারি চার্জ '.$m[3].'; সর্বনিম্ন '.$m[4].'।',
                '/^Quantity for (.+)$/u' => fn($m) => self::text($m[1]).'-এর পরিমাণ',
                '/^(Tk [0-9,.]+) each$/' => '$1 প্রতিটি',
                '/^(Tk [0-9,.]+) each · এখন পাওয়া যাচ্ছে না; এগোতে এটি সরান$/u' => '$1 প্রতিটি · এখন পাওয়া যাচ্ছে না; এগোতে এটি সরান',
                '/^View image (\d+)$/' => 'ছবি $1 দেখুন',
                '/^(.+), image (\d+)$/' => fn($m) => self::text($m[1]).', ছবি '.$m[2],
                '/^(.+) home$/' => fn($m) => self::text($m[1]).' হোম',
                '/^(\d+) out of 5$/' => '৫-এর মধ্যে $1',
            ];
            foreach ($patterns as $pattern => $replacement) {
                if (preg_match($pattern, $key)) {
                    $value = is_callable($replacement) ? preg_replace_callback($pattern, $replacement, $key) : preg_replace($pattern, $replacement, $key);
                    break;
                }
            }
        }
        if ($value === null) return $text; // Custom content remains exactly as authored.
        return substr($text, 0, strlen($text) - strlen(ltrim($text))) . $value . substr($text, strlen(rtrim($text)));
    }

    public static function html(string $html): string
    {
        // Preserve raw script/style bytes; DOM serialization may entity-encode Bengali in raw-text elements.
        $rawBlocks = []; $nonce = bin2hex(random_bytes(8));
        $html = preg_replace_callback('~<(script|style)\b[^>]*>[\s\S]*?</\1\s*>~i', static function ($match) use (&$rawBlocks, $nonce) {
            $key = '<!--locale-raw-' . $nonce . '-' . count($rawBlocks) . '-->';
            $rawBlocks[$key] = $match[0]; return $key;
        }, $html);
        $document = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            if (!$document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET)) return $html;
            $xpath = new \DOMXPath($document);
            foreach ($xpath->query('//text()[not(ancestor::script or ancestor::style or ancestor::textarea or ancestor::code or ancestor::*[@data-no-translate])]') as $node) {
                $node->nodeValue = self::text($node->nodeValue);
            }
            foreach ($xpath->query('//*[@placeholder or @alt or @title or @aria-label or @data-lightbox or self::meta[@name="description"]]') as $node) {
                if ($xpath->query('ancestor-or-self::*[@data-no-translate]', $node)->length) continue;
                foreach (['placeholder', 'alt', 'title', 'aria-label', 'data-lightbox'] as $attribute) {
                    if ($node->hasAttribute($attribute)) $node->setAttribute($attribute, self::text($node->getAttribute($attribute)));
                }
                if ($node->nodeName === 'meta' && $node->getAttribute('name') === 'description') $node->setAttribute('content', self::text($node->getAttribute('content')));
            }
            $document->documentElement->setAttribute('lang', self::$current);
            foreach (iterator_to_array($document->childNodes) as $node) {
                if ($node->nodeType === XML_PI_NODE) $document->removeChild($node);
            }
            $document->encoding = 'UTF-8';
            return strtr($document->saveHTML(), $rawBlocks);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    public static function protectContent(string $html): void
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $old = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_NONET);
        $xp = new \DOMXPath($doc);
        foreach ($xp->query('//text()') as $node) self::$protected[trim($node->nodeValue)] = true;
        libxml_clear_errors(); libxml_use_internal_errors($old);
    }
    public static function translateFragment(string $html, string $language): string
    {
        $previous = self::$current; $protected = self::$protected;
        try {
            self::$current = $language; self::$protected = [];
            if (!str_contains($html, '<')) return self::text($html);
            return RichText::clean(self::html($html));
        } finally { self::$current = $previous; self::$protected = $protected; }
    }

    public static function searchTerms(string $search): array
    {
        $terms = [$search, str_replace(' ', '-', $search)];
        foreach (self::catalogue('en') as $bn => $en) {
            if (mb_stripos($en, $search) !== false) $terms[] = $bn;
        }
        return array_slice(array_values(array_unique($terms)), 0, 40);
    }
}
