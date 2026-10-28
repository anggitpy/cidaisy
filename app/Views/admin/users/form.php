<?php

/**
 * @var \CodeIgniter\View\View $this
 * @var \CodeIgniter\Shield\Entities\User|null $user
 * @var array<string, array{title: string, description: string}> $groups
 */
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<section class="card max-w-3xl border border-base-300 bg-base-100"><div class="card-body">
<form method="post" action="<?= $user ? site_url('admin/users/' . $user->id) : site_url('admin/users') ?>" class="space-y-6">
<?= csrf_field() ?>
<div class="grid gap-5 sm:grid-cols-2"><label class="field"><span class="field-label">Username</span><input class="input w-full" name="username" value="<?= old('username', $user?->username ?? '') ?>" minlength="3" maxlength="30" pattern="[a-zA-Z0-9.]+" autocomplete="off" required><span class="field-help">Letters, numbers, and periods; 3–30 characters.</span></label><label class="field"><span class="field-label">Email</span><input class="input w-full" type="email" name="email" value="<?= old('email', $user?->email ?? '') ?>" maxlength="254" required></label></div>
<?php if (! $user): ?><div class="grid gap-5 sm:grid-cols-2"><label class="field"><span class="field-label">Temporary password</span><input class="input w-full" type="password" name="password" minlength="12" autocomplete="new-password" required><span class="field-help">At least 12 characters. A change is required at first login.</span></label><label class="field"><span class="field-label">Confirm password</span><input class="input w-full" type="password" name="password_confirm" autocomplete="new-password" required></label></div><?php endif ?>
<fieldset class="rounded-box border border-base-300 p-4"><legend class="px-2 text-sm font-semibold">Groups</legend><div class="grid gap-4 sm:grid-cols-2">
<?php $selected = old('groups', $user?->getGroups() ?? ['user'], false); ?>
<?php foreach ($groups as $key => $group): ?><label class="flex items-start gap-3"><input class="checkbox checkbox-sm mt-1" type="checkbox" name="groups[]" value="<?= esc($key) ?>" <?= in_array($key, (array) $selected, true) ? 'checked' : '' ?>><span><span class="block text-sm font-medium"><?= esc($group['title']) ?></span><span class="text-xs text-base-content/60"><?= esc($group['description']) ?></span></span></label><?php endforeach ?>
</div></fieldset><p class="field-help">Choose at least one group. Only superadmins can assign privileged groups.</p>
<div class="flex gap-3"><button type="submit" class="btn btn-primary">Save user</button><a class="btn btn-ghost" href="<?= site_url('admin/users') ?>">Cancel</a></div>
</form></div></section>
<?= $this->endSection() ?>
