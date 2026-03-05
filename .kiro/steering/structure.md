# Project Structure

## Architecture Pattern

MedSDN follows a **modular package-based architecture** using Konekt Concord for package management. The codebase is organized into self-contained packages under the `packages/Webkul/` namespace, allowing for clean separation of concerns and extensibility.

## Root Directory Structure

```
medsdn/
├── app/                    # Core application code (minimal, most logic in packages)
├── bootstrap/              # Application bootstrapping
├── config/                 # Configuration files
├── database/               # Migrations and seeders (base tables only)
├── lang/                   # Multi-language support (20+ languages)
├── packages/Webkul/        # Modular packages (core business logic)
├── public/                 # Public assets and entry point
├── resources/              # Views, assets (if not in packages)
├── routes/                 # Application routes
├── storage/                # Logs, cache, uploads
├── tests/                  # Application-level tests
└── vendor/                 # Composer dependencies
```

## Package Structure

All business logic is organized into packages under `packages/Webkul/`. Each package follows a consistent internal structure:

### Standard Package Layout

```
packages/Webkul/{PackageName}/
├── src/
│   ├── Config/             # Package configuration (ACL, menus, system settings)
│   ├── Database/           # Migrations, factories, seeders
│   ├── DataGrids/          # Data grid definitions for admin tables
│   ├── Helpers/            # Helper classes and utilities
│   ├── Http/
│   │   ├── Controllers/    # HTTP controllers
│   │   ├── Middleware/     # Package-specific middleware
│   │   ├── Requests/       # Form request validation
│   │   └── Resources/      # API resources
│   ├── Listeners/          # Event listeners
│   ├── Mail/               # Mailable classes
│   ├── Models/             # Eloquent models
│   ├── Providers/          # Service providers
│   ├── Repositories/       # Repository pattern implementation
│   ├── Resources/
│   │   ├── assets/         # Package-specific JS/CSS
│   │   ├── lang/           # Package translations
│   │   └── views/          # Blade templates
│   ├── Routes/             # Package routes
│   └── Validations/        # Custom validation rules
├── tests/                  # Package-specific tests
├── composer.json           # Package dependencies
└── package.json            # Frontend dependencies (if applicable)
```

## Core Packages

### Admin & Shop
- **Admin**: Admin panel UI, controllers, and management features
- **Shop**: Storefront UI and customer-facing features
- **Theme**: Theme management and customization

### Catalog & Products
- **Product**: Product management, types, and catalog
- **Category**: Category hierarchy and management
- **Attribute**: Product attributes and attribute sets
- **Inventory**: Stock management

### Sales & Orders
- **Sales**: Order management and processing
- **Checkout**: Cart and checkout flow
- **Payment**: Payment method integrations
- **Shipping**: Shipping method management
- **Paypal**: PayPal payment integration

### Customer Management
- **Customer**: Customer accounts and profiles
- **User**: Admin user management

### Marketing & Rules
- **Marketing**: Campaigns, events, and promotions
- **CartRule**: Shopping cart price rules
- **CatalogRule**: Catalog pricing rules
- **Rule**: Rule engine foundation

### Content & CMS
- **CMS**: Content management (pages, blocks)
- **Notification**: Notification system

### Core Infrastructure
- **Core**: Foundation classes, helpers, and core functionality
- **DataGrid**: Reusable data grid component
- **DataTransfer**: Import/export functionality
- **Installer**: Installation wizard

### Additional Features
- **BookingProduct**: Booking/reservation products
- **Tax**: Tax calculation and management
- **SocialLogin**: OAuth social authentication
- **SocialShare**: Social media sharing
- **Sitemap**: XML sitemap generation
- **GDPR**: GDPR compliance features
- **MagicAI**: AI integration features
- **FPC**: Full page caching
- **DebugBar**: Development debugging tools

## Autoloading

PSR-4 autoloading is configured in `composer.json`:
- `App\` → `app/`
- `Webkul\{Package}\` → `packages/Webkul/{Package}/src`
- Tests follow the same pattern with `\Tests\` suffix

## Configuration Files

Located in `config/`:
- Standard Laravel configs (app, database, cache, etc.)
- MedSDN-specific: `concord.php`, `products.php`, `themes.php`
- Package configs published via `vendor:publish`

## Database Migrations

- Base migrations in `database/migrations/`
- Package-specific migrations in `packages/Webkul/{Package}/src/Database/Migrations/`
- Run all with `php artisan migrate`

## Routes

- Application routes in `routes/web.php`
- Package routes in `packages/Webkul/{Package}/src/Routes/`
- Typical route files: `web.php`, `auth-routes.php`, `rest-routes.php`

## Views & Assets

- Package views: `packages/Webkul/{Package}/src/Resources/views/`
- Package assets: `packages/Webkul/{Package}/src/Resources/assets/`
- Compiled assets published to `public/` via Vite

## Testing Structure

- Application tests: `tests/`
- Package tests: `packages/Webkul/{Package}/tests/`
- Pest and PHPUnit supported
- Playwright for E2E tests

## Key Conventions

1. **Repository Pattern**: Data access through repositories, not direct model queries
2. **Service Providers**: Each package has its own service provider for registration
3. **Event-Driven**: Heavy use of Laravel events and listeners
4. **DataGrids**: Standardized data grid component for admin tables
5. **Multi-Language**: All strings must be translatable via lang files
6. **ACL**: Access control defined in package Config/acl.php
7. **Modular Routes**: Routes organized by feature in separate files

## Adding New Features

When adding new functionality:
1. Create a new package under `packages/Webkul/` or extend existing package
2. Follow the standard package structure
3. Register package in `composer.json` autoload section
4. Create service provider and register in `config/concord.php`
5. Add migrations, models, repositories, controllers
6. Define routes, views, and assets
7. Add translations for all user-facing strings
8. Write tests in package `tests/` directory
