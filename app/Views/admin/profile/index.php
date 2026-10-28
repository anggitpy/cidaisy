<?php

/**
 * @var \CodeIgniter\View\View $this
 * @var \CodeIgniter\Shield\Entities\User $user
 */
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php if ($user->requiresPasswordReset() || session()->getTempdata('magicLogin')): ?><div class="alert alert-warning" role="alert">Choose a new password below to finish securing your account.</div><?php endif ?>
<div class="grid items-start gap-6 xl:grid-cols-2">
<section class="card border border-base-300 bg-base-100"><div class="card-body"><h2 class="card-title">Personal information</h2><p class="text-sm text-base-content/60">Update the details used to identify your account.</p>
<form method="post" action="<?= site_url('admin/profile') ?>" class="mt-3 space-y-5"><?= csrf_field() ?>
<label class="field"><span class="field-label">Username</span><input class="input w-full" name="username" value="<?= esc($user->username) ?>" minlength="3" maxlength="30" required></label>
<label class="field"><span class="field-label">Email</span><input class="input w-full" type="email" name="email" value="<?= esc($user->email) ?>" required></label>
<label class="field"><span class="field-label">Current password</span><input class="input w-full" type="password" name="current_password" autocomplete="current-password" required><span class="field-help">Required to change your account details.</span></label>
<button class="btn btn-primary">Save profile</button></form></div></section>
<section class="card border border-base-300 bg-base-100"><div class="card-body"><h2 class="card-title">Change password</h2><p class="text-sm text-base-content/60">Use a unique password with at least 12 characters.</p>
<form method="post" action="<?= site_url('admin/profile/password') ?>" class="mt-3 space-y-5"><?= csrf_field() ?>
<?php if (! session()->getTempdata('magicLogin')): ?><label class="field"><span class="field-label">Current password</span><input class="input w-full" type="password" name="current_password" autocomplete="current-password" required></label><?php endif ?>
<label class="field"><span class="field-label">New password</span><input class="input w-full" type="password" name="password" minlength="12" autocomplete="new-password" required></label>
<label class="field"><span class="field-label">Confirm new password</span><input class="input w-full" type="password" name="password_confirm" autocomplete="new-password" required></label>
<button class="btn btn-primary">Change password</button><p class="field-help">You will be asked to sign in again. Remember-me tokens are revoked.</p></form></div></section>
</div>
<?= $this->endSection() ?>
