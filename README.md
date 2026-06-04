# Backend starter — Hawker Ordering Platform (PHP + filess.io)

A tiny PHP + MySQL backend. No framework. Each file in `api/` is one endpoint
that runs a query and returns JSON.

## Files
- `config.php` — your filess.io credentials (DO NOT commit — add to .gitignore)
- `db.php` — the single shared PDO connection; every endpoint uses `db()`
- `test_connection.php` — run this first to confirm PHP reaches filess.io
- `schema.sql` — creates all the tables (run once in Adminer / Workbench)
- `api/get_menu.php` — example endpoint returning the menu as JSON

## Setup (in order)
1. **Fill in `config.php`** with the host, port, db name, user and password
   shown on your filess.io dashboard. Copy them exactly (note the port).
2. **Create the tables**: open Adminer (from filess.io) or MySQL Workbench,
   connect with the same credentials, and run the contents of `schema.sql`.
3. **Test the connection**: run `test_connection.php`
   - in a browser, or
   - from a terminal: `php test_connection.php`
   You should see "SUCCESS" and a list of your tables.
4. **See real data**: open `api/get_menu.php` — you should get JSON of the
   seeded menu items.

## To run PHP locally
From this folder: `php -S localhost:8000`
Then visit http://localhost:8000/test_connection.php and
http://localhost:8000/api/get_menu.php

## Notes
- filess.io free tier = 10 MB. Don't store images in the DB — keep image URLs only.
- Keep `config.php` out of GitHub. Share credentials with teammates privately.
