# Inlay demo

The official consumer demo for Inlay: Laravel 13, Inertia 3, React, and the
public `inlayphp/inlay` package.

**Live demo:** [inlay-demo.laravel.cloud](https://inlay-demo.laravel.cloud/)

This repository deliberately has no Composer VCS repositories, monorepo path
packages, or local npm links. Composer installs Inlay from Packagist and pnpm
installs every `@inlayphp/*` renderer from npm, exactly like an application
created outside the Inlay monorepo.

## What the installer gives an application

Start with an existing Laravel application using Inertia 3 and React:

```bash
composer require inlayphp/inlay:"^0.3"
php artisan inlay:install --panels
php artisan migrate
php artisan inlay:make-user
pnpm run build
php artisan inlay:doctor --production
```

The default preset provides the `/admin` panel, Inlay-owned login and logout,
account profile and password screens, the server-authored dashboard widgets, a
User resource with create/edit/delete, theming, and the Media Manager. The
generated application code remains editable; package code stays in `vendor/`
and `node_modules/`.

This demo also registers a PHP-authored showcase across three navigation groups:

- **Shop** — products, product categories, brands, customers, and orders (status
  tabs, money columns,
  repeatable line items, filters, badges, and order detail infolists).
- **Blog** — posts, authors, and categories (content forms, status views,
  searchable tables, copyable slugs, and read-only detail pages).
- **HR & projects** — departments, employees, projects, tasks, timesheets,
  leave requests, and expenses (checkbox skill lists, key/value metadata,
  repeatable plans and expense lines, workflow tabs, and central validation).

Every resource uses the shared Inlay resource pages and renderer contracts. The
React files under `resources/js/pages/inlay/resource/` are intentionally generic
mount points; the schema, table, infolist, actions, authorization, validation,
navigation group, and seeded data are all defined in PHP. `ShowcaseRules` is an
application example, not a package rule set.

The public demo adds two standalone examples outside the panel:

- `/demo/forms` — a server-authored Inlay form
- `/demo/tables` — a server-driven Inlay table

Those package examples, together with the GitHub source link, are grouped under
**Package demos** in the panel navigation and open in a new browser tab.

The dashboard widgets are also resolved from PHP and include stats, charts, and
recent blog/order tables. CMS packages are intentionally excluded from this
demo so the admin surface stays focused on the core Inlay experience. The
default Media Manager seed includes a text asset and two SVG dashboard assets,
so the picker and delivery routes have useful content immediately after
`migrate --seed`.

## Demo login

The database seeder creates one verified administrator:

```text
Email: demo@inlayphp.com
Password: password
```

Override `DEMO_USER_NAME`, `DEMO_USER_EMAIL`, and `DEMO_USER_PASSWORD` in the
deployment environment. Do not use the public demo password for a real panel.

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
corepack enable
pnpm install --frozen-lockfile
pnpm run build
php artisan serve
```

Open `http://127.0.0.1:8000`.

## Verification

```bash
composer validate --strict
composer test
pnpm run lint:check
pnpm run format:check
pnpm run types:check
pnpm run build
php artisan inlay:doctor --production
```

GitHub Actions repeats the full install, migration, seed, backend tests, type
checks, production build, and compiled Inlay CSS verification from a clean
checkout.

## Laravel Cloud

The demo consumes only published Packagist and npm packages, so it can deploy
from this repository without the Inlay monorepo. Commit both lockfiles and run
the normal production installation and build commands:

```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
pnpm install --frozen-lockfile
pnpm run build
php artisan inlay:doctor --production
php artisan migrate --force
```

Configure `INLAY_MEDIA_DISK` with persistent or object storage in production.
The application filesystem may be replaced between Cloud deployments and must
not be treated as durable storage for Media Manager uploads.

The demo includes the S3 Flysystem adapter by default, so Laravel Cloud can
attach an S3-compatible bucket without changing the application dependencies.
Set `INLAY_MEDIA_DISK=s3` and provide the usual `AWS_*` bucket credentials in
the environment when enabling the bucket.
