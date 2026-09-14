<?php if ($mode === 'list'): ?>
<div class="mb-6 flex justify-end"><a class="rounded-full bg-emerald-900 px-6 py-3 text-sm font-semibold text-white" href="/control-center/team/new">+ Add team member</a></div>
<div class="grid gap-4 md:grid-cols-2">
<?php foreach ($rows as $r): ?><article class="rounded-2xl border border-stone-200 bg-white p-6"><h2 class="text-lg font-semibold" data-no-translate><?= e($r['name']); ?></h2><p class="my-2 break-all text-sm text-stone-500" data-no-translate><?= e($r['email']); ?></p><p class="text-sm"><?= e(ucfirst($r['role'])); ?></p>
<?php if ((int) $r['id'] === (int) user()['id']): ?><p class="mt-4 text-sm text-emerald-800">Your account</p><?php else: ?><div class="mt-5 flex justify-between"><a class="text-sm font-semibold text-emerald-900" href="/control-center/team/<?= (int) $r['id']; ?>/edit">Edit member →</a><form method="post" action="/control-center/team" data-confirm="Remove this staff account?"><?= csrf(); ?><input type="hidden" name="id" value="<?= (int) $r['id']; ?>"><input type="hidden" name="action" value="delete"><button class="text-sm text-red-700">Remove</button></form></div><?php endif; ?></article><?php endforeach; ?>
</div>
<?php else: ?>
<a class="mb-6 inline-block text-sm font-semibold text-emerald-900" href="/control-center/team">← Back to team</a>
<form class="mx-auto max-w-3xl space-y-5 rounded-2xl border border-stone-200 bg-white p-6 sm:p-8" action="/control-center/team" method="post">
    <h2 class="text-2xl font-semibold"><?= $edit ? 'Edit team member' : 'Add team member'; ?></h2><?= csrf(); ?><input type="hidden" name="action" value="<?= $edit ? 'update' : 'create'; ?>"><input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0); ?>">
    <label class="block text-sm font-semibold">Name<input class="mt-2 w-full rounded-xl border border-stone-300 p-3 text-sm" name="name" value="<?= e($edit['name'] ?? ''); ?>" required maxlength="100"></label>
    <label class="block text-sm font-semibold">Email<input class="mt-2 w-full rounded-xl border border-stone-300 p-3 text-sm" name="email" type="email" value="<?= e($edit['email'] ?? ''); ?>" required></label>
    <?php if (!$edit): ?><label class="block text-sm font-semibold">Password<input class="mt-2 w-full rounded-xl border border-stone-300 p-3 text-sm" name="password" type="password" required minlength="12" autocomplete="new-password"></label><?php endif; ?>
    <label class="block text-sm font-semibold">Role<select class="mt-2 w-full rounded-xl border border-stone-300 bg-white p-3 text-sm" name="role"><option value="manager" <?= ($edit['role'] ?? '') === 'manager' ? 'selected' : ''; ?>>Manager</option><option value="owner" <?= ($edit['role'] ?? '') === 'owner' ? 'selected' : ''; ?>>Owner</option></select></label>
    <button class="rounded-full bg-emerald-900 px-7 py-3 text-sm font-semibold text-white">Save changes</button>
</form>
<?php endif; ?>
