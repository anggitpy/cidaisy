# CI Daisy developer guide

This is a traditional CodeIgniter 4 application. Keep new modules small: an explicit route, a controller, a model when persistence needs one, and PHP views. Shield owns users, identities, groups, and permissions. Settings owns persistent configuration. Never modify vendor files.

## Local setup

Requirements: PHP 8.2+, Composer, MariaDB/MySQL, Node.js/npm for CSS, and a web server. Run `composer check-platform-reqs` for the installed dependencies. PHP needs intl, mbstring, MySQLi, and the extensions required by Composer. Git is recommended.

```sh
composer install
npm ci
```

Copy [.env.example](.env.example) to `.env`, supply your local database credentials, and create `cidaisy` with utf8mb4. Create a separate `cidaisy_test` database for automated tests. Do not reuse application data for tests. The local `.env` is ignored; never commit credentials.

```sh
php spark migrate --all
php spark db:seed InitialSetup
npm run build
php spark serve --host 127.0.0.1 --port 8080
```

Open http://localhost:8080. Change `app.baseURL` if using another host or port. Database settings are environment-specific; the reusable example contains no database username or password.

### First superadmin

Temporarily set these keys in your ignored `.env` using your own values:

```ini
setup.username = 'your.name'
setup.email = 'you@example.com'
setup.password = 'your own unique strong password'
```

Run `php spark app:superadmin`, then **remove all three setup keys**. The command refuses to run if a superadmin already exists. Passwords must pass Shield's validators and contain at least 12 characters. There is no seeded administrator or default password. Subsequent account administration is available in the UI or through `php spark help shield:user`.

### Mail and password recovery

Configure `email.protocol`, SMTP host, port, authentication, encryption, and a valid sender in `.env`. Use a local SMTP catcher for development. Shield sends short-lived, single-use magic links in place of a separate password-reset token system. After verifying a link, the user lands on their profile and can choose a new password without entering the forgotten one. Ordinary password changes require the current password. Changing profile identifiers also requires the current password.

New accounts receive an administrator-chosen temporary password and must replace it at first login. Share temporary credentials through an appropriate private channel. The admin reset action emails a link to Shield's recovery request form and requires a password change after the next login. It never emails a password. If sending fails, the UI reports failure and does not mark the account for reset. Configure SMTP before relying on recovery.

## 1. Add a controller and route

Create `app/Controllers/Admin/Reports.php`:

```php
<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;

class Reports extends BaseController
{
    public function index(): string
    {
        return view('admin/reports/index', ['title' => 'Reports']);
    }
}
```

Inside the existing `admin` route group in [Routes.php](app/Config/Routes.php):

```php
$routes->get('reports', 'Reports::index', ['filter' => 'admin-access:reports.view']);
```

The group applies Shield's session filter. `admin-access` checks account status, the requested permission, and required password changes. Auto-routing is disabled. State changes require POST routes, server-side validation, and a CSRF field.

## 2. Create an admin page

Create `app/Views/admin/reports/index.php`:

```php
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<section class="card bg-base-100 border border-base-300">
    <div class="card-body">
        <h2 class="card-title">Reports</h2>
        <p>Your report content.</p>
    </div>
</section>
<?= $this->endSection() ?>
```

The layout includes navigation, breadcrumbs, title, messages, footer, and confirmation dialog. Pass `subtitle` alongside `title` to customize supporting text.

## 3. Add a sidebar item

Add an entry to `$items` in [sidebar.php](app/Views/components/sidebar.php):

```php
['admin/reports', 'Reports', 'reports.view'],
```

The navigation checks `$user->can()`. This only controls presentation: always enforce the same permission in the route or controller.

## 4. Add a permission

In [AuthGroups.php](app/Config/AuthGroups.php), add to `$permissions`:

```php
'reports.view' => 'View reports',
```

Controllers can also check permissions explicitly:

```php
if (! auth()->user()->can('reports.view')) {
    return $this->response->setStatusCode(403);
}
```

Views may use `<?php if (auth()->user()->can('reports.view')): ?>` around optional elements. Escape user-controlled text with `esc()` and attribute values with `esc($value, 'attr')` where appropriate.

## 5. Assign permissions to groups

Add the new permission to the appropriate entries in `$matrix`. There is no implicit superadmin bypass; grant new module permissions to superadmin as well.

| Group | Dashboard | Users | Settings | Own profile | Beta |
|---|---|---|---|---|---|
| superadmin | Yes | View/create/edit/delete | View/edit | Edit | Yes |
| admin | Yes | View/create/edit/delete standard/beta accounts | View/edit | Edit | No |
| developer | Yes | No | View | Edit | Yes |
| user | Yes | No | No | Edit | No |
| beta | Yes | No | No | Edit | Yes |

Only superadmins can assign admin, developer, and superadmin groups. Ordinary admins cannot modify privileged accounts or accounts with direct permissions. No one can delete/deactivate their own account or change their own groups through the UI. Use Shield CLI administration if a superadmin must deliberately change their own group. These conservative rules prevent removal of the last operational superadmin through this interface.

```php
$user = auth()->getProvider()->findById($id);
$user->syncGroups('user', 'beta');
```

Do not accept arbitrary groups or permissions directly from request data.

## 6. Add a model and migration

```sh
php spark make:migration CreateProjects
php spark make:model ProjectModel --return entity
php spark migrate --all
```

Use Forge in the generated migration:

```php
$this->forge->addField([
    'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
    'name' => ['type' => 'VARCHAR', 'constraint' => 150],
]);
$this->forge->addKey('id', true);
$this->forge->createTable('projects', true, ['ENGINE' => 'InnoDB']);
```

Add a matching `down()` using `$this->forge->dropTable('projects', true)`. Set the model's table, allowed fields, and validation rules. Prefer models directly over repository abstractions. Use transactions when multiple records must change together.

## 7. Add an application setting

Declare a public, typed default in [App.php](app/Config/App.php):

```php
public int $itemsPerPage = 20;
```

Read it in a controller or view with `setting('App.itemsPerPage')`. Persist a validated value using:

```php
service('settings')->set('App.itemsPerPage', 30);
```

Add the field and validation to the settings controller/view if administrators should edit it. Never accept arbitrary setting keys from a request. Keep database credentials, mail secrets, and deployment configuration in the environment, outside the settings UI.

Maintenance mode returns HTTP 503 for normal access, with a Retry-After header. Authentication remains reachable, and active, unbanned users with `settings.edit` may administer the app. The timezone setting controls request-time PHP date formatting; storage uses the framework's normal database timestamp behavior.

## 8. Reuse components and layouts

Use `$this->include('components/alerts')` for flash messages and validation errors. Use the layout's `actions` section for page actions:

```php
<?= $this->section('actions') ?>
<a class="btn btn-primary" href="<?= site_url('admin/reports/new') ?>">New report</a>
<?= $this->endSection() ?>
```

Forms use daisyUI's `input`, `select`, `textarea`, `checkbox`, `toggle`, and `btn` classes. Include `csrf_field()` in every POST form. Add `data-confirm="Delete this report?"` to a destructive form to use the shared native HTML dialog. JavaScript progressively enhances confirmation and theme selection. Authorization and CSRF remain server-side.

## 9. Rebuild frontend assets

```sh
npm run dev
npm run build
```

Development watches [resources/css/app.css](resources/css/app.css) and PHP views. Production minifies into [public/assets/app.css](public/assets/app.css). Tailwind v4 sources are declared explicitly with `@source`; add a source if a future module stores class names elsewhere. Use complete class names rather than building class names from string fragments. npm is used only to compile CSS. No frontend framework or bundler is involved.

## 10. Deploy

1. Set the web root to the absolute `public` directory. Never expose the project root, `.env`, app, vendor, or writable directories.
2. Configure Nginx/PHP-FPM or Apache with URL rewriting. A sample [Nginx configuration](deploy/nginx.conf.example) is included. Laragon must likewise use `public` as the site's document root.
3. Run `composer install --no-dev --optimize-autoloader`. Build CSS in CI with `npm ci` and `npm run build`, then deploy the compiled asset. Node.js is not required on the production server.
4. Set `CI_ENVIRONMENT=production`, the correct HTTPS `app.baseURL`, `app.forceGlobalSecureRequests=true`, and `cookie.secure=true`. Configure a dedicated database user and real SMTP credentials. Keep secrets outside version control.
5. Allow the PHP process to write only to `writable`. Back up the database, run `php spark migrate --all`, and initialize defaults with `php spark db:seed InitialSetup` on first deployment.
6. Create the initial superadmin securely, remove setup credentials, and verify login, permissions, email delivery, and HTTPS.

Do not use `spark serve` in production. Keep dependency lockfiles, review dependency updates, back up data, and ensure database/session storage matches your deployment topology. PHP CLI and web-server PHP may load different INI files; verify required extensions for both.

## Verification

```sh
composer check-platform-reqs
php vendor/phpunit/phpunit/phpunit --no-coverage
npm run build
php spark routes
```

The feature suite uses the separate `database.tests` connection and refreshes Shield/Settings tables. It exercises login/logout, permissions, user CRUD, escalation guards, account status, settings, maintenance, password changes, and CSRF. Test passwords are fixture-only credentials; no account is seeded into the development database by these tests. Browser checks and actual SMTP delivery are separate from the feature suite.

## Official references

- [CodeIgniter user guide](https://codeigniter.com/user_guide/)
- [Shield installation](https://shield.codeigniter.com/latest/getting_started/install/)
- [Shield magic-link login](https://shield.codeigniter.com/latest/references/magic_link_login/)
- [CodeIgniter Settings](https://github.com/codeigniter4/settings)
- [Tailwind CLI](https://tailwindcss.com/docs/installation/tailwind-cli)
- [daisyUI CLI setup](https://daisyui.com/docs/install/cli/)
