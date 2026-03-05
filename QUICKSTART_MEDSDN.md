# MedSDN Quick Start Guide

## 🚀 Get Started in 5 Minutes

### Prerequisites
- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js & NPM

### Installation Steps

#### 1. Clone & Install
```bash
# Navigate to project directory
cd medsdn

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

#### 2. Configure Environment
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit .env file with your database credentials
nano .env
```

**Required .env settings:**
```env
APP_NAME=MedSDN
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medsdn
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### 3. Setup Database
```bash
# Run migrations
php artisan migrate

# Seed database with sample data (optional)
php artisan db:seed
```

#### 4. Build Assets
```bash
# Build frontend assets
npm run build

# Or run in development mode with hot reload
npm run dev
```

#### 5. Start Server
```bash
# Start Laravel development server
php artisan serve

# Visit: http://localhost:8000
```

## 🏥 Medical Features Setup

### Enable Medical Features

Edit your `.env` file:

```env
# Prescription Management
MEDSDN_PRESCRIPTION_REQUIRED=true
MEDSDN_PRESCRIPTION_UPLOAD=true
MEDSDN_PRESCRIPTION_VERIFICATION=true

# License Verification
MEDSDN_LICENSE_VERIFICATION=true
MEDSDN_LICENSE_REQUIRED_CHECKOUT=false

# Product Features
MEDSDN_BATCH_TRACKING=true
MEDSDN_EXPIRY_MANAGEMENT=true
MEDSDN_EXPIRY_ALERT_DAYS=90

# Compliance
MEDSDN_HIPAA_ENABLED=false
MEDSDN_AUDIT_LOGGING=true
MEDSDN_DATA_ENCRYPTION=true
```

### Clear Cache After Changes
```bash
php artisan config:clear
php artisan cache:clear
```

## 🐳 Docker Setup (Alternative)

### Using Laravel Sail
```bash
# Install Sail
composer require laravel/sail --dev

# Publish Sail configuration
php artisan sail:install

# Start containers
./vendor/bin/sail up

# Run migrations
./vendor/bin/sail artisan migrate

# Access at http://localhost
```

## 👤 Default Admin Access

After seeding the database:

- **URL**: http://localhost:8000/admin
- **Email**: admin@example.com
- **Password**: admin123

**⚠️ Change these credentials immediately in production!**

## 📦 Common Commands

### Development
```bash
# Start dev server
php artisan serve

# Watch assets for changes
npm run dev

# Run tests
./vendor/bin/pest
```

### Production
```bash
# Build assets for production
npm run build

# Optimize application
php artisan optimize

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Maintenance
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback
```

## 🔧 Troubleshooting

### Issue: Permission Denied
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Issue: Assets Not Loading
```bash
# Rebuild assets
npm run build

# Clear view cache
php artisan view:clear
```

### Issue: Database Connection Error
```bash
# Check database credentials in .env
# Ensure MySQL is running
sudo systemctl status mysql

# Test connection
php artisan tinker
>>> DB::connection()->getPdo();
```

### Issue: Composer Dependencies
```bash
# Update dependencies
composer update

# Clear composer cache
composer clear-cache
```

## 📚 Next Steps

1. **Configure Your Store**
   - Visit Admin Panel → Settings
   - Set up store information
   - Configure payment methods
   - Set up shipping methods

2. **Add Products**
   - Navigate to Catalog → Products
   - Add your medical products
   - Set up categories
   - Configure attributes

3. **Customize Theme**
   - Go to Settings → Themes
   - Customize colors and layout
   - Upload your logo
   - Configure homepage

4. **Set Up Medical Features**
   - Review `config/medsdn.php`
   - Enable required features
   - Configure prescription settings
   - Set up license verification

5. **Test Your Store**
   - Create test orders
   - Test checkout process
   - Verify email notifications
   - Test admin workflows

## 🆘 Getting Help

- **Documentation**: [docs.medsdn.com](https://docs.medsdn.com) (Coming Soon)
- **Forum**: [forums.medsdn.com](https://forums.medsdn.com) (Coming Soon)
- **Email**: support@medsdn.com
- **Issues**: GitHub Issues

## 📖 Additional Resources

- `README_MEDSDN.md` - Full project documentation
- `REBRANDING.md` - Rebranding details
- `config/medsdn.php` - Medical features configuration
- `.kiro/steering/medical-features.md` - Medical features guide

## ✅ Verification Checklist

After installation, verify:

- [ ] Application loads at http://localhost:8000
- [ ] Admin panel accessible at /admin
- [ ] Can login with default credentials
- [ ] Database tables created successfully
- [ ] Assets loading correctly (CSS/JS)
- [ ] No errors in browser console
- [ ] No errors in Laravel logs (storage/logs/)

## 🎉 You're Ready!

Your MedSDN installation is complete. Start building your medical eCommerce platform!

---

**Need help?** Contact support@medsdn.com
