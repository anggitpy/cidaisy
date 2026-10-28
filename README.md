# CI Daisy — CodeIgniter 4 admin boilerplate

A reusable, server-rendered PHP admin foundation using CodeIgniter Shield, CodeIgniter Settings, MariaDB/MySQL, Tailwind CSS v4, and daisyUI. No JavaScript framework or bundler.

## Included

- Session authentication, remember me, and Shield magic-link recovery
- Responsive admin layout, mobile navigation, and persistent light/dark preference
- Dashboard, paginated/searchable users, group assignment, activation, and deletion
- Profile editing and password changes
- Persistent settings and maintenance mode
- Server-side permissions, session CSRF protection, and account-management safeguards
- Initial setup seeder, environment-based first-superadmin command, and feature tests

## Start

1. Run `composer install` and `npm ci`.
2. Copy `.env.example` to `.env` and configure your database, base URL, and SMTP.
3. Create the application database and a separate disposable test database.
4. Run `php spark migrate --all` and `php spark db:seed InitialSetup`.
5. Temporarily set `setup.username`, `setup.email`, and `setup.password` in `.env`.
6. Run `php spark app:superadmin`, then remove the setup credentials.
7. Run `npm run build` and `php spark serve --host 127.0.0.1 --port 8080`.
8. Open http://localhost:8080.

There is no default administrator or password. SMTP is required for email recovery. Production web roots must point to `public/`.

## Develop and verify

```sh
npm run dev
npm run build
composer test -- --no-coverage
composer check-platform-reqs
```

See [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md) for installation details, extension examples, permissions, testing, mail setup, and deployment. See [VERIFICATION.md](VERIFICATION.md) for checks executed during implementation.

Dependencies are pinned by Composer and npm lockfiles. PHP requires 8.2 or newer, subject to installed package requirements. This repository uses the official CodeIgniter appstarter and retains its MIT license.
