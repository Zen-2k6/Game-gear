# GameGear Hub

A server-rendered PHP and MySQL gaming-gear storefront with customer and admin areas.

## Requirements

- PHP 8.1 or newer with PDO MySQL
- MySQL 8.0 or newer
- A web server such as Apache, Nginx with PHP-FPM, or PHP's development server

## Local setup

1. Create the database (replace `gamegear_hub` if you use another `DB_NAME`):

   ```sh
   mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS gamegear_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   ```

2. Apply the schema migration:

   ```sh
   mysql -u root -p gamegear_hub < database/migrations/001_create_schema.sql
   ```

3. Load the optional demo data:

   ```sh
   mysql -u root -p gamegear_hub < database/seeds/001_demo_data.sql
   ```

4. Copy `.env.example` to `.env` and update the values for your machine. The application loads this file automatically; server environment variables take precedence.
5. Start the development server:

```sh
php -S localhost:8000 -t public public/index.php
```

When `public/` is the web server document root, set `APP_URL=` in `.env`. When the repository is hosted in an Apache subdirectory such as `/gamegear`, set `APP_URL=/gamegear/public`.

For Apache, Nginx/PHP-FPM, or production deployments, configure the same variables in the server environment. Use a restricted database account and a non-empty password outside local development.

## Environment variables

| Variable | Required | Default | Description |
| --- | --- | --- | --- |
| `DB_HOST` | No | `127.0.0.1` | MySQL host |
| `DB_PORT` | No | `3306` | MySQL port |
| `DB_NAME` | No | `gamegear_hub` | MySQL database |
| `DB_USER` | Yes | — | MySQL username |
| `DB_PASS` | Yes | — | MySQL password; may be explicitly empty for local development |
| `APP_URL` | No | Auto-detected | Public URL path prefix, without a trailing slash |

The database seed creates a demo administrator account (`admin@gamegear.test` / `admin123`). Change it before using the application outside a demo environment.

## Application structure

```text
Game-gear/
├── public/
│   ├── index.php                  # Single HTTP entry point and router
│   └── assets/
│       ├── css/style.css
│       └── js/app.js
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── CartController.php
│   │   ├── CheckoutController.php
│   │   ├── AdminController.php
│   │   └── StorefrontController.php
│   ├── Services/
│   │   ├── CartService.php
│   │   └── OrderService.php
│   ├── Views/
│   │   ├── user/
│   │   ├── admin/
│   │   └── partials/
│   └── Support/
│       └── helpers.php
├── config/
│   ├── app.php
│   └── database.php
├── database/
│   ├── migrations/
│   │   └── 001_create_schema.sql
│   └── seeds/
│       └── 001_demo_data.sql
├── tests/
│   ├── ArchitectureTest.php
│   ├── RouterTest.php
│   ├── ViewRenderTest.php
│   └── run.php
├── .env.example
└── README.md
```

`public/index.php` is the only executable web entry point. It creates the services and controllers, validates CSRF tokens for every POST request, and dispatches requests by route or action.

Controllers own request validation, database queries needed to prepare pages, authorization, and redirects. `CartService` reconciles the session cart with live product stock. `OrderService` owns transactional order creation, locked price and stock checks, shipment creation, and demo payment records.

Views contain presentation markup and receive their data from controllers. Customer and admin layouts remain independent, while shared routing, rendering, authentication, CSRF, escaping, and formatting functions live under `app/Support`. Application and PDO configuration live in `config`.

The admin portal uses the clean `/admin` route. Other storefront routes retain the portable query-string form such as `index.php?route=products`. Apache rewriting is included in `public/.htaccess`; for Nginx, route missing files to `index.php` with `try_files`.

## Tests

Run the structural and routing checks with:

```sh
php tests/run.php
```

See `database/README.md` for migration and seed details.
