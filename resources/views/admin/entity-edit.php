<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <a class="text-sm font-semibold text-emerald-900 hover:underline" href="/control-center/<?= e($section); ?>">← Back to list</a>
    <span class="rounded-full bg-white px-4 py-2 text-sm text-stone-500"><?= e($config['label']); ?></span>
</div>
<form id="editor" action="/control-center/<?= e($section); ?>/save" method="post" class="mx-auto max-w-5xl space-y-6" data-content-form>
    <?= csrf(); ?><input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0); ?>">
    <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-8">
        <h2 class="mb-6 text-2xl font-semibold text-emerald-950"><?= $edit ? 'Edit item' : 'Add new item'; ?></h2>
        <?php require __DIR__ . '/content-tabs.php'; ?>
        <div class="mt-6 grid gap-6 md:grid-cols-2">
            <?php foreach ($config['fields'] as $field => $type):
                $fallback = $edit[$field] ?? match ($type) { 'checkbox', 'number' => 0, 'rating' => 5, default => '' };
                if ($field === 'category_id' && !$edit) $fallback = $categoryId ?: '';
                $label = ucwords(str_replace('_', ' ', $field));
                $translated = array_key_exists($field, App\Services\Translations::fields($section));
            ?>
                <div class="min-w-0 <?= in_array($type, ['rich', 'rich-inline', 'images', 'image', 'textarea'], true) ? 'md:col-span-2' : ''; ?>">
                    <?php if ($translated): foreach (['en', 'bn'] as $language):
                        $value = App\Services\Translations::draft($section, (int) ($edit['id'] ?? 0), $field, $language, (string) $fallback);
                        $name = 'translations[' . $language . '][' . $field . ']'; $inputId = 'field-' . $field . '-' . $language;
                    ?>
                        <div class="[&[hidden]]:hidden" data-content-panel="<?= $language; ?>" lang="<?= $language; ?>" data-no-translate><?php require __DIR__ . '/input-field.php'; ?></div>
                    <?php endforeach; else:
                        $value = $type === 'money' && $fallback !== '' ? number_format((int) $fallback / 100, 2, '.', '') : $fallback;
                        $name = $field; $inputId = 'field-' . $field;
                        require __DIR__ . '/input-field.php';
                    endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="sticky bottom-3 z-10 flex flex-wrap justify-end gap-3 rounded-2xl border border-stone-200 bg-white/95 p-4 shadow-lg backdrop-blur">
        <a class="rounded-full border border-stone-300 px-6 py-3 text-sm font-medium" href="/control-center/<?= e($section); ?>">Cancel</a>
        <button class="rounded-full bg-emerald-900 px-7 py-3 text-sm font-semibold text-white hover:bg-emerald-800 disabled:opacity-40" type="submit">Save changes</button>
    </div>
</form>
