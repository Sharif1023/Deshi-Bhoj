<section class="section mx-auto px-5 py-16 lg:px-10 max-w-lg"><form class="card rounded-2xl border border-stone-200 bg-white p-6 shadow-sm space-y-5" method="post" action="/reset-password"><h1 class="font-display [overflow-wrap:anywhere] text-3xl">Choose a new password</h1><?= csrf() ?><input type="hidden" name="token" value="<?= e(
    $_GET['token'] ?? '',
) ?>"><input type="hidden" name="email" value="<?= e(
    $_GET['email'] ?? '',
) ?>"><label class="block text-sm font-medium">New password<input class="w-full rounded-lg border border-stone-300 bg-white p-3 text-sm text-ink [&[type=checkbox]]:w-auto [&[type=radio]]:w-auto" name="password" type="password" required minlength="12" autocomplete="new-password"></label><button class="btn-dark inline-flex items-center justify-center rounded-full px-6 py-3 text-sm font-semibold text-white transition disabled:opacity-40 bg-ink hover:bg-stone-700">Reset password</button></form></section>
