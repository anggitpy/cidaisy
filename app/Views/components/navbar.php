<header class="navbar border-b border-base-300 bg-base-100 px-4 md:px-8">
    <div class="flex-1 gap-3"><label for="admin-drawer" class="btn btn-ghost btn-square lg:hidden" aria-label="Open navigation">☰</label><span class="text-sm text-base-content/60">Your workspace, organized.</span></div>
    <div class="flex-none gap-2"><button type="button" class="btn btn-ghost btn-sm" data-theme-toggle>Change theme</button>
        <details class="dropdown dropdown-end"><summary class="btn btn-ghost btn-sm"><?= esc(auth()->user()->username) ?> ▾</summary>
            <ul class="menu dropdown-content z-20 mt-3 w-52 rounded-box border border-base-300 bg-base-100 p-2 shadow-lg">
                <li><a href="<?= site_url('admin/profile') ?>">My profile</a></li>
                <li><form method="post" action="<?= url_to('logout') ?>"><?= csrf_field() ?><button type="submit" class="w-full text-left">Sign out</button></form></li>
            </ul>
        </details>
    </div>
</header>
