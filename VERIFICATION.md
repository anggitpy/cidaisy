# Implementation verification

Executed locally on 28 October 2026 using PHP 8.4.16, Node.js 24.18.0, npm 11.16.0, and MariaDB 12.0.2.

## Installed versions

| Package | Version |
|---|---|
| CodeIgniter framework/appstarter | 4.7.5 |
| Shield | 1.4.1 |
| Settings | 2.4.0 |
| Tailwind CSS / CLI | 4.3.3 |
| daisyUI | 5.7.50 |

Composer selected compatible stable versions; lockfiles preserve the resolved packages. Official documentation and installed package source were consulted. No vendor files were edited.

## Executed checks

- Official appstarter and Composer dependency installation completed.
- `composer validate --no-check-publish` passed.
- `composer check-platform-reqs` passed.
- `npm ci` passed; npm reported zero vulnerabilities during installation.
- `npm run build` passed and generated minified CSS.
- All application PHP files passed `php -l`.
- `php spark migrate --all` completed for Shield and Settings on `cidaisy`.
- `php spark migrate:status` confirmed all four package migrations are applied.
- `php spark db:seed InitialSetup` completed.
- MariaDB metadata confirmed all nine application tables use InnoDB.
- `php spark routes` confirmed explicit routes, session protection, per-route permission filters, global CSRF, and POST-only logout.
- `php vendor/phpunit/phpunit/phpunit --no-coverage` passed: **25 tests, 91 assertions**.

## Automated behavior covered

The suite includes authentication success/failure, remember-me login selection, POST logout, guest redirects, authenticated-login redirects, dashboard access, forbidden operations, read-only developer settings, user creation/detail/edit/search/status/delete, group synchronization, forced password change, self-deletion/deactivation prevention, privilege-escalation prevention, duplicate-email rejection, inactive-session rejection, settings persistence through a fresh Settings instance, maintenance behavior, invalid-setting rejection, current-password validation, CSRF rejection, unavailable registration/GET logout, and first-superadmin setup/refusal to repeat setup.

Shield recovery tests mock email delivery only and exercise token creation, verification, consumption, and password change without the forgotten password. A failed admin recovery notification is verified not to force a password change.

Tests use `cidaisy_test` with a separate table prefix. The development database is not used by PHPUnit and contains no seeded users.

## Browser checks

An authenticated preview used the separate test database and the PHP development server. At 1440px width, dashboard, users, create-user, profile, and settings each returned HTTP 200 with the expected heading and no document-level horizontal overflow. At 390px width, the dashboard had no document-level horizontal overflow, the navigation drawer opened and closed, and a theme change survived reload. Login and dashboard navigation were exercised through the browser.

## Remaining deployment configuration

- No initial development administrator was created. Set temporary setup credentials locally and run `php spark app:superadmin`, then remove them; see the [developer guide](DEVELOPER_GUIDE.md#first-superadmin).
- Actual SMTP delivery has not been tested. Configure a local mail catcher or SMTP service before relying on account recovery. Mocked delivery tests do not demonstrate real email delivery.
- Nginx was detected running in Laragon, but its external configuration was not changed. The application was verified using `spark serve`. The [Nginx example](deploy/nginx.conf.example) must be adapted to your installation, with the document root pointing to `public`.
- The npm install reported an unapproved optional `@parcel/watcher` install script. No script approval was added; clean install and the production CSS build both succeeded. The long-running development watch command was not tested.
- No deployment or external publication was performed.
