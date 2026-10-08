# Buy a Cow

User activity tracker in plain PHP 8.1 and MySQL 8, no framework.

## Setup

You need Docker. PHP and Composer run inside the container.

```bash
git clone https://github.com/nikola-86/buy-a-cow.git
cd buy-a-cow
docker compose up -d --build
docker compose exec app composer install
```

The app runs on http://localhost:8080.

Admin login is `admin` / `admin123`. Regular users can sign up on the Register page.

To get some demo data (3 users and 500 events over the last 30 days):

```bash
docker compose exec app php bin/seed-demo-data.php
```

Demo users are `demo1`, `demo2` and `demo3`, password `demo1234`.

## Pages

- Page A: "Buy a cow" button
- Page B: "Download" button
- Reports: daily chart and table, for every logged-in user (admin can also filter by user)
- Statistics: all events with filters, admin only

## Tests

```bash
docker compose exec app composer test               # unit tests
docker compose exec app composer test:integration   # integration test, uses the user_tracker_test database
docker compose exec app composer cs                 # code style (PSR-12)
```

## Notes

- All dates are in UTC.
- Reports show the activity of all users. Only the admin can filter by user.
- The Statistics date filter is a single day, not a range.
- "Buy a cow" hides the button after the click is saved. It shows up again after a refresh.
- Failed login attempts are not tracked.

## Troubleshooting

- Ports 8080 and 3306 need to be free.
- Errors are logged to `docker compose logs app`.
- To start over with an empty database: `docker compose down -v && docker compose up -d` (deletes all data).
