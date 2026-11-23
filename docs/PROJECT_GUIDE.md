# RBW CRM: Developer Project Guide

This guide is a map of the current application structure and the main places to make changes. It describes the implementation in this repository; for Laravel or Filament framework behavior, refer to their official documentation.

## What the application does

RBW CRM is a Laravel application for sales and order operations. It manages customers and bookings, staff and role-specific workflows, machinery and serial numbers, shipments, complaints, and business reports. Filament provides the staff UI through separate panels. The application also exposes a key-protected machinery API and synchronizes Meta Ads campaign data.

## Technology and entry points

| Area | Location | Responsibility |
| --- | --- | --- |
| PHP dependencies and Composer scripts | `composer.json` | Laravel, Filament, permissions, exports, media, scripts, and PHP runtime requirements |
| Frontend dependencies and scripts | `package.json`, `vite.config.js` | Vite build/dev scripts and frontend packages |
| Application bootstrap and providers | `bootstrap/app.php`, `bootstrap/providers.php` | Framework bootstrapping and registration of all seven Filament panel providers |
| Web routes | `routes/web.php` | Password setup, order calendar data, invoices, and public serial/QR pages |
| API routes | `routes/api.php` | Bajaj machinery item and serial-verification API |
| Console schedule | `routes/console.php` | Daily Meta Ads sync schedule |
| Database schema | `database/migrations/` | Versioned schema changes |
| Seed data | `database/seeders/` | Role and development user setup |
| Automated tests | `tests/Feature/`, `tests/Unit/` | HTTP/workflow and isolated behavior tests |

## Application modules

### Staff, roles, and access

- `app/Models/User.php` is the authenticatable staff model. It stores employee details, salary fields, activation state, call-center association, and role relationships.
- `app/Models/CallCenter.php` represents call centers and their users. `app/Models/AgentTarget.php` stores agent target data.
- `app/Notifications/AgentInvitationNotification.php` and `app/Http/Controllers/SetPasswordController.php` implement the invitation/password setup flow.
- `app/Http/Middleware/` contains request middleware, including the API-key check for Bajaj endpoints.
- `database/seeders/RoleSeeder.php` defines the application roles. `User::canAccessPanel()` maps roles to Filament panel IDs; panel login alone does not grant access to every panel.

### Filament panels

Each panel has its own provider under `app/Providers/Filament/`. The provider sets its URL path and discovers resources, pages, and widgets from a corresponding `app/Filament/` directory. Role-to-panel authorization is centralized in `User::canAccessPanel()`.

| Panel | URL | Code area | Main purpose |
| --- | --- | --- | --- |
| Admin | `/crm` | `app/Filament/Resources/`, `app/Filament/Pages/`, `app/Filament/Widgets/` | CRM administration, shared reporting pages, and core resources |
| Agent | `/agent` | `app/Filament/Agent/` | Agent dashboard and agent-owned customers, complaints, and visiting orders |
| Call Center Admin (Manager) | `/manager` | `app/Filament/Manager/` | Agent management and call-center performance |
| Operations Manager | `/order-confirming` | `app/Filament/Operations/` | Order confirmation workflows, customer orders, and complaints |
| Packing | `/packing` | `app/Filament/Packing/` | Packing-stage order/customer workflow |
| Shipment | `/shipment` | `app/Filament/Shipment/` | Shipment-stage order workflow |
| Supervisor | `/supervisor` | `app/Filament/Supervisor/` | Supervisor order/customer workflow |

Role-specific resource directories intentionally have their own forms, tables, and pages, even where they act on the same Eloquent model. For example, several panels have a `CustomerResource`, but its queries and actions can differ by workflow. Check that panel's resource before changing shared behavior. The admin panel discovers the shared resources and pages directly from `app/Filament/Resources/` and `app/Filament/Pages/`.

Within a Filament resource, the usual organization is:

- `*Resource.php`: model binding, navigation, and page registration.
- `Pages/`: list, create, and edit screens and their actions.
- `Schemas/`: form fields and layout.
- `Tables/`: columns, filters, and row actions.
- `RelationManagers/`: related records managed from a resource.

### Customers, bookings, and order operations

- `app/Models/Customer.php` is the central customer/order record. It relates to the agent, creator/user, product category, machine model, shipment, complaints, and hold reason. It also uses soft deletes and Spatie Media Library.
- `app/Models/Complaint.php`, `app/Models/VisitingOrder.php`, `app/Models/Invoice.php`, `app/Models/Shipment.php`, `app/Models/Courier.php`, and `app/Models/HoldReason.php` cover related operational records.
- `app/Services/CustomerImportService.php` imports customer rows from Excel and applies the import transaction and data lookups.
- `app/Http/Controllers/InvoiceController.php` creates downloadable invoice output; the template is `resources/views/pdf/invoice.blade.php`.
- `app/Filament/Pages/OrderCalendar.php` and `routes/web.php` provide calendar UI data. The calendar-events route validates the requested dates and active agent before returning grouped booking data.
- `app/Filament/Pages/OrdersByDate.php`, `Dashboard.php`, and `DashboardDetails.php` provide order and dashboard reporting workflows.
- The Agent customer resource scopes its records to the signed-in user. Other panels have separate resource implementations and should be checked independently for their filters and permissions.

The customer workflow uses a status field to track order progress. Status-specific screens and transitions are implemented in panel resources and their table/form actions; consult those before adding a new status or changing a transition.

### Machinery catalog and serial numbers

There are two related but distinct catalog areas:

- Sales catalog: `Category`, `MachineModel`, and `MachineModelAttachment` models and their admin Filament resources represent categories/models used in customer bookings.
- Machinery inventory: `MachineryCategory`, `MachineryItem`, `MachinerySerial`, and `MachinerySerialCounter` represent machinery records and generated serials.

`app/Services/MachinerySerialService.php` generates serials inside a database transaction. Machinery inventory resources and serial management are under `app/Filament/Resources/MachineryCategories/`, `MachineryItems/`, and `MachineryItems/Pages/ManageMachinerySerials.php`. Machinery API responses and serial QR pages use the related controllers and Blade views described below.

### Meta Ads reporting

- `app/Services/MetaAdsService.php` retrieves yesterday's campaign spend from the Meta Graph API.
- `app/Console/Commands/SyncMetaAds.php` fetches and synchronizes campaign data into `MetaAdReport` records.
- `app/Console/Commands/InspectMetaAds.php` inspects paginated raw API data for a requested date without saving it.
- `app/Imports/MetaAdsImport.php` handles spreadsheet-based Meta Ads imports.
- `app/Filament/Pages/MetaAdsReport.php` presents campaign reporting and budget/spend views.
- `app/Support/EmployeeDailyExpenseCalculator.php` contains the daily salary, spend, and budget calculations used by reporting.
- `config/meta.php` reads Meta settings from environment variables. `routes/console.php` schedules `app:sync-meta-ads` daily at 14:00 in the `Asia/Kolkata` timezone.

### API and public HTTP workflows

`routes/api.php` groups the following routes behind `BajajApiAuthentication` (the `X-API-Key` header is checked against `BAJAJ_API_KEY`):

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/api/machinery-items` | List active machinery items |
| `GET` | `/api/machinery-items/{machineryItem}` | Fetch a machinery item |
| `POST` | `/api/serials/verify` | Verify a serial number/code |

The API controllers are under `app/Http/Controllers/Api/`. The web routes in `routes/web.php` also provide:

- Password setup pages and submission (`SetPasswordController`).
- Invoice download (`InvoiceController`).
- Calendar event data for an active agent.
- Machinery serial details and QR views at `/machines/serial/{serialCode}` and `/machines/serial/{serialNumber}/qr`.

### Reporting, exports, and dashboards

- `app/Filament/Pages/` contains admin-oriented pages for dashboards, dashboard details, pincode search, order calendars, orders by date, employee daily expenses, and Meta Ads reporting.
- `app/Filament/Widgets/` and role-specific `Widgets/` directories provide dashboard summaries and charts.
- `app/Exports/` contains Laravel Excel exports for agents, customers, orders, dashboards, and machinery serials.
- `app/Filament/Exports/CustomerExporter.php` contains a Filament-specific customer exporter.
- `app/Filament/Agent/Widgets/` and `app/Filament/Manager/Widgets/` hold role-specific dashboards and performance summaries.
- `resources/views/filament/` contains custom Filament page/widget templates; `resources/views/pdf/` and `resources/views/qr/` contain invoice and serial views.

### Frontend assets

- `resources/js/` contains the Vite entry point, Axios bootstrap, and custom admin date-range behavior.
- `resources/css/app.css` contains application styles.
- `resources/views/` contains Blade templates.
- `public/` is the web root and compiled asset destination; generated files there should not be edited as source.

## Local development

The repository's Composer `setup` script installs PHP and JavaScript dependencies, creates `.env` if missing, generates an application key, runs migrations, and builds the frontend:

```powershell
composer run setup
```

Set the database connection in `.env` before running setup. The checked-in `.env.example` uses SQLite. For integrations, configure the required Meta API values (`META_ACCESS_TOKEN` and one or more ad account IDs) and `BAJAJ_API_KEY` as needed. Keep credentials local; never add them to source control.

Run the app and its development services with:

```powershell
composer run dev
```

Useful focused commands:

```powershell
php artisan migrate
php artisan test
php artisan app:sync-meta-ads
php artisan app:inspect-meta-ads 2026-01-31
npm run build
```

The Meta commands require valid Meta API configuration. The inspect command expects a real date in `YYYY-MM-DD` format and does not persist the returned data.

## Tests

- `tests/Feature/` exercises HTTP endpoints and application workflows, including pincode access, invoices, and Meta Ads behavior.
- `tests/Unit/` covers isolated domain/support logic and notifications.
- PHPUnit is configured to run with an in-memory SQLite database. Run the full suite with `php artisan test` or `composer test`.

When modifying a workflow, add or update the closest existing test file. For a Filament change, test the panel/role behavior as well as the underlying model behavior where appropriate.

## Change guide

| If the change is about… | Start here |
| --- | --- |
| A panel URL, login, or discovered resources | `app/Providers/Filament/` |
| Which role can enter a panel | `app/Models/User.php` and `database/seeders/RoleSeeder.php` |
| A form, table, or action in one staff workflow | The matching `app/Filament/<Panel>/Resources/<Resource>/` directory |
| Shared customer/order data or relationships | `app/Models/Customer.php`, related models, and `database/migrations/` |
| Excel import/export | `app/Services/`, `app/Imports/`, or `app/Exports/` |
| Machinery serial generation or verification | `app/Services/MachinerySerialService.php`, API controllers, and `app/Models/MachinerySerial.php` |
| Meta Ads sync or reporting | `app/Console/Commands/`, `app/Services/MetaAdsService.php`, `app/Models/MetaAdReport.php`, and `app/Filament/Pages/MetaAdsReport.php` |
| HTTP behavior | `routes/`, then the corresponding controller or middleware |
| A reporting calculation | `app/Support/` and its unit/feature tests |
| Frontend behavior | `resources/js/`, `resources/css/`, and related Blade views under `resources/views/` |
