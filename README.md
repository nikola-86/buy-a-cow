# Buy a Cow

A small user activity tracker in vanilla PHP 8.1 and MySQL 8, without frameworks.
Users register, log in, visit Page A ("Buy a cow" button) and Page B ("Download" button);
every action is stored as an event. Admins see every event on the Statistics page, and every
logged-in user sees a daily chart and table on the Reports page.

## Setup

Requirements: Docker with Compose, and PHP 8.1 with Composer on the host (the app container
mounts the project directory, including `vendor/`).

```bash
composer install && docker compose up -d --build
```

Open <http://localhost:8080>. On the first start MySQL runs the scripts in `database/`:
the schema, the admin user and the empty test database `user_tracker_test`.

The init scripts only run on a fresh volume. To add the test database to an existing volume
without losing data:

```bash
docker compose exec -T db mysql -uroot -prootpassword < database/03-test-database.sql
```

### Accounts

| Account | Username | Password |
| --- | --- | --- |
| Admin | `admin` | `admin123` |
| Demo users (after seeding) | `demo1`, `demo2`, `demo3` | `demo1234` |

Users created via `/register` always get the `user` role.

### Demo data

The database starts empty except for the admin. To see a multi-day chart, run the seeder
(optional, can be run more than once):

```bash
docker compose exec app php bin/seed-demo-data.php
```

It creates `demo1`-`demo3` if they are missing and inserts 500 random events spread over the last 30 days.

## Quality checks

```bash
composer cs     # PHP_CodeSniffer, PSR-12 for src/, public/, bin/ and tests/
composer test   # PHPUnit: Unit and Integration test suites
```

The Integration suite needs the Docker MySQL (port 3306) and the `user_tracker_test` database.
Run only the unit tests with `vendor/bin/phpunit --testsuite Unit`, or everything inside the
container with `docker compose exec app vendor/bin/phpunit`.

## Architecture

```
public/index.php        Front controller and composition root
src/Core/               Infrastructure: Database (PDO singleton), Request, Response, Router, View, Csrf, Auth
src/Controllers/        HTTP only: read the request, call a service, render a view or respond
src/Services/           Business logic: AuthService, ActivityTracker, StatisticsService, ReportService
src/Repositories/       SQL only, behind EventRepositoryInterface and UserRepositoryInterface
src/Enums/              EventAction, EventTarget, Role: the single source of allowed values
src/Dto/EventFilter.php Typed Statistics filter
views/                  Templates, escaped output via e()
bin/seed-demo-data.php  Optional demo data seeder (separate CLI entry point)
database/               MySQL init scripts (schema, admin, test database)
tests/                  Unit tests (no database) and one integration test
```

### Design decisions

- **Layers**: controllers handle HTTP, services hold the business logic, repositories contain the SQL,
  and `Core` is infrastructure. Superglobals are only read in `Core` (`Request`, `Auth`, `Csrf`).
- **Dependency injection without a container**: every class receives its dependencies through the
  constructor. `public/index.php` is the composition root: it registers one factory per controller, and
  the `Router` only calls the factory of the matched route, so the database connection is opened lazily.
  With five controllers, a DI container would add more code than it saves.
- **Dependency inversion at the infrastructure boundary**: services and controllers depend on
  `EventRepositoryInterface` and `UserRepositoryInterface`. Those are the only interfaces in the project,
  so the services can be tested with PHPUnit test doubles.
- **No middleware pipeline**: access and CSRF checks are the only cross-cutting concerns, so they live in the
  `Router`. Event tracking is an explicit `ActivityTracker::track()` call where each event happens, because
  not every request is an event, and for login and registration the user id is only known halfway through the action.
- **401 instead of a redirect for the API**: an unauthenticated `POST /api/events` gets a JSON 401, which `buy-cow.js`
  turns into a redirect to the login page; web pages redirect to `/login` directly.
- **Security**: prepared statements for every query (native prepares), `password_hash()`, session id regeneration
  on login, hardened session cookie, a CSRF token for every POST, and output escaped with `htmlspecialchars()`.
- **Indexes**: `(created_at, action, target)` covers the Reports query and the Statistics date filter,
  `(user_id, created_at)` the user filters, `(action, created_at)` the action filter.
  The date filter is a range on `created_at`, never `DATE(created_at) = ...`, so the index can be used.
- **Error handling**: one top-level handler logs the exception (`docker compose logs app`) and shows a generic 500
  page (JSON for `/api/`). Only expected cases (validation, wrong credentials, unknown API target) are handled locally.

### Testing strategy

- **Unit tests** (`tests/Unit`) cover the services without a database. Each test shows a different technique:
  a `#[DataProvider]` for invalid Statistics filters, `createMock()` with `expects($this->once())->with(...)` for the
  pagination contract, a fixed "today" for deterministic gap filling in the Reports, and `expects($this->never())`
  to prove that a duplicate username never reaches `create()`. Stubs are used for queries, mocks only for contracts that matter.
- **Integration test** (`tests/Integration/EventRepositoryTest.php`) runs the real SQL of `dailyCounts()` and
  `findFiltered()` against the separate `user_tracker_test` database (`TEST_DB_NAME` in `phpunit.xml`), never the
  development database. It loads `database/01-schema.sql` if the tables are missing and rolls back every test.
- `PDO` is never mocked: a mocked `PDO` would only repeat the SQL strings, the integration test checks what the SQL returns.

## Interpretations of the specification

- **Roles**: "admin (user rights + view page with stat)" means an admin can do everything a user can,
  plus open the Statistics page. Statistics is the only admin-only page; there is no user management,
  and the only admin comes from `database/02-seed.sql`.
- **Reports** are available to every logged-in user and show the activity of all users. The user filter
  (bonus) is shown and applied only for admins; `?user_id=` is ignored for regular users.
- **Reports dates**: every day from the first event to today, days without events are shown with zeros.
- **"Buy a cow"**: the button disappears and `thankYou` appears only after the server stored the click (HTTP 201).
  This is client-side only: after a refresh the button is back, and every click is tracked.
- **"Download"** is a form button (`POST /download`), so link prefetching cannot record fake clicks, and the
  user stays on Page B while the dummy `.exe` downloads.
- **Statistics filters**: a single date (not a range), a user and one of the five actions
  (`login`, `logout`, `registration`, `view-page`, `button-click`). Pagination shows 50 events per page
  with Previous/Next links and no total page count.
- **Time zone**: everything runs in UTC (PHP, MySQL and the displayed dates).
- **Empty start**: the database contains only the admin; regular users are created via `/register` or the seeder.
- Failed login attempts are not tracked. Two simultaneous registrations with the same username end with a
  500 from the unique key instead of a validation message.

## Possible improvements

- A middleware pipeline once more cross-cutting concerns appear (rate limiting, logging, API authentication).
- A plain `created_at` index if the unfiltered Statistics query becomes slow at scale.
- A date range filter for the Reports and a JSON API for the report data.
- User management for admins (roles, deactivating accounts).
- Tracking failed login attempts.
- A total page count in the Statistics pagination.
