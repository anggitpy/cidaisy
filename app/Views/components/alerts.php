<?php foreach (['message' => 'alert-success', 'error' => 'alert-error'] as $key => $class): ?>
    <?php if (session($key)): ?><div role="alert" class="alert <?= $class ?>"><span><?= esc(session($key)) ?></span></div><?php endif ?>
<?php endforeach ?>
<?php $errors = session('errors') ?? validation_errors(); ?>
<?php if ($errors): ?><div class="alert alert-error" role="alert"><ul class="list-inside list-disc"><?php foreach ((array) $errors as $error): ?><li><?= esc($error) ?></li><?php endforeach ?></ul></div><?php endif ?>
