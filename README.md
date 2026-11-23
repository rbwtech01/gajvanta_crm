# RBW CRM

RBW CRM is a Laravel 12 application for managing sales customers and orders, employee roles, machinery inventory and serial numbers, shipments, and operational reporting. Its staff interface is built with Filament and is divided into role-specific panels.

## Getting started

Requirements: PHP 8.2+, Composer, and Node.js/npm.

1. Install PHP and JavaScript dependencies and build the frontend:

   ```powershell
   composer run setup
   ```

   The setup script installs dependencies, creates `.env` if needed, generates an application key, runs migrations, and builds frontend assets. Configure the database and any integrations in `.env` first. The example environment defaults to SQLite.

2. Start the development services:

   ```powershell
   composer run dev
   ```

   This starts the Laravel server, queue listener, application log viewer, and Vite development server.

3. Run automated tests:

   ```powershell
   php artisan test
   ```

For module responsibilities, panel paths, integrations, and the main code entry points, see the [Developer Project Guide](docs/PROJECT_GUIDE.md).

## Project stack

- Laravel 12 / PHP 8.2+
- Filament 5 for administrative and staff panels
- Eloquent ORM and Laravel migrations
- Laravel Excel for spreadsheet import/export
- Spatie Media Library for uploaded media
- Vite and Tailwind CSS for frontend assets

Before sharing a seeded environment, review the account seeders and replace any development credentials with environment-appropriate credentials.
