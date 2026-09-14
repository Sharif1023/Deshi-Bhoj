<section class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-16">
    <p class="text-xs font-semibold uppercase tracking-[.22em] text-accent">শেষ ধাপ</p><h1 class="my-5 text-4xl font-semibold text-emerald-950 sm:text-5xl">অর্ডার নিশ্চিত করুন।</h1>
    <?php if (!$cart): ?><p class="my-6 text-stone-500">আপনার কার্ট খালি।</p><a class="inline-block rounded-full bg-emerald-900 px-6 py-3 text-sm font-semibold text-white" href="/menu">মেনু দেখুন</a><?php else:
        $subtotal = array_sum(array_map(static fn($p) => $p['quantity'] * $p['price'], $cart));
        $deliveryFee = max(0, (int) setting('ordering.delivery_fee', 0));
    ?>
    <form action="/checkout" method="post" class="mt-8 grid items-start gap-6 lg:grid-cols-2">
        <?= csrf(); ?><input type="hidden" name="checkout_key" value="<?= e($_SESSION['checkout_key']); ?>">
        <div class="space-y-5 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="text-2xl font-semibold">আপনার তথ্য</h2>
            <label class="block text-sm font-medium">পুরো নাম<input class="mt-2 w-full rounded-xl border border-stone-300 p-3 text-sm" name="name" required maxlength="100" autocomplete="name"></label>
            <label class="block text-sm font-medium">ইমেইল<input class="mt-2 w-full rounded-xl border border-stone-300 p-3 text-sm" name="email" type="email" required autocomplete="email"></label>
            <label class="block text-sm font-medium">বাংলাদেশি মোবাইল নম্বর<input class="mt-2 w-full rounded-xl border border-stone-300 p-3 text-sm" name="phone" type="tel" placeholder="01712345678" required autocomplete="tel"></label>
            <fieldset><legend class="mb-3 text-sm font-medium">অর্ডারের ধরন</legend><div class="grid grid-cols-2 gap-3">
                <label class="relative cursor-pointer"><input class="peer sr-only" type="radio" name="order_type" value="pickup" checked><span class="flex h-full min-h-24 flex-col justify-center rounded-xl border border-stone-300 p-4 text-sm peer-checked:border-emerald-900 peer-checked:bg-emerald-50 peer-checked:text-emerald-950 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-emerald-800"><strong>পিকআপ</strong><span class="mt-1 text-xs">কোনো চার্জ নেই</span></span></label>
                <?php if (setting('ordering.delivery_enabled', true)): ?><label class="relative cursor-pointer"><input class="peer sr-only" type="radio" name="order_type" value="delivery"><span class="flex h-full min-h-24 flex-col justify-center rounded-xl border border-stone-300 p-4 text-sm peer-checked:border-emerald-900 peer-checked:bg-emerald-50 peer-checked:text-emerald-950 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-emerald-800"><strong>ডেলিভারি</strong><span class="mt-1 text-xs">+ <?= money($deliveryFee); ?></span></span></label><?php endif; ?>
            </div></fieldset>
            <label class="block text-sm font-medium [&[hidden]]:hidden" data-delivery-address>ডেলিভারির ঠিকানা<textarea class="mt-2 w-full rounded-xl border border-stone-300 p-3 text-sm" name="address" maxlength="1000" rows="3" autocomplete="street-address"></textarea></label>
            <label class="block text-sm font-medium">বিশেষ নির্দেশনা / অ্যালার্জি<textarea class="mt-2 w-full rounded-xl border border-stone-300 p-3 text-sm" name="notes" maxlength="2000" rows="3"></textarea></label>
        </div>
        <div class="space-y-5 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="text-2xl font-semibold">অর্ডারের হিসাব</h2>
            <?php foreach ($cart as $p): ?><p class="flex justify-between gap-4 border-b border-stone-100 pb-4 text-sm"><span class="min-w-0 break-words"><?= e(plain($p['name'])); ?> × <?= (int) $p['quantity']; ?></span><span class="shrink-0 font-semibold"><?= money($p['quantity'] * $p['price']); ?></span></p><?php endforeach; ?>
            <p class="flex justify-between text-sm"><span>ডেলিভারি চার্জ</span><span data-checkout-fee><?= money(0); ?></span></p>
            <p class="text-xs leading-6 text-stone-500">সর্বনিম্ন অর্ডারের কোনো সীমা নেই। প্রযোজ্য কর মূল্যের অন্তর্ভুক্ত।</p>
            <p class="flex justify-between border-t pt-5 text-xl"><strong>মোট</strong><strong data-checkout-total data-subtotal="<?= $subtotal; ?>" data-fee="<?= $deliveryFee; ?>"><?= money($subtotal); ?></strong></p>
            <fieldset class="space-y-3"><legend class="mb-3 font-medium">পেমেন্টের মাধ্যম</legend>
                <?php foreach (['cod' => 'পিকআপ / ডেলিভারিতে নগদ', 'bkash' => 'bKash', 'nagad' => 'Nagad'] as $value => $label): $disabled = $value !== 'cod' && !App\Services\PaymentService::enabled(); ?>
                    <label class="relative block <?= $disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'; ?>"><input class="peer sr-only" type="radio" name="payment_method" value="<?= $value; ?>" <?= $value === 'cod' ? 'checked' : ''; ?> <?= $disabled ? 'disabled' : ''; ?>><span class="block rounded-xl border border-stone-300 p-4 text-sm peer-checked:border-emerald-900 peer-checked:bg-emerald-50 peer-checked:font-semibold peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-emerald-800"><?= e($label); ?></span></label>
                <?php endforeach; ?>
            </fieldset>
            <?php if (!App\Services\PaymentService::enabled()): ?><p class="text-xs text-stone-500">অনলাইন পেমেন্ট এখন চালু নেই।</p><?php elseif (env('SSLCOMMERZ_SANDBOX', 'true') === 'true'): ?><p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-900">Sandbox payment mode — test transactions only.</p><?php endif; ?>
            <button class="w-full rounded-full bg-emerald-900 px-6 py-4 text-sm font-semibold text-white hover:bg-emerald-800">অর্ডার নিশ্চিত করুন →</button><p class="text-xs leading-6 text-stone-500">অর্ডার নম্বর ও মোবাইল নম্বর দিয়ে পরে অর্ডার ট্র্যাক করতে পারবেন।</p>
        </div>
    </form>
    <?php endif; ?>
</section>
