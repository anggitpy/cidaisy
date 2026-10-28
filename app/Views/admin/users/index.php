<?php

/**
 * @var \CodeIgniter\View\View $this
 * @var \CodeIgniter\Shield\Entities\User[] $users
 * @var \CodeIgniter\Pager\Pager $pager
 * @var string $search
 */
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('actions') ?><?php if (auth()->user()->can('users.create')): ?><a class="btn btn-primary" href="<?= site_url('admin/users/new') ?>">+ Create user</a><?php endif ?><?= $this->endSection() ?>
<?= $this->section('content') ?>
<section class="card border border-base-300 bg-base-100"><div class="card-body">
<form method="get" class="flex flex-wrap gap-3"><label class="input flex-1"><span class="sr-only">Search users</span><input type="search" name="q" placeholder="Search email or username…" value="<?= esc($search) ?>" maxlength="100"></label><button class="btn">Search</button><?php if ($search): ?><a class="btn btn-ghost" href="<?= site_url('admin/users') ?>">Clear</a><?php endif ?></form>
<div class="mt-3 overflow-x-auto"><table class="table"><thead><tr><th>User</th><th>Groups</th><th>Status</th><th class="text-right">Actions</th></tr></thead><tbody>
<?php foreach ($users as $user): ?><tr><td><a class="font-semibold hover:underline" href="<?= site_url('admin/users/' . $user->id) ?>"><?= esc($user->username) ?></a><div class="text-xs text-base-content/60"><?= esc($user->email) ?></div></td><td><?php foreach ($user->getGroups() as $group): ?><span class="badge badge-sm badge-outline mr-1"><?= esc($group) ?></span><?php endforeach ?></td><td><span class="badge badge-sm <?= $user->active ? 'badge-success' : 'badge-ghost' ?>"><?= $user->active ? 'Active' : 'Inactive' ?></span></td><td class="text-right"><a class="btn btn-ghost btn-sm" href="<?= site_url('admin/users/' . $user->id) ?>">View →</a></td></tr><?php endforeach ?>
<?php if ($users === []): ?><tr><td colspan="4" class="py-12 text-center text-base-content/60">No users found.</td></tr><?php endif ?>
</tbody></table></div><?= $pager->only(['q'])->links() ?>
</div></section>
<?= $this->endSection() ?>
