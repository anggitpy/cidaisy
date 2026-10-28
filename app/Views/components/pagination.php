<?php

/**
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 */
?>

<?php $pager->setSurroundCount(2); ?>

<nav class="join mt-5" aria-label="Pagination">

    <?php if ($pager->hasPrevious()): ?>
        <a class="btn join-item btn-sm" href="<?= $pager->getFirst() ?>">First</a>
        <a class="btn join-item btn-sm" href="<?= $pager->getPrevious() ?>">Previous</a>
    <?php endif ?>

    <?php foreach ($pager->links() as $link): ?>
        <a class="btn join-item btn-sm <?= $link['active'] ? 'btn-primary' : '' ?>" href="<?= $link['uri'] ?>" <?= $link['active'] ? 'aria-current="page"' : '' ?>>
            <?= esc((string) $link['title']) ?>
        </a>
    <?php endforeach ?>

    <?php if ($pager->hasNext()): ?>
        <a class="btn join-item btn-sm" href="<?= $pager->getNext() ?>">Next</a>
        <a class="btn join-item btn-sm" href="<?= $pager->getLast() ?>">Last</a>
    <?php endif ?>

</nav>
