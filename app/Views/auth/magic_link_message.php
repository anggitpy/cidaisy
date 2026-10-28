<?php

/**
 * @var \CodeIgniter\View\View $this
 */
?>

<?= $this->extend('layouts/auth') ?>
<?= $this->section('title') ?>Check your email<?= $this->endSection() ?>
<?= $this->section('main') ?>
<h2 class="card-title">Check your email</h2>
<p class="text-sm">Your sign-in link has been sent. Follow it to access your account, then change your password in your
    profile.</p>
<p class="text-xs text-base-content/60">The link expires and can only be used once.</p>
<a class="btn btn-primary" href="<?= url_to('login') ?>">Return to sign in</a>
<?= $this->endSection() ?>
