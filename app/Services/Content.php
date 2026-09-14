<?php
namespace App\Services;
use App\Core\DB;
final class Content
{
    public static function setting(string $key, mixed $default = '', bool $localized = true): mixed
    {
        static $all = null;
        if ($all === null) {
            $all = [];
            foreach (DB::all('SELECT * FROM settings') as $s) {
                $all[$s['setting_key']] = json_decode($s['value'], true);
            }
        }
        $translated = $localized ? Translations::value('settings', 0, $key, Locale::current()) : null;
        if ($translated !== null) { Locale::protectContent($translated); return $translated; }
        return $all[$key] ?? $default;
    }
    public static function save(string $key, mixed $value): void
    {
        DB::run('DELETE FROM settings WHERE setting_key=?', [$key]);
        DB::insert('settings', [
            'setting_key' => $key,
            'value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
    }
    public static function products(
        string $search = '',
        string $category = '',
        string $sort = '',
    ): array {
        $sql =
            'SELECT p.*,c.name category_name FROM products p JOIN categories c ON c.id=p.category_id WHERE 1=1';
        $args = [];
        if ($search !== '') {
            $matches = [];
            foreach (Locale::searchTerms($search) as $term) {
                $matches[] = '(p.name LIKE ? OR p.description LIKE ? OR p.slug LIKE ?)';
                array_push($args, '%'.$term.'%', '%'.$term.'%', '%'.$term.'%');
            }
            $matches[] = "EXISTS (SELECT 1 FROM content_translations ct WHERE ct.entity='products' AND ct.entity_id=p.id AND ct.field IN ('name','description') AND ct.value LIKE ?)";
            $args[] = '%' . $search . '%';
            $sql .= ' AND (' . implode(' OR ', $matches) . ')';
        }
        if ($category !== '') {
            $sql .= ' AND c.slug=?';
            $args[] = $category;
        }
        $sql .=
            ' ORDER BY ' .
            match ($sort) {
                'price_asc' => 'p.price ASC',
                'price_desc' => 'p.price DESC',
                default => 'p.sort_order,p.id',
            };
        $products = Translations::rows('products', DB::all($sql, $args));
        $categories = array_column(Translations::rows('categories', DB::all('SELECT * FROM categories')), null, 'id');
        foreach ($products as &$product) $product['category_name'] = $categories[$product['category_id']]['name'] ?? $product['category_name'];
        return $products;
    }
}
