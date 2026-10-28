<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Access denied</title>
        <link rel="stylesheet" href="<?= base_url('assets/app.css') ?>">
    </head>
    <body class="grid min-h-screen place-items-center bg-base-200 p-6">
        <main class="card max-w-md bg-base-100 shadow">
            <div class="card-body">
                <span class="badge badge-error">403</span>
                <h1 class="card-title">Access denied</h1>
                <p>You do not have permission to perform this action.</p>
                <a class="btn btn-primary mt-4" href="<?= site_url('admin') ?>">Back to dashboard</a>
            </div>
        </main>
    </body>
</html>
