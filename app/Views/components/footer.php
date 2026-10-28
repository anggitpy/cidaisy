<footer class="flex flex-wrap justify-between gap-2 border-t border-base-300 px-8 py-5 text-xs text-base-content/50">
    <span><?= esc(setting('App.siteName')) ?> · Admin workspace</span>
    <span>
        <?= esc(date((string) setting('App.dateFormat'))) ?>
    </span>
</footer>
