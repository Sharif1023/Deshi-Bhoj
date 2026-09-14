<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <p class="text-sm text-stone-500">Manage the content your guests see.</p>
    <a class="rounded-full bg-emerald-900 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-800" href="/control-center/<?= e($section); ?>/new<?= $categoryId ? '?category_id=' . $categoryId : ''; ?>">+ Add new item</a>
</div>
<?php if (in_array($section, ['products', 'categories'], true)): ?>
<nav class="mb-5 flex gap-2 overflow-x-auto rounded-2xl bg-emerald-950 p-2 text-sm text-white sm:flex-wrap" aria-label="Filter by category">
    <?php $categoryLink = static fn($id) => '/control-center/' . $section . '?' . http_build_query(['category_id' => $id, 'q' => $query]); ?>
    <a class="shrink-0 rounded-full px-4 py-2.5 hover:bg-white/10 aria-[current=page]:bg-[#d8c6a2] aria-[current=page]:text-emerald-950" href="<?= e($categoryLink(0)); ?>" <?= !$categoryId ? 'aria-current="page"' : ''; ?>>All categories</a>
    <?php foreach ($categories as $c): ?><a class="shrink-0 rounded-full px-4 py-2.5 hover:bg-white/10 aria-[current=page]:bg-[#d8c6a2] aria-[current=page]:text-emerald-950" href="<?= e($categoryLink((int) $c['id'])); ?>" <?= $categoryId === (int) $c['id'] ? 'aria-current="page"' : ''; ?>><?= e(plain($c['name'])); ?></a><?php endforeach; ?>
</nav>
<?php endif; ?>
<form class="mb-6 flex flex-wrap gap-3" method="get">
    <input type="hidden" name="category_id" value="<?= $categoryId; ?>">
    <input class="min-w-0 flex-1 rounded-xl border border-stone-300 bg-white p-3 text-sm" name="q" type="search" value="<?= e($query); ?>" placeholder="Search items…" aria-label="Search items">
    <button class="rounded-full bg-ink px-6 py-3 text-sm font-semibold text-white">Search</button>
</form>
<div class="mb-4 text-sm text-stone-500"><?= count($rows); ?> items</div>
<div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
<?php $categoryNames = array_column($categories, 'name', 'id'); foreach ($rows as $row):
    $rowImages = isset($row['images']) ? json_decode($row['images'], true) : (!empty($row['image']) ? [$row['image']] : []);
    $rowTitle = plain($row['name'] ?? $row['title']);
?>
    <article class="min-w-0 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
        <div class="flex items-start gap-4">
            <?php if ($rowImages): ?><img class="h-20 w-20 shrink-0 rounded-xl object-cover" src="<?= e(safeImage($rowImages[0])); ?>" alt="<?= e($rowTitle); ?>"><?php endif; ?>
            <div class="min-w-0 flex-1"><h2 class="break-words text-lg font-semibold"><?= e($rowTitle); ?></h2>
                <?php if (isset($row['category_id'])): ?><p class="mt-1 text-xs text-emerald-800"><?= e(plain($categoryNames[$row['category_id']] ?? '')); ?></p><?php endif; ?>
                <p class="mt-2 text-sm text-stone-500"><?php if (isset($row['price'])): ?><?= money((int) $row['price']); ?> · <?= $row['available'] ? 'Available' : 'Unavailable'; ?><?php elseif (isset($row['published'])): ?><?= $row['published'] ? 'Published' : 'Draft'; ?><?php else: ?>Position <?= (int) $row['sort_order']; ?><?php endif; ?></p>
            </div>
        </div>
        <div class="mt-5 flex items-center justify-between border-t border-stone-100 pt-4">
            <a class="text-sm font-semibold text-emerald-900 hover:underline" href="/control-center/<?= e($section); ?>/<?= (int) $row['id']; ?>/edit">Edit item →</a>
            <form method="post" action="/control-center/<?= e($section); ?>/delete" data-confirm="Delete this item? This cannot be undone."><?= csrf(); ?><input type="hidden" name="id" value="<?= (int) $row['id']; ?>"><button class="text-sm text-red-700">Delete</button></form>
        </div>
    </article>
<?php endforeach; ?>
</div>
<?php if (!$rows): ?><p class="rounded-2xl bg-white p-10 text-center text-stone-500">No records found.</p><?php endif; ?>
