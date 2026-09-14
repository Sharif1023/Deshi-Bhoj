<?php if (empty($detail)): ?>
<div class="mb-6"><form class="flex flex-wrap gap-3"><input class="min-w-0 flex-1 rounded-xl border border-stone-300 bg-white p-3 text-sm" name="q" placeholder="Search records" value="<?= e($_GET['q'] ?? ''); ?>"><button class="rounded-full bg-emerald-900 px-6 py-3 text-sm text-white">Search</button></form></div>
<div class="grid gap-4 xl:grid-cols-2"><?php foreach ($rows as $record): ?>
<a class="min-w-0 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm hover:border-emerald-500" href="/control-center/<?= e($section); ?>/<?= (int) $record['id']; ?>"><h2 class="break-all text-lg font-semibold"><?= e($record['name'] ?? $record['transaction_id'] ?? ('#' . $record['id'])); ?></h2><p class="mt-3 text-sm text-stone-500"><?= e($record['status']); ?> · <?= e($record['created_at']); ?></p><p class="mt-4 text-sm font-semibold text-emerald-900">Open details →</p></a>
<?php endforeach; ?></div>
<?php if (!$rows): ?><p class="rounded-2xl bg-white p-10 text-center">No records found.</p><?php endif; ?>
<div class="mt-6 flex justify-between text-sm"><span><?= $total; ?> records</span><div class="flex gap-5"><?php if ($pageNumber > 1): ?><a href="?<?= e(http_build_query(['page' => $pageNumber - 1, 'q' => $_GET['q'] ?? ''])); ?>">← Previous</a><?php endif; ?><?php if ($pageNumber * 25 < $total): ?><a href="?<?= e(http_build_query(['page' => $pageNumber + 1, 'q' => $_GET['q'] ?? ''])); ?>">Next →</a><?php endif; ?></div></div>
<?php return; endif; ?>
<a class="mb-6 inline-block text-sm font-semibold text-emerald-900" href="/control-center/<?= e($section); ?>">← Back to list</a>
<div class="space-y-5"><?php
foreach ($rows as $r): ?><article class="card rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
<?php if (
    $section === 'orders'
): ?><div class="flex flex-wrap justify-between gap-4"><div><h2 class="font-display [overflow-wrap:anywhere] text-2xl"><?= e(
    $r['number'],
) ?></h2><p class="mt-2 text-sm"><?= e($r['customer_name']) ?> · <?= e($r['phone']) ?> · <?= e(
     $r['email'],
 ) ?></p><p class="mt-2 text-sm text-stone-500"><?= e($r['order_type']) ?> · <?= e(
     $r['address'],
 ) ?></p></div><div class="text-right"><p class="text-xl font-semibold"><?= money(
    (int) $r['total'],
) ?></p><p class="mt-2 text-sm"><?= e($r['payment_method']) ?> · <?= e(
     $r['payment_status'],
 ) ?></p><span class="badge inline-block rounded-full bg-stone-100 px-3 py-1 text-xs font-medium mt-2"><?= e(
    $r['status'],
) ?></span></div></div><details class="my-4 rounded-lg bg-stone-50 p-4"><summary class="cursor-pointer text-sm">Order items & notes</summary><div class="mt-3 space-y-2"><?php foreach (
    App\Core\DB::all('SELECT * FROM order_items WHERE order_id=?', [$r['id']])
    as $i
): ?><p class="text-sm"><?= e($i['name']) ?> × <?= $i['quantity'] ?> — <?= money(
     $i['price'] * $i['quantity'],
 ) ?></p><?php endforeach; ?><p class="text-sm">Delivery: <?= money(
    (int) $r['delivery_fee'],
) ?></p><p class="text-sm"><?= e(
    $r['notes'] ?: 'No special notes.',
) ?></p></div></details><div class="flex flex-wrap gap-3"><?php
$transitions = [
    'Pending' => ['Confirmed', 'Cancelled'],
    'Confirmed' => ['Preparing', 'Cancelled'],
    'Preparing' => ['Delivered', 'Cancelled'],
];
foreach (
    $transitions[$r['status']] ?? []
    as $next
): ?><form method="post" action="/control-center/orders/status" data-confirm="Change this order to <?= e(
    $next,
) ?>?"><?= csrf() ?><input type="hidden" name="detail" value="1"><input type="hidden" name="id" value="<?= $r[
    'id'
] ?>"><input type="hidden" name="status" value="<?= e($next) ?>"><button class="btn-dark inline-flex items-center justify-center rounded-full px-6 py-3 text-sm font-semibold text-white transition disabled:opacity-40 bg-ink hover:bg-stone-700"><?= e(
    $next,
) ?></button></form><?php endforeach;
if (
    $r['payment_method'] === 'cod' &&
    $r['payment_status'] === 'unpaid' &&
    $r['status'] === 'Delivered'
): ?><form method="post" action="/control-center/cash/status" data-confirm="Confirm that cash was actually received?"><?= csrf() ?><input type="hidden" name="detail" value="1"><input type="hidden" name="id" value="<?= $r[
    'id'
] ?>"><button class="btn inline-flex items-center justify-center rounded-full bg-accent px-6 py-3 text-sm font-semibold text-white transition hover:bg-orange-800 disabled:opacity-40">Record cash collected</button></form><?php endif;
?></div>
<?php elseif (
    $section === 'reservations'
): ?><div class="flex flex-wrap justify-between gap-4"><div><h2 class="font-display [overflow-wrap:anywhere] text-2xl"><?= e(
    $r['name'],
) ?> · <?= $r['guests'] ?> guests</h2><p class="my-2"><?= e(
     str_replace('T', ' ', $r['reserved_at']),
 ) ?></p><p class="text-sm"><?= e($r['email']) ?> · <?= e(
     $r['phone'],
 ) ?></p><p class="my-3 text-sm text-stone-500"><?= e(
    $r['notes'],
) ?></p></div><span class="badge inline-block rounded-full bg-stone-100 px-3 py-1 text-xs font-medium h-fit"><?= e(
    $r['status'],
) ?></span></div><div class="mt-4 flex gap-3"><?php
$transitions = ['Pending' => ['Confirmed', 'Cancelled'], 'Confirmed' => ['Completed', 'Cancelled']];
foreach (
    $transitions[$r['status']] ?? []
    as $next
): ?><form method="post" action="/control-center/reservations/status"><?= csrf() ?><input type="hidden" name="detail" value="1"><input type="hidden" name="id" value="<?= $r[
    'id'
] ?>"><input type="hidden" name="status" value="<?= e($next) ?>"><button class="btn-dark inline-flex items-center justify-center rounded-full px-6 py-3 text-sm font-semibold text-white transition disabled:opacity-40 bg-ink hover:bg-stone-700"><?= e(
    $next,
) ?></button></form><?php endforeach;
?></div>
<?php elseif ($section === 'payments'): ?>
<h2 class="break-all text-xl font-semibold"><?= e($r['transaction_id']); ?></h2>
<dl class="mt-5 space-y-3 text-sm"><div><dt>Amount</dt><dd><?= money((int) $r['amount']); ?></dd></div><div><dt>Status</dt><dd><?= e($r['status']); ?></dd></div><div><dt>Created</dt><dd><?= e($r['created_at']); ?></dd></div><div><dt>Provider reference</dt><dd class="break-all"><?= e($r['provider_reference'] ?? '—'); ?></dd></div></dl>
<a class="mt-6 inline-block text-sm font-semibold text-emerald-900" href="/control-center/orders/<?= (int) $r['order_id']; ?>">Open order →</a>
<?php elseif (
    $section === 'messages'
): ?><div class="flex justify-between gap-5"><div><h2 class="font-display [overflow-wrap:anywhere] text-2xl"><?= e(
    $r['name'],
) ?></h2><p class="text-sm text-stone-500"><?= e(
    $r['email'],
) ?></p></div><span class="badge inline-block rounded-full bg-stone-100 px-3 py-1 text-xs font-medium h-fit"><?= e(
    $r['status'],
) ?></span></div><p class="my-5 whitespace-pre-line leading-7"><?= e(
    $r['message'],
) ?></p><form method="post" action="/control-center/messages/status" class="flex items-center gap-4"><?= csrf() ?><input type="hidden" name="detail" value="1"><input type="hidden" name="id" value="<?= $r[
    'id'
] ?>"><input type="hidden" name="status" value="<?= $r['status'] === 'Read'
    ? 'Unread'
    : 'Read' ?>"><button class="btn-dark inline-flex items-center justify-center rounded-full px-6 py-3 text-sm font-semibold text-white transition disabled:opacity-40 bg-ink hover:bg-stone-700">Mark <?= $r['status'] === 'Read'
    ? 'unread'
    : 'read' ?></button><a class="text-sm underline" href="mailto:<?= e(
    $r['email'],
) ?>">Reply by email</a></form>
<?php else: ?><div class="flex flex-wrap justify-between gap-4"><div><h2 class="font-display [overflow-wrap:anywhere] break-all text-xl"><?= e(
    $r['transaction_id'],
) ?></h2><p class="mt-2 text-sm">Order #<?= $r['order_id'] ?> · Provider reference: <?= e(
     $r['provider_reference'] ?: 'Awaiting verification',
 ) ?></p></div><p><?= money((int) $r['amount']) ?> <span class="badge inline-block rounded-full bg-stone-100 px-3 py-1 text-xs font-medium"><?= e(
     $r['status'],
 ) ?></span></p></div><?php endif; ?><p class="mt-5 text-xs text-stone-400"><?= e(
    $r['created_at'],
) ?></p></article><?php endforeach;
if (
    !$rows
): ?><div class="card rounded-2xl border border-stone-200 bg-white p-6 shadow-sm py-14 text-center text-stone-500">No records found.</div><?php endif;
?></div>
