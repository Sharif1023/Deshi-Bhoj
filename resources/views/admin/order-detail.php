<div class="mb-6 flex flex-wrap items-center justify-between gap-3"><a class="text-sm font-semibold text-emerald-900" href="/control-center/orders">← Back to orders</a><button class="rounded-full border border-stone-300 bg-white px-5 py-2.5 text-sm print:hidden" type="button" data-print>Print receipt</button></div>
<div class="mx-auto max-w-5xl space-y-6">
    <section class="rounded-2xl bg-emerald-950 p-6 text-white sm:p-8">
        <p class="text-sm text-emerald-100">Order details</p><div class="mt-3 flex flex-wrap items-center justify-between gap-4"><h2 class="text-3xl font-semibold"><?= e($o['number']); ?></h2><span class="rounded-full bg-white/10 px-4 py-2 font-medium"><?= e(App\Services\OrderService::statusLabel($o['status'], $o['order_type'])); ?></span></div>
        <p class="mt-4 text-sm text-emerald-100"><?= e($o['created_at']); ?> · <?= e(ucfirst($o['order_type'])); ?></p>
    </section>
    <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold">Customer & delivery</h3><div class="mt-4 grid gap-5 sm:grid-cols-2">
            <div class="space-y-2 text-sm" data-no-translate><p class="font-semibold"><?= e($o['customer_name']); ?></p><a class="block text-emerald-900 underline" href="tel:<?= e($o['phone']); ?>"><?= e($o['phone']); ?></a><p class="break-all"><?= e($o['email']); ?></p></div>
            <div class="text-sm leading-7"><p><?= e(ucfirst($o['order_type'])); ?></p><p class="whitespace-pre-line break-words" data-no-translate><?= e($o['address']); ?></p></div>
        </div>
        <div class="mt-5 rounded-xl bg-amber-50 p-4"><h4 class="text-sm font-semibold">Kitchen notes / allergies</h4><p class="mt-2 whitespace-pre-line break-words text-sm leading-7" data-no-translate><?= e($o['notes'] ?: 'No special notes.'); ?></p></div>
    </section>
    <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h3 class="mb-5 text-lg font-semibold">Order items</h3>
        <?php foreach ($items as $item): ?><div class="flex justify-between gap-4 border-b border-stone-100 py-4 text-sm"><div class="min-w-0 break-words"><?= e(App\Services\OrderService::itemName($item)); ?> <strong>× <?= (int) $item['quantity']; ?></strong><p class="mt-1 text-xs text-stone-500"><?= money((int) $item['price']); ?> each</p></div><strong class="shrink-0"><?= money($item['price'] * $item['quantity']); ?></strong></div><?php endforeach; ?>
        <dl class="mt-5 space-y-3 text-sm"><div class="flex justify-between"><dt>Delivery fee</dt><dd><?= money((int) $o['delivery_fee']); ?></dd></div><div class="flex justify-between text-xl font-bold"><dt>Total</dt><dd><?= money((int) $o['total']); ?></dd></div><div class="flex justify-between"><dt>Payment</dt><dd><?= e($o['payment_method'] === 'cod' ? 'Cash' : $o['payment_method']); ?> · <?= e($o['payment_status']); ?></dd></div></dl>
    </section>
    <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm print:hidden">
        <h3 class="mb-4 text-lg font-semibold">Next action</h3>
        <?php if ($o['payment_method'] !== 'cod' && $o['payment_status'] !== 'paid' && !in_array($o['status'], ['Cancelled', 'Delivered'], true)): ?><p class="mb-4 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">Online payment must be verified before fulfillment.</p><?php endif; ?>
        <div class="flex flex-wrap gap-3">
        <?php foreach (App\Services\OrderService::nextStatuses($o) as $next): $blocked = $next !== 'Cancelled' && $o['payment_method'] !== 'cod' && $o['payment_status'] !== 'paid'; ?>
            <form action="/control-center/orders/status" method="post" data-confirm="Change this order to <?= e(App\Services\OrderService::statusLabel($next, $o['order_type'])); ?>?"><?= csrf(); ?><input type="hidden" name="id" value="<?= (int) $o['id']; ?>"><input type="hidden" name="status" value="<?= e($next); ?>"><input type="hidden" name="detail" value="1"><button class="rounded-full px-5 py-3 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-40 <?= $next === 'Cancelled' ? 'border border-red-200 text-red-700' : 'bg-emerald-900 text-white hover:bg-emerald-800'; ?>" <?= $blocked ? 'disabled' : ''; ?>><?= e(App\Services\OrderService::statusLabel($next, $o['order_type'])); ?></button></form>
        <?php endforeach; ?>
        <?php if ($o['payment_method'] === 'cod' && $o['payment_status'] === 'unpaid' && $o['status'] === 'Delivered'): ?>
            <form action="/control-center/cash/status" method="post" data-confirm="Confirm that cash was actually received?"><?= csrf(); ?><input type="hidden" name="id" value="<?= (int) $o['id']; ?>"><input type="hidden" name="detail" value="1"><button class="rounded-full bg-accent px-5 py-3 text-sm font-semibold text-white">Record cash collected</button></form>
        <?php endif; ?>
        <?php if (!App\Services\OrderService::nextStatuses($o)): ?><p class="text-sm text-stone-500">This order is complete. No further status changes are available.</p><?php endif; ?>
        </div>
    </section>
    <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm"><h3 class="mb-4 text-lg font-semibold">Status history</h3><ol class="space-y-3"><?php foreach ($events as $event): ?><li class="flex flex-wrap justify-between gap-2 border-l-2 border-emerald-700 pl-4 text-sm"><span><?= e(App\Services\OrderService::statusLabel($event['status'], $o['order_type'])); ?></span><time class="text-stone-500"><?= e($event['created_at']); ?></time></li><?php endforeach; ?></ol></section>
</div>
