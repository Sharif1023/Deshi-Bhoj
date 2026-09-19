<?php
$selectedCategory = is_string($_GET['category'] ?? null)
    ? $_GET['category']
    : '';

$menuUrl = static function (string $category): string {
    return '/menu?' .
        http_build_query(
            array_filter(
                [
                    'category' => $category,
                    'lang' => App\Services\Locale::current(),
                ],
                static fn($value) => $value !== '',
            ),
        );
};
?>

<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-16 lg:px-10">

    <!-- =========================
         PAGE HEADING
         ========================= -->

    <p class="text-xs font-semibold uppercase tracking-[.22em] text-accent">
        দেশি স্বাদের আয়োজন
    </p>

    <h1
        class="font-display [overflow-wrap:anywhere] my-5
               text-4xl leading-tight text-[#173d2e]
               sm:text-5xl md:text-6xl"
    >
        আপনার পছন্দের খাবার বেছে নিন।
    </h1>

    <p class="max-w-2xl leading-7 text-stone-500">
        কাচ্চি, কালা ভুনা ও মাছের পদ—বাংলার চেনা স্বাদ।
    </p>


    <!-- =========================
         CATEGORY MENU
         ========================= -->

    <nav
        aria-label="খাবারের বিভাগ"
        class="mt-8 flex gap-2 overflow-x-auto rounded-2xl
               bg-[#153e2e] p-2 text-sm text-paper shadow-sm
               sm:flex-wrap"
    >

        <!-- All products -->
        <a
            href="<?= e($menuUrl('')) ?>"
            <?= $selectedCategory === ''
                ? 'aria-current="page"'
                : '' ?>
            class="shrink-0 rounded-full px-5 py-3 font-medium
                   transition-colors
                   hover:bg-white/10
                   hover:text-[#d8c6a2]
                   aria-[current=page]:bg-[#d8c6a2]
                   aria-[current=page]:text-[#153e2e]"
        >
            সব খাবার
        </a>


        <!-- Categories -->
        <?php foreach ($categories as $category): ?>

            <a
                href="<?= e($menuUrl($category['slug'])) ?>"
                <?= $selectedCategory === $category['slug']
                    ? 'aria-current="page"'
                    : '' ?>
                class="shrink-0 rounded-full px-5 py-3 font-medium
                       transition-colors
                       hover:bg-white/10
                       hover:text-[#d8c6a2]
                       aria-[current=page]:bg-[#d8c6a2]
                       aria-[current=page]:text-[#153e2e]"
            >
                <?= e(plain($category['name'])) ?>
            </a>

        <?php endforeach; ?>

    </nav>


    <!-- =========================
         PRODUCTS
         ========================= -->

    <div class="menu-products-wrapper mt-8">

        <?php require __DIR__ . '/cards.php'; ?>

    </div>

</section>