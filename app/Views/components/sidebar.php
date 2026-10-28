<aside class="drawer-side z-30">
    <label for="admin-drawer" class="drawer-overlay" aria-label="Close navigation"></label>
    <div class="flex min-h-full w-64 flex-col border-r border-base-300 bg-base-100 p-5">
        <a href="<?= site_url('admin') ?>" class="mb-1 flex items-center gap-3 px-3 py-4 text-xl font-bold">
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-primary text-sm text-primary-content">CI</span>
            <?= esc(setting('App.siteName')) ?>
        </a>
        <p class="mb-8 px-3 text-xs text-base-content/50">APPLICATION ADMINISTRATION</p>
        <nav aria-label="Main navigation">
            <ul class="menu w-full gap-1">

                <?php $items = [['admin', 'Overview', 'dashboard.access'], ['admin/users', 'Users', 'users.view'], ['admin/settings', 'Settings', 'settings.view'], ['admin/profile', 'My profile', 'profile.edit']]; ?>

                <?php foreach ($items as [$path, $label, $permission]): ?>

                    <?php if (auth()->user()->can($permission)): ?>

                        <li>
                            <a href="<?= site_url($path) ?>" <?= trim(uri_string(), '/') === $path ? 'class="menu-active" aria-current="page"' : '' ?>>
                                <?= esc($label) ?>
                            </a>
                        </li>

                    <?php endif ?>

                <?php endforeach ?>

            </ul>
        </nav>
        <div class="mt-auto rounded-box bg-base-200 p-4 text-xs leading-relaxed text-base-content/60">
            <strong class="block text-base-content">Built to grow</strong>Add your next module using the shared layout
            and permissions.
        </div>
    </div>
</aside>
