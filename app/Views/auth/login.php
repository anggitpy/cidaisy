<?php

/**
 * @var \CodeIgniter\View\View $this
 */
?>

<?= $this->extend('layouts/auth') ?>
<?= $this->section('title') ?>Sign in<?= $this->endSection() ?>
<?= $this->section('main') ?>
<h2 class="card-title">Welcome back</h2>
<p class="text-sm text-base-content/60">Sign in to your account to continue.</p>
<form action="<?= url_to('login') ?>" method="post" class="space-y-5">
    <?= csrf_field() ?>
    <label class="field">
        <span class="field-label">Email address</span>
        <input class="input w-full" type="email" name="email" value="<?= old('email') ?>" autocomplete="email" required
            autofocus>
    </label>
    <label class="field">
        <span class="field-label">Password</span>
        <input class="input w-full" type="password" name="password" autocomplete="current-password" required>
    </label>
    <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
        <label class="flex items-center gap-2">
            <input class="checkbox checkbox-sm" type="checkbox" name="remember" value="1">Remember me
        </label>
        <a class="link link-primary" href="<?= url_to('magic-link') ?>">Forgot password?</a>
    </div>
    <button class="btn btn-primary w-full" type="submit">Sign in</button>
</form>
<p class="text-xs text-base-content/50">Accounts are provided by your administrator.</p>
<?= $this->endSection() ?>
