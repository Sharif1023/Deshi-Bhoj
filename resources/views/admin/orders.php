<div class="mb-6 flex flex-wrap items-center justify-between gap-4"><p class="text-sm text-stone-500">Find an order, open its details and move it to the next step.</p><a class="rounded-full border border-stone-300 bg-white px-5 py-2.5 text-sm font-semibold" href="/control-center/export/orders">Export CSV</a></div>
<?php $filterUrl = static fn($state) => '/control-center/orders?' . http_build_query(['status' => $state, 'type' => $type, 'q' => $query]); ?>
<nav class="mb-5 flex gap-2 overflow-x-auto rounded-2xl bg-emerald-950 p-2 text-sm text-white sm:flex-wrap" aria-label="Filter order status">
    <a class="shrink-0 rounded-full px-4 py-2.5 aria-[current=page]:bg-[#d8c6a2] aria-[current=page]:text-emerald-950" href="<?= e($filterUrl('')); ?>" <?= $status === '' ? 'aria-current="page"' : ''; ?>>All orders (<?= array_sum($counts); ?>)</a>
    <?php foreach (App\Services\OrderService::STATUSES as $state): ?><a class="shrink-0 rounded-full px-4 py-2.5 hover:bg-white/10 aria-[current=page]:bg-[#d8c6a2] aria-[current=page]:text-emerald-950" href="<?= e($filterUrl($state)); ?>" <?= $status === $state ? 'aria-current="page"' : ''; ?>><?= e($state); ?> (<?= (int) ($counts[$state] ?? 0); ?>)</a><?php endforeach; ?>
</nav>
<form class="mb-6 grid gap-3 rounded-2xl border border-stone-200 bg-white p-4 md:grid-cols-[minmax(0,1fr)_200px_auto]" method="get">
    <input type="hidden" name="status" value="<?= e($status); ?>">
    <input class="min-w-0 rounded-xl border border-stone-300 p-3 text-sm" name="q" value="<?= e($query); ?>" type="search" placeholder="Order number, customer or phone" aria-label="Search orders">
    <select class="rounded-xl border border-stone-300 bg-white p-3 text-sm" name="type" aria-label="Order type"><option value="">All order types</option><option value="pickup" <?= $type === 'pickup' ? 'selected' : ''; ?>>Pickup</option><option value="delivery" <?= $type === 'delivery' ? 'selected' : ''; ?>>Delivery</option></select>
    <button class="rounded-full bg-emerald-900 px-6 py-3 text-sm font-semibold text-white">Search</button>
</form>
<div class="grid gap-4 xl:grid-cols-2">
<?php foreach ($rows as $o): ?>
    <article class="min-w-0 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="text-lg font-bold text-emerald-950"><?= e($o['number']); ?></h2><p class="mt-1 text-xs text-stone-500"><?= e($o['created_at']); ?></p></div><span class="rounded-full px-3 py-1.5 text-xs font-semibold <?= $o['status'] === 'Cancelled' ? 'bg-red-50 text-red-800' : 'bg-emerald-50 text-emerald-900'; ?>"><?= e(App\Services\OrderService::statusLabel($o['status'], $o['order_type'])); ?></span></div>
        <div class="my-4 space-y-1 text-sm"><p class="font-medium" data-no-translate><?= e($o['customer_name']); ?></p><p class="text-stone-500" data-no-translate><?= e($o['phone']); ?></p><p><?= e(ucfirst($o['order_type'])); ?> · <?= e($o['payment_status']); ?></p></div>
        <div class="flex items-center justify-between gap-3 border-t border-stone-100 pt-4"><strong><?= money((int) $o['total']); ?></strong><a class="rounded-full bg-emerald-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800" href="/control-center/orders/<?= (int) $o['id']; ?>">Open order →</a></div>
    </article>
<?php endforeach; ?>
</div>
<?php if (!$rows): ?><p class="rounded-2xl bg-white p-10 text-center text-stone-500">No records found.</p><?php endif; ?>
<div class="mt-6 flex flex-wrap justify-between gap-4 text-sm"><span><?= $total; ?> records · Page <?= $pageNumber; ?></span><div class="flex gap-5">
<?php foreach (['← Previous' => $pageNumber - 1, 'Next →' => $pageNumber + 1] as $label => $number): if ($number < 1 || ($number > $pageNumber && $pageNumber * 25 >= $total)) continue; ?>
    <a class="font-medium text-emerald-900 underline" href="?<?= e(http_build_query(['page' => $number, 'q' => $query, 'status' => $status, 'type' => $type])); ?>"><?= e($label); ?></a>
<?php endforeach; ?></div></div>
