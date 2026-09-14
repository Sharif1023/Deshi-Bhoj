<?php $steps = App\Services\OrderService::steps($o['order_type']); $stepIndex = array_search($o['status'], $steps, true); ?>
<section class="mx-auto max-w-4xl px-4 py-10 sm:px-6 sm:py-16" data-order-tracking data-status-url="/order/<?= e($o['access_token']); ?>/status?lang=<?= e(App\Services\Locale::current()); ?>">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3"><a class="text-sm font-semibold text-emerald-900" href="/track-order">অর্ডার ট্র্যাক করুন</a><button class="rounded-full border border-stone-300 bg-white px-5 py-2.5 text-sm print:hidden" type="button" data-print>রসিদ প্রিন্ট করুন</button></div>
    <div class="rounded-2xl bg-emerald-950 p-6 text-white sm:p-8"><p class="text-sm text-emerald-100">আপনার অর্ডার</p><h1 class="mt-3 break-all text-3xl font-semibold"><?= e($o['number']); ?></h1><p class="mt-4 text-sm"><span data-no-translate><?= e($o['customer_name']); ?></span> · <?= e(ucfirst($o['order_type'])); ?></p><p class="mt-4 inline-block rounded-full bg-white/10 px-4 py-2 text-sm font-semibold" data-order-status><?= e(App\Services\OrderService::statusLabel($o['status'], $o['order_type'])); ?></p></div>
    <section class="mt-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm" aria-label="Order progress">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3"><h2 class="text-xl font-semibold">অর্ডারের বর্তমান অবস্থা</h2><a class="text-sm font-semibold text-emerald-900 underline" href="/order/<?= e($o['access_token']); ?>">রিফ্রেশ করুন</a></div>
        <p class="mb-4 rounded-xl bg-red-50 p-4 text-sm text-red-800 [&[hidden]]:hidden" data-order-cancelled <?= $o['status'] !== 'Cancelled' ? 'hidden' : ''; ?>>অর্ডারটি বাতিল করা হয়েছে।</p>
        <ol class="grid gap-3 sm:grid-cols-2">
            <?php foreach ($steps as $index => $step): $done = $stepIndex !== false && $index <= $stepIndex; ?>
                <li class="flex items-center gap-3 rounded-xl border border-stone-200 p-4 text-sm data-[done=true]:border-emerald-300 data-[done=true]:bg-emerald-50 data-[done=true]:text-emerald-950 aria-[current=step]:ring-2 aria-[current=step]:ring-emerald-700" data-order-step="<?= $index; ?>" data-step-status="<?= e($step); ?>" data-done="<?= $done ? 'true' : 'false'; ?>" <?= $index === $stepIndex ? 'aria-current="step"' : ''; ?>><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-stone-100 text-xs font-bold"><?= $index + 1; ?></span><span><?= e(App\Services\OrderService::statusLabel($step, $o['order_type'])); ?></span><time class="ml-auto text-xs text-stone-500" data-step-time><?php foreach ($events as $event) if ($event['status'] === $step) echo e(substr($event['created_at'], 11, 5)); ?></time></li>
            <?php endforeach; ?>
        </ol>
        <p class="mt-5 text-xs leading-6 text-stone-500" role="status" aria-live="polite" data-tracking-message>এই পাতা খোলা থাকলে অবস্থা স্বয়ংক্রিয়ভাবে আপডেট হবে।</p>
    </section>
    <section class="mt-6 space-y-5 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-semibold">অর্ডারের হিসাব</h2>
        <?php foreach ($items as $item): ?><p class="flex justify-between gap-4 border-b border-stone-100 pb-4 text-sm"><span class="min-w-0 break-words"><?= e(App\Services\OrderService::itemName($item)); ?> × <?= (int) $item['quantity']; ?></span><span class="shrink-0 font-semibold"><?= money($item['price'] * $item['quantity']); ?></span></p><?php endforeach; ?>
        <p class="flex justify-between text-sm"><span>ডেলিভারি চার্জ</span><span><?= money((int) $o['delivery_fee']); ?></span></p><p class="flex justify-between border-t pt-5 text-xl font-bold"><span>মোট</span><span><?= money((int) $o['total']); ?></span></p>
        <p class="text-sm">পেমেন্ট: <strong data-payment-status><?= e($o['payment_status']); ?></strong> · <?= e($o['payment_method'] === 'cod' ? 'Cash' : $o['payment_method']); ?></p>
        <?php if ($o['payment_method'] !== 'cod' && $o['payment_status'] === 'unpaid' && $o['status'] !== 'Cancelled'): ?><form method="post" action="/pay" data-order-pay><?= csrf(); ?><input type="hidden" name="access_token" value="<?= e($o['access_token']); ?>"><button class="w-full rounded-full bg-emerald-900 px-6 py-3 text-sm font-semibold text-white">পেমেন্ট করুন: <?= e($o['payment_method'] === 'bkash' ? 'bKash' : 'Nagad'); ?> →</button></form><?php endif; ?>
        <p class="text-xs leading-6 text-stone-500">এই লিংকটি ব্যক্তিগত। পরে অর্ডার নম্বর ও মোবাইল নম্বর দিয়েও ট্র্যাক করতে পারবেন।</p>
    </section>
</section>
