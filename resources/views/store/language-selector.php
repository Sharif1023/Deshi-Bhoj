<?php
$languageId = $languageId ?? 'site-language';
$languagePath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (!str_starts_with($languagePath, '/') || str_starts_with($languagePath, '//')) {
    $languagePath = '/';
}
?>
<form method="get" action="<?= e(
    $languagePath,
) ?>" class="flex shrink-0 items-center gap-0.5 rounded-full border border-white/30 px-1 py-0.5 text-paper" data-no-translate>
    <?php foreach ($_GET as $key => $value):
        if ($key !== 'lang' && is_string($value)): ?>
        <input type="hidden" name="<?= e($key) ?>" value="<?= e($value) ?>">
    <?php endif;
    endforeach; ?>
    <label class="sr-only" for="<?= e($languageId); ?>">Choose language / ভাষা বেছে নিন</label>
    <select id="<?= e($languageId); ?>" name="lang" data-language-select aria-label="Choose language / ভাষা বেছে নিন" class="min-h-11 w-auto max-w-[110px] cursor-pointer rounded-full border-0 bg-transparent py-2 pl-3 pr-5 text-sm text-inherit focus-visible:outline-[#d8c6a2]">
        <option class="bg-[#172018] text-white" value="en" lang="en" <?= App\Services\Locale::current() ===
        'en'
            ? 'selected'
            : '' ?>>English</option>
        <option class="bg-[#172018] text-white" value="bn" lang="bn" <?= App\Services\Locale::current() ===
        'bn'
            ? 'selected'
            : '' ?>>বাংলা</option>
    </select>
    <noscript><button class="min-h-11 rounded-full px-3 text-sm" type="submit">→</button></noscript>
</form>
