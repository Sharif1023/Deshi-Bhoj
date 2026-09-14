<section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 sm:py-20">
    <p class="text-xs font-semibold uppercase tracking-[.22em] text-accent">অর্ডারের খবর</p><h1 class="my-5 text-4xl font-semibold text-emerald-950 sm:text-5xl">আপনার অর্ডার ট্র্যাক করুন</h1>
    <p class="mb-7 text-sm leading-7 text-stone-500">অর্ডার নম্বর এবং অর্ডারের সময় দেওয়া মোবাইল নম্বর লিখুন।</p>
    <?php if ($error): ?><p role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><?= e($error); ?></p><?php endif; ?>
    <form action="/track-order" method="post" class="space-y-5 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <?= csrf(); ?>
        <label class="block text-sm font-medium">অর্ডার নম্বর<input class="mt-2 w-full rounded-xl border border-stone-300 p-3 text-sm uppercase" name="number" maxlength="40" placeholder="KJ-1234567890" value="<?= e(is_string($_POST['number'] ?? null) ? $_POST['number'] : ''); ?>" required autocapitalize="characters" autocomplete="off"></label>
        <label class="block text-sm font-medium">মোবাইল নম্বর<input class="mt-2 w-full rounded-xl border border-stone-300 p-3 text-sm" name="phone" type="tel" maxlength="20" placeholder="01712345678" required autocomplete="tel"></label>
        <button class="w-full rounded-full bg-emerald-900 px-6 py-3.5 text-sm font-semibold text-white hover:bg-emerald-800">অর্ডার ট্র্যাক করুন →</button>
    </form>
    <?php if (!empty($_SESSION['recent_orders'])): ?><section class="mt-8"><h2 class="mb-4 text-lg font-semibold">এই ডিভাইসের সাম্প্রতিক অর্ডার</h2><div class="space-y-2"><?php foreach ($_SESSION['recent_orders'] as $token => $number): ?><a class="flex justify-between rounded-xl border border-stone-200 bg-white px-5 py-4 text-sm font-semibold text-emerald-900" href="/order/<?= e($token); ?>"><span><?= e($number); ?></span><span>দেখুন →</span></a><?php endforeach; ?></div></section><?php endif; ?>
</section>
