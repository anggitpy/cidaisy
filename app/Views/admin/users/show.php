<?php

/**
 * @var \CodeIgniter\View\View $this
 * @var \CodeIgniter\Shield\Entities\User $user
 */
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<section class="card max-w-3xl border border-base-300 bg-base-100"><div class="card-body">
<div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="card-title text-2xl"><?= esc($user->username) ?></h2><p class="mt-1 text-base-content/60"><?= esc($user->email) ?></p></div><span class="badge <?= $user->active ? 'badge-success' : 'badge-ghost' ?>"><?= $user->active ? 'Active' : 'Inactive' ?></span></div>
<dl class="my-5 grid gap-5 sm:grid-cols-2"><div><dt class="text-xs text-base-content/50">Groups</dt><dd class="mt-1"><?= esc(implode(', ', $user->getGroups())) ?></dd></div><div><dt class="text-xs text-base-content/50">Created</dt><dd class="mt-1"><?= esc($user->created_at?->format((string) setting('App.dateFormat')) ?? '—') ?></dd></div></dl>
<?php $manageable = (int) $user->id !== (int) auth()->id() && (auth()->user()->inGroup('superadmin') || (array_diff($user->getGroups(), ['user', 'beta']) === [] && $user->getPermissions() === [])); ?>
<div class="flex flex-wrap gap-3">
<?php if ($manageable && auth()->user()->can('users.edit')): ?>
<a class="btn btn-primary" href="<?= site_url('admin/users/' . $user->id . '/edit') ?>">Edit user</a>
<form method="post" action="<?= site_url('admin/users/' . $user->id . '/status') ?>" data-confirm="Change this account's active status?"><?= csrf_field() ?><button class="btn btn-outline"><?= $user->active ? 'Deactivate' : 'Activate' ?></button></form>
<form method="post" action="<?= site_url('admin/users/' . $user->id . '/reset-password') ?>" data-confirm="Email recovery instructions and require a password change?"><?= csrf_field() ?><button class="btn btn-outline">Request password reset</button></form>
<?php endif ?>
<?php if ($manageable && auth()->user()->can('users.delete')): ?><form method="post" action="<?= site_url('admin/users/' . $user->id . '/delete') ?>" data-confirm="Delete this user? They will lose access to the application."><?= csrf_field() ?><button class="btn btn-error btn-outline">Delete user</button></form><?php endif ?>
<a class="btn btn-ghost" href="<?= site_url('admin/users') ?>">Back to users</a>
</div></div></section>
<?= $this->endSection() ?>
