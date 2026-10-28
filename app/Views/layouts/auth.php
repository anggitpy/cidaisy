<!doctype html>
<html lang="en"><head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->renderSection('title') ?> · <?= esc(setting('App.siteName')) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/app.css') ?>"><script src="<?= base_url('assets/app.js') ?>" defer></script>
</head><body class="min-h-screen bg-base-200 text-base-content">
<div class="flex justify-end p-4"><button type="button" class="btn btn-ghost btn-sm" data-theme-toggle>Change theme</button></div>
<main class="mx-auto flex min-h-[80vh] max-w-md flex-col justify-center px-5 py-10">
    <div class="mb-7 text-center"><span class="badge badge-primary badge-outline mb-3">ADMIN WORKSPACE</span><h1 class="text-3xl font-bold tracking-tight"><?= esc(setting('App.siteName')) ?></h1><p class="mt-2 text-sm text-base-content/60"><?= esc(setting('App.siteDescription')) ?></p></div>
    <div class="card border border-base-300 bg-base-100 shadow-sm"><div class="card-body gap-5">
        <?= $this->include('components/alerts') ?>
        <?= $this->renderSection('main') ?>
    </div></div>
    <p class="mt-6 text-center text-xs text-base-content/50">A dependable place to start.</p>
</main></body></html>
