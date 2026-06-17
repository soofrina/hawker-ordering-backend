# HawkerGo — Project structure

HawkerGo is split into three portals plus shared resources.

```
Hawkergo/
├── customer/          Customer website (browse, order, account)
├── hawker/            Hawker stall portal (orders, menu, reports)
├── admin/             Admin control panel
├── connection/        Database connection
├── includes/          Shared PHP helpers
├── css/               Shared stylesheets
├── js/                Shared JavaScript
├── images/            Customer images & assets
├── docs/              User guide (read docs/index.html)
└── DATABASE FILE/     SQL dump for setup
```

## Entry URLs (XAMPP)

| Portal   | URL |
|----------|-----|
| Customer | http://localhost/Hawkergo/ or http://localhost/Hawkergo/customer/ |
| Hawker   | http://localhost/Hawkergo/hawker/ |
| Admin    | http://localhost/Hawkergo/admin/ |
| User guide | http://localhost/Hawkergo/docs/ |

## Setup

1. Import `DATABASE FILE/hawker.sql` into MySQL (database name: `hawker`).
2. Start Apache and MySQL in XAMPP.
3. Open the customer site URL above.

Default database settings are in `connection/connect.php` (localhost, root, no password).

## Notes

- Customer pages live under `customer/`; the root `index.php` redirects there.
- Stall and dish images are stored under `admin/Res_img/`.
- Hawker portal accounts are linked to hawker stalls in the database (one account per stall).

See **docs/USER_GUIDE.md** or **docs/index.html** for full usage instructions.
