# Technology Stack

## Core Framework

- **PHP**: ^8.2 (minimum requirement)
- **Laravel**: ^11.0 (latest major version)
- **Vue.js**: Progressive JavaScript framework for frontend
- **Vite**: Modern build tool for asset compilation

## Backend Technologies

### Key Laravel Packages
- **Laravel Sanctum**: API authentication
- **Laravel Octane**: High-performance application server
- **Laravel Socialite**: OAuth authentication
- **Laravel UI**: Frontend scaffolding
- **Konekt Concord**: Modular package management

### Database & Search
- **MySQL**: 8.0+ (primary database)
- **Elasticsearch**: ^8.10 (search and indexing)
- **Redis**: Caching and session management
- **Predis**: Redis client for PHP

### Additional Libraries
- **Intervention Image**: Image processing and manipulation
- **DomPDF/mPDF**: PDF generation
- **Maatwebsite Excel**: Excel import/export
- **Guzzle**: HTTP client
- **PayPal SDK**: Payment integration
- **OpenAI PHP**: AI integration support

## Frontend Technologies

- **Vite**: Asset bundling and hot module replacement
- **Axios**: HTTP client for API requests
- **Tailwind CSS**: Utility-first CSS framework (used in admin/shop themes)

## Development Tools

- **Composer**: PHP dependency management
- **NPM**: JavaScript package management
- **Laravel Pint**: Code style fixer (PSR-2 compliance)
- **Pest**: Modern PHP testing framework
- **PHPUnit**: Unit testing
- **Playwright**: End-to-end testing
- **Laravel Debugbar**: Development debugging
- **Faker**: Test data generation

## Docker Support

- **Laravel Sail**: Docker development environment
- MySQL, Redis, Elasticsearch, Kibana, and Mailpit services included
- Default ports: 80 (app), 5173 (Vite), 3306 (MySQL)

## Common Commands

### Development
```bash
# Start development server (manual - do not use in automation)
php artisan serve

# Start Vite dev server (manual - do not use in automation)
npm run dev

# Using Docker/Sail
./vendor/bin/sail up
```

### Build & Compile
```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Build assets for production
npm run build

# Generate application key
php artisan key:generate
```

### Database
```bash
# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Refresh database (drop all tables and re-migrate)
php artisan migrate:fresh --seed
```

### Testing
```bash
# Run Pest tests
./vendor/bin/pest

# Run PHPUnit tests
./vendor/bin/phpunit

# Run Playwright E2E tests
npx playwright test
```

### Code Quality
```bash
# Fix code style (PSR-2)
./vendor/bin/pint

# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan optimize
```

### Package Management
```bash
# Discover packages
php artisan package:discover

# Publish package assets
php artisan vendor:publish
```

## Performance Optimization

- **Laravel Octane** for high-performance serving
- **Response caching** via Spatie package
- **Full Page Cache (FPC)** package included
- **Redis** for session and cache storage
- **Elasticsearch** for fast product search
