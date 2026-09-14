<?php if (!$selectedGroup): ?>
<p class="mb-6 text-stone-500">Choose a page to edit its content and settings.</p>
<div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
    <?php foreach ($groups as $group => $fields): ?>
        <a class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm transition hover:border-emerald-500 hover:shadow-md" href="/control-center/settings?<?= e(http_build_query(['group' => $group])); ?>">
            <h2 class="text-xl font-semibold text-emerald-950"><?= e($group); ?></h2><p class="mt-3 text-sm text-stone-500">Edit page →</p>
        </a>
    <?php endforeach; ?>
</div>
<?php else: ?>
<a class="mb-6 inline-block text-sm font-semibold text-emerald-900" href="/control-center/settings">← All settings pages</a>
<form method="post" action="/control-center/settings" class="mx-auto max-w-5xl space-y-6" data-content-form>
    <?= csrf(); ?><input type="hidden" name="settings_group" value="<?= e($selectedGroup); ?>">
    <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-8">
        <h2 class="mb-6 text-2xl font-semibold text-emerald-950"><?= e($selectedGroup); ?></h2>
        <?php require __DIR__ . '/content-tabs.php'; ?>
        <div class="mt-6 grid gap-6 md:grid-cols-2">
            <?php foreach ($groups[$selectedGroup] as $key => $type):
                $fallback = App\Services\Content::setting($key, '', false);
                $label = ucwords(str_replace(['.', '_'], ' ', $key));
                $translated = array_key_exists($key, App\Services\Translations::fields('settings'));
            ?>
            <div class="min-w-0 <?= in_array($type, ['rich', 'rich-inline', 'image', 'textarea'], true) ? 'md:col-span-2' : ''; ?>">
                <?php if ($translated): foreach (['en', 'bn'] as $language):
                    $value = App\Services\Translations::draft('settings', 0, $key, $language, (string) $fallback);
                    $name = 'translations[' . $language . '][' . $key . ']'; $inputId = 'setting-' . str_replace('.', '_', $key) . '-' . $language;
                ?>
                    <div class="[&[hidden]]:hidden" data-content-panel="<?= $language; ?>" lang="<?= $language; ?>" data-no-translate><?php require __DIR__ . '/input-field.php'; ?></div>
                <?php endforeach; else:
                    $value = $type === 'money' ? number_format((int) $fallback / 100, 2, '.', '') : $fallback;
                    $name = str_replace('.', '_', $key); $inputId = 'setting-' . $name;
                    require __DIR__ . '/input-field.php';
                endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="sticky bottom-3 z-10 flex justify-end rounded-2xl border border-stone-200 bg-white/95 p-4 shadow-lg backdrop-blur">
        <button class="rounded-full bg-emerald-900 px-7 py-3 text-sm font-semibold text-white hover:bg-emerald-800 disabled:opacity-40">Save changes</button>
    </div>
</form>
<?php endif; ?>
