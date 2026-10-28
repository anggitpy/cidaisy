<?php

/**
 * @var \CodeIgniter\View\View $this
 * @var string|null $title Optional page title.
 */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Admin') ?> · <?= esc(setting('App.siteName')) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/app.css') ?>">
    <script src="<?= base_url('assets/app.js') ?>" defer></script>
</head>
<body class="min-h-screen bg-base-200 text-base-content">
<a href="#main" class="btn btn-primary fixed -top-20 left-4 z-50 focus:top-4">Skip to content</a>
<div class="drawer lg:drawer-open">
    <input id="admin-drawer" type="checkbox" class="drawer-toggle">
    <div class="drawer-content flex min-h-screen min-w-0 flex-col">
        <?= $this->include('components/navbar') ?>
        <main id="main" class="mx-auto w-full max-w-7xl flex-1 space-y-6 p-4 md:p-8" tabindex="-1">
            <?= $this->include('components/page_title') ?>
            <?= $this->include('components/alerts') ?>
            <?= $this->renderSection('content') ?>
        </main>
        <?= $this->include('components/footer') ?>
    </div>
    <?= $this->include('components/sidebar') ?>
</div>
<dialog id="confirm-dialog" class="modal">
    <div class="modal-box"><h2 class="text-lg font-bold">Please confirm</h2><p class="py-4" data-confirm-message></p>
        <div class="modal-action"><form method="dialog"><button class="btn">Cancel</button></form><button type="button" class="btn btn-error" data-confirm-accept>Confirm</button></div>
    </div>
</dialog>
</body></html>
