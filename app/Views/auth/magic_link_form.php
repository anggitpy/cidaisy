<?= $this->extend('layouts/auth') ?>
<?= $this->section('title') ?>Recover access<?= $this->endSection() ?>
<?= $this->section('main') ?>
<h2 class="card-title">Recover your account</h2><p class="text-sm text-base-content/60">We will email you a secure sign-in link. Once signed in, choose a new password from your profile.</p>
<form method="post" action="<?= url_to('magic-link') ?>" class="space-y-5"><?= csrf_field() ?><label class="field"><span class="field-label">Email address</span><input class="input w-full" type="email" name="email" autocomplete="email" required></label><button class="btn btn-primary w-full">Send sign-in link</button></form>
<a href="<?= url_to('login') ?>" class="link text-sm">Back to sign in</a>
<?= $this->endSection() ?>
