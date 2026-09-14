<?php
declare(strict_types=1);
namespace App\Services;
use App\Core\{DB, ValidationException};

/** Explicit editor translations; legacy content remains a fallback. */
final class Translations
{
    private static array $cache = [];
    public static function fields(string $entity): array
    {
        if ($entity === 'settings') {
            $fields = [];
            foreach (require ROOT . '/config/settings.php' as $group) {
                foreach ($group as $field => $type) {
                    if (in_array($type, ['text', 'textarea', 'rich', 'rich-inline'], true) && $field !== 'contact.phone') $fields[$field] = $type;
                }
            }
            return $fields;
        }
        $configs = require ROOT . '/config/entities.php';
        return array_filter($configs[$entity]['fields'] ?? [], static fn($type) => in_array($type, ['text?', 'text', 'textarea', 'rich', 'rich-inline'], true));
    }
    public static function values(string $entity, int $id): array
    {
        $key = $entity . ':' . $id;
        if (!isset(self::$cache[$key])) {
            $values = [];
            try {
                foreach (DB::all('SELECT locale,field,value FROM content_translations WHERE entity=? AND entity_id=?', [$entity, $id]) as $row) $values[$row['locale']][$row['field']] = $row['value'];
            } catch (\PDOException $e) {
                if (($e->errorInfo[1] ?? null) !== 1146 && $e->getCode() !== '42S02') throw $e;
                // Old installations still render until the documented upgrade command runs.
            }
            self::$cache[$key] = $values;
        }
        return self::$cache[$key];
    }
    public static function value(string $entity, int $id, string $field, string $language): ?string
    {
        return self::values($entity, $id)[$language][$field] ?? null;
    }
    public static function draft(string $entity, int $id, string $field, string $language, string $fallback): string
    {
        $saved = self::value($entity, $id, $field, $language);
        if ($saved !== null) return $saved;
        $source = preg_match('/[\x{0980}-\x{09FF}]/u', plain($fallback)) ? 'bn' : 'en';
        if ($source === $language) return $fallback;
        $translated = Locale::translateFragment($fallback, $language);
        return plain($translated) === plain($fallback) ? '' : $translated;
    }
    public static function row(string $entity, array $row): array
    {
        foreach (self::fields($entity) as $field => $_type) {
            $value = self::value($entity, (int) $row['id'], $field, Locale::current());
            if ($value !== null) {
                $row[$field] = $value;
                Locale::protectContent($value);
            }
        }
        return $row;
    }
    public static function rows(string $entity, array $rows): array
    {
        if (!$rows) return [];
        // Load the entire page in one query, including explicit empty caches.
        $ids = array_map(static fn($r) => (int) $r['id'], $rows);
        $missing = array_values(array_filter($ids, static fn($id) => !isset(self::$cache[$entity . ':' . $id])));
        if ($missing) {
            foreach ($missing as $id) self::$cache[$entity . ':' . $id] = [];
            try {
                $placeholders = implode(',', array_fill(0, count($missing), '?'));
                foreach (DB::all("SELECT entity_id,locale,field,value FROM content_translations WHERE entity=? AND entity_id IN ($placeholders)", [$entity, ...$missing]) as $r) self::$cache[$entity . ':' . $r['entity_id']][$r['locale']][$r['field']] = $r['value'];
            } catch (\PDOException $e) {
                if (($e->errorInfo[1] ?? null) !== 1146 && $e->getCode() !== '42S02') throw $e;
            }
        }
        return array_map(static fn($r) => self::row($entity, $r), $rows);
    }
    public static function submitted(string $entity, array $allowedFields): array
    {
        $translations = $_POST['translations'] ?? null;
        if ($translations === null) return [];
        if (!is_array($translations)) throw new ValidationException('Invalid translations.');
        $result = [];
        foreach (['en', 'bn'] as $language) {
            if (!isset($translations[$language]) || !is_array($translations[$language])) throw new ValidationException('Both language fields must be submitted.');
            foreach (self::fields($entity) as $field => $type) {
                if (!array_key_exists($field, $allowedFields)) continue;
                $value = $translations[$language][$field] ?? null;
                if (!is_string($value) || strlen($value) > 20000) throw new ValidationException('Invalid translated field: ' . $field);
                $result[$language][$field] = in_array($type, ['rich', 'rich-inline'], true) ? RichText::clean($value, $type === 'rich-inline') : trim($value);
            }
        }
        return $result;
    }
    public static function save(string $entity, int $id, array $translations): void
    {
        foreach ($translations as $language => $fields) foreach ($fields as $field => $value) {
            DB::run('DELETE FROM content_translations WHERE entity=? AND entity_id=? AND locale=? AND field=?', [$entity, $id, $language, $field]);
            if (plain($value) !== '') DB::insert('content_translations', ['entity' => $entity, 'entity_id' => $id, 'locale' => $language, 'field' => $field, 'value' => $value]);
        }
        unset(self::$cache[$entity . ':' . $id]);
    }
}
