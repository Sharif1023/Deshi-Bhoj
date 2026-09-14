<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-5"><?php foreach (
    $stats
    as $label => $value
): ?><div class="card rounded-2xl border border-stone-200 bg-white p-6 shadow-sm"><p class="text-xs uppercase tracking-wider text-stone-500"><?= e(
    $label,
) ?></p><p class="mt-5 font-display text-3xl"><?= e(
    $value,
) ?></p><p class="mt-3 text-xs text-stone-400">From your restaurant records</p></div><?php endforeach; ?></div><div class="mt-8 grid items-start gap-6 xl:grid-cols-[2fr_1fr]"><section class="card rounded-2xl border border-stone-200 bg-white p-6 shadow-sm overflow-x-auto"><div class="mb-5 flex justify-between"><h2 class="font-display [overflow-wrap:anywhere] text-2xl">Recent orders</h2><a class="text-sm text-accent" href="/control-center/orders">View all →</a></div><table class="w-full text-left text-sm"><thead><tr><th class="border-b px-4 py-3 text-xs uppercase tracking-wide text-stone-500">Order</th><th class="border-b px-4 py-3 text-xs uppercase tracking-wide text-stone-500">Guest</th><th class="border-b px-4 py-3 text-xs uppercase tracking-wide text-stone-500">Total</th><th class="border-b px-4 py-3 text-xs uppercase tracking-wide text-stone-500">Status</th></tr></thead><tbody><?php foreach (
    $orders
    as $o
): ?><tr><td class="border-b border-stone-100 px-4 py-4"><?= e($o['number']) ?></td><td class="border-b border-stone-100 px-4 py-4"><?= e($o['customer_name']) ?></td><td class="border-b border-stone-100 px-4 py-4"><?= money(
    (int) $o['total'],
) ?></td><td class="border-b border-stone-100 px-4 py-4"><span class="badge inline-block rounded-full bg-stone-100 px-3 py-1 text-xs font-medium"><?= e(
    $o['status'],
) ?></span></td></tr><?php endforeach; ?></tbody></table><?php if (
    !$orders
): ?><p class="py-12 text-center text-stone-500">Your first order will appear here.</p><?php endif; ?></section><section class="card rounded-2xl border border-stone-200 bg-white p-6 shadow-sm"><h2 class="font-display [overflow-wrap:anywhere] mb-5 text-2xl">Recent activity</h2><?php foreach (
    $activities
    as $a
): ?><p class="border-b py-3 text-sm"><?= e(
    $a['action'],
) ?><small class="mt-1 block text-stone-400"><?= e(
    $a['created_at'],
) ?></small></p><?php endforeach; ?></section></div>
