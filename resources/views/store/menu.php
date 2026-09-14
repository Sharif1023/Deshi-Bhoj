<?php
$query = is_string($_GET['q'] ?? null) ? $_GET['q'] : '';
$selectedCategory = is_string($_GET['category'] ?? null) ? $_GET['category'] : '';
$sort = is_string($_GET['sort'] ?? null) ? $_GET['sort'] : '';
$menuUrl = static function (string $category) use ($query, $sort): string {
    return '/menu?' .
        http_build_query(
            array_filter(
                [
                    'q' => $query,
                    'category' => $category,
                    'sort' => $sort,
                    'lang' => App\Services\Locale::current(),
                ],
                static fn($value) => $value !== '',
            ),
        );
};
?>
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-16 lg:px-10">
    <p class="text-xs font-semibold uppercase tracking-[.22em] text-accent">দেশি স্বাদের আয়োজন</p>
    <h1 class="font-display [overflow-wrap:anywhere] my-5 text-4xl leading-tight text-[#173d2e] sm:text-5xl md:text-6xl">আপনার পছন্দের খাবার বেছে নিন।</h1>
    <p class="max-w-2xl leading-7 text-stone-500">কাচ্চি, কালা ভুনা ও মাছের পদ—বাংলার চেনা স্বাদ।</p>

    <nav aria-label="খাবারের বিভাগ" class="mt-8 flex gap-2 overflow-x-auto rounded-2xl bg-[#153e2e] p-2 text-sm text-paper shadow-sm sm:flex-wrap">
        <a href="<?= e($menuUrl('')) ?>" <?= $selectedCategory === ''
    ? 'aria-current="page"'
    : '' ?> class="shrink-0 rounded-full px-5 py-3 font-medium transition-colors hover:bg-white/10 hover:text-[#d8c6a2] aria-[current=page]:bg-[#d8c6a2] aria-[current=page]:text-[#153e2e]">সব খাবার</a>
        <?php foreach ($categories as $category): ?>
            <a href="<?= e($menuUrl($category['slug'])) ?>" <?= $selectedCategory ===
$category['slug']
    ? 'aria-current="page"'
    : '' ?> class="shrink-0 rounded-full px-5 py-3 font-medium transition-colors hover:bg-white/10 hover:text-[#d8c6a2] aria-[current=page]:bg-[#d8c6a2] aria-[current=page]:text-[#153e2e]"><?= e(
     plain($category['name']),
 ) ?></a>
        <?php endforeach; ?>
    </nav>

    <form action="/menu" method="get" role="search" class="mb-9 mt-5 grid items-end gap-3 rounded-2xl border border-stone-200 bg-white p-4 sm:p-5 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_auto]">
        <input type="hidden" name="category" value="<?= e($selectedCategory) ?>">
        <input type="hidden" name="lang" value="<?= e(App\Services\Locale::current()) ?>">
        <div class="min-w-0">
            <label class="mb-2 block text-sm font-medium" for="q">খাবার খুঁজুন</label>
            <input class="text-ink [&[type=checkbox]]:w-auto [&[type=radio]]:w-auto w-full rounded-xl border border-stone-300 bg-stone-50 p-3 text-sm" type="search" id="q" name="q" maxlength="100" placeholder="পছন্দের খাবার লিখুন…" value="<?= e(
                $query,
            ) ?>">
        </div>
        <div class="min-w-0">
            <label class="mb-2 block text-sm font-medium" for="menu-sort">Sort</label>
            <select class="text-ink w-full cursor-pointer rounded-xl border border-stone-300 bg-stone-50 p-3 text-sm" id="menu-sort" name="sort">
                <option value="">আমাদের বাছাই</option>
                <option value="price_asc" <?= $sort === 'price_asc'
                    ? 'selected'
                    : '' ?>>দাম: কম থেকে বেশি</option>
                <option value="price_desc" <?= $sort === 'price_desc'
                    ? 'selected'
                    : '' ?>>দাম: বেশি থেকে কম</option>
            </select>
        </div>
        <button class="inline-flex min-h-12 items-center justify-center rounded-full bg-[#174934] px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#286549]">খুঁজুন</button>
    </form>
    <?php require __DIR__ . '/cards.php'; ?>
</section>
