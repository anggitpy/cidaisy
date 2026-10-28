<?php

/**
 * @var \CodeIgniter\View\View $this
 * @var array{siteName: string, siteDescription: string, contactEmail: string, timezone: string, dateFormat: string, maintenanceMode: bool} $values
 */
?>

<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<section class="card max-w-3xl border border-base-300 bg-base-100">
    <div class="card-body">
        <h2 class="card-title">General settings</h2>
        <p class="text-sm text-base-content/60">Changes are stored using CodeIgniter Settings.</p>

        <?php $editable = auth()->user()->can('settings.edit'); ?>

        <form method="post" action="<?= site_url('admin/settings') ?>" class="mt-3 space-y-5"><?= csrf_field() ?>
            <fieldset class="space-y-5" <?= $editable ? '' : 'disabled' ?>>
                <label class="field">
                    <span class="field-label">Site name</span>
                    <input class="input w-full" name="siteName" value="<?= old('siteName', $values['siteName']) ?>" maxlength="100" required>
                </label>
                <label class="field">
                    <span class="field-label">Site description</span>
                    <textarea class="textarea w-full" name="siteDescription" maxlength="500">
<?= old('siteDescription', $values['siteDescription']) ?>
</textarea>
                </label>
                <label class="field">
                    <span class="field-label">Contact email</span>
                    <input class="input w-full" type="email" name="contactEmail" value="<?= old('contactEmail', $values['contactEmail']) ?>" required>
                </label>
                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="field">
                        <span class="field-label">Timezone</span>
                        <select class="select w-full" name="timezone">
                            <?php foreach (timezone_identifiers_list() as $zone): ?>
                                <option value="<?= esc($zone) ?>" <?= old('timezone', $values['timezone']) === $zone ? 'selected' : '' ?>>
                                    <?= esc($zone) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </label>
                    <label class="field">
                        <span class="field-label">Date format</span>
                        <select class="select w-full" name="dateFormat">
                            <?php foreach (['d M Y', 'Y-m-d', 'd/m/Y', 'm/d/Y'] as $format): ?>
                                <option value="<?= esc($format) ?>" <?= old('dateFormat', $values['dateFormat']) === $format ? 'selected' : '' ?>><?= esc($format) ?> · <?= esc(date($format)) ?></option>
                            <?php endforeach ?>
                        </select>
                    </label>
                </div>
                <label class="flex items-start gap-4 rounded-box border border-base-300 p-4">
                    <input class="toggle toggle-warning" type="checkbox" name="maintenanceMode" value="1"
                        <?= old('maintenanceMode', $values['maintenanceMode'], false) ? 'checked' : '' ?>>
                    <span>
                        <span class="block text-sm font-semibold">Maintenance mode</span>
                        <span class="field-help">Blocks normal access. Active users with settings.edit can still
                            administer the application.</span>
                    </span>
                </label>
            </fieldset>

            <?php if ($editable): ?>
                <button class="btn btn-primary">Save settings</button>
            <?php else: ?>
                <div class="alert">You have read-only access to settings.</div>
            <?php endif ?>

        </form>
    </div>
</section>
<?= $this->endSection() ?>
