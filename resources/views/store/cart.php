<section class="section mx-auto px-5 py-16 lg:px-10 max-w-4xl"><p class="eyebrow text-xs font-semibold uppercase tracking-[.22em] text-accent">আপনার পছন্দের খাবার</p><h1 class="font-display [overflow-wrap:anywhere] my-5 text-5xl">আপনার কার্ট</h1><?php
$total = 0;
foreach ($cart as $p):
    $total +=
        $p['quantity'] *
        $p[
            'price'
        ]; ?><div class="my-4 flex flex-wrap items-center justify-between gap-5 rounded-xl bg-white p-5"><div><a class="font-display text-2xl" href="/menu/<?= e(
    $p['slug'],
) ?>"><?= e(plain($p['name'])) ?></a><p class="text-sm text-stone-500"><?= money(
    (int) $p['price'],
) ?> each <?= !$p['available']
     ? ' · এখন পাওয়া যাচ্ছে না; এগোতে এটি সরান'
     : '' ?></p></div><form method="post" action="/cart" class="flex items-center gap-3"><?= csrf() ?><input type="hidden" name="action" value="update"><input type="hidden" name="product_id" value="<?= $p[
    'id'
] ?>"><input class="rounded-lg border border-stone-300 bg-white p-3 text-sm text-ink [&[type=checkbox]]:w-auto [&[type=radio]]:w-auto w-20" name="quantity" aria-label="Quantity for <?= e(
    $p['name'],
) ?>" type="number" min="0" max="20" value="<?= $p[
    'quantity'
] ?>"><button class="btn-dark inline-flex items-center justify-center rounded-full px-6 py-3 text-sm font-semibold text-white transition disabled:opacity-40 bg-ink hover:bg-stone-700">আপডেট</button></form><span><?= money(
    $p['quantity'] * $p['price'],
) ?></span></div><?php
endforeach;
if (
    !$cart
): ?><p class="my-10 text-stone-500">এখনও কোনো খাবার যোগ করেননি।</p><a class="btn inline-flex items-center justify-center rounded-full bg-accent px-6 py-3 text-sm font-semibold text-white transition hover:bg-orange-800 disabled:opacity-40" href="/menu">মেনু দেখুন</a><?php else: ?><p class="my-4 text-xs text-stone-500">কোনো পদ সরাতে পরিমাণ ০ দিয়ে আপডেট করুন।</p><div class="my-8 flex items-center justify-between border-t pt-6"><strong>খাবারের মোট</strong><strong><?= money(
    $total,
) ?></strong></div><a class="btn inline-flex items-center justify-center rounded-full bg-accent px-6 py-3 text-sm font-semibold text-white transition hover:bg-orange-800 disabled:opacity-40" href="/checkout">অর্ডার সম্পন্ন করুন →</a><?php endif;
?></section>
