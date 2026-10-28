<?php

/**
 * @var \CodeIgniter\View\View $this
 * @var string|null $title Optional page title.
 * @var string|null $subtitle Optional supporting text.
 */
?>

<div>
    <div class="breadcrumbs mb-2 text-xs text-base-content/50">
        <ul>
            <li>
                <a href="<?= site_url('admin') ?>">Workspace</a>
            </li>
            <li>
                <?= esc($title ?? 'Overview') ?>
            </li>
        </ul>
    </div>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">
                <?= esc($title ?? 'Overview') ?>
            </h1>
            <p class="mt-2 text-sm text-base-content/60">
                <?= esc($subtitle ?? 'Manage your application from one place.') ?>
            </p>
        </div>
        <?= $this->renderSection('actions') ?>
    </div>
</div>
