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

<style>
/* =====================================================
   MENU PRODUCT GRID
   ===================================================== */

.menu-products-wrapper > .grid {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    align-items: stretch !important;
    gap: 1rem !important;
}

/*
 * প্রত্যেক product একই height
 */
.menu-products-wrapper > .grid > * {
    min-width: 0;
    height: 100%;
}

/*
 * Product card equal height
 */
.menu-products-wrapper > .grid > article,
.menu-products-wrapper > .grid > a,
.menu-products-wrapper > .grid > div {
    height: 100%;
}


/* =====================================================
   MOBILE PRODUCT CARD
   ===================================================== */

@media (max-width: 639.98px) {

    /*
     * Mobile-এ প্রতি row-তে 2টি product
     */
    .menu-products-wrapper > .grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        grid-auto-rows: 1fr !important;
        gap: 0.75rem !important;
    }


    /*
     * প্রত্যেক card একই height
     */
    .menu-products-wrapper > .grid > article {
        display: flex !important;
        height: 100% !important;
        flex-direction: column !important;
    }


    /*
     * Product image সব একই size
     */
    .menu-products-wrapper article img {
        display: block;
        width: 100% !important;
        aspect-ratio: 1 / 1 !important;
        object-fit: cover !important;
    }


    /*
     * Product description mobile-এ hide
     */
    .menu-products-wrapper .product-description,
    .menu-products-wrapper .menu-product-description,
    .menu-products-wrapper [class*="product-description"],
    .menu-products-wrapper [class*="description"],
    .menu-products-wrapper .rich-content {
        display: none !important;
    }


    /*
     * Product name maximum 2 line।
     * সব card-এর name area একই size থাকবে।
     */
    .menu-products-wrapper article h2,
    .menu-products-wrapper article h3 {
        display: -webkit-box !important;
        min-height: 2.65rem !important;
        margin-bottom: 0.35rem !important;

        overflow: hidden !important;

        font-size: 0.9rem !important;
        line-height: 1.3rem !important;

        -webkit-box-orient: vertical !important;
        -webkit-line-clamp: 2 !important;
    }


    /*
     * ভিতরের content overflow করবে না
     */
    .menu-products-wrapper article > div,
    .menu-products-wrapper article a {
        min-width: 0;
    }
}


/* =====================================================
   TABLET + DESKTOP
   ===================================================== */

@media (min-width: 640px) {

    .menu-products-wrapper > .grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 1.25rem !important;
    }

}
</style>


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