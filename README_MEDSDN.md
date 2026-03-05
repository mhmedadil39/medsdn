# MedSDN - Medical & Healthcare eCommerce Platform

<p align="center">
  <img src="https://via.placeholder.com/400x100/4A90E2/FFFFFF?text=MedSDN" alt="MedSDN Logo">
</p>

<p align="center">
  <strong>Open-Source Laravel-based eCommerce Platform for Medical & Healthcare Products</strong>
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#installation">Installation</a> •
  <a href="#documentation">Documentation</a> •
  <a href="#contributing">Contributing</a> •
  <a href="#license">License</a>
</p>

---

## 🏥 About MedSDN

MedSDN is a specialized eCommerce platform built on Laravel, designed specifically for medical and healthcare product sales. Based on the robust Bagisto framework, MedSDN adds healthcare-specific features while maintaining enterprise-grade scalability and performance.

### Why MedSDN?

- **Healthcare-Focused**: Built specifically for pharmacies, medical suppliers, and healthcare providers
- **Compliance-Ready**: Tools for HIPAA, FDA, and other healthcare regulations
- **Prescription Management**: Integrated prescription upload and verification workflows
- **Medical Product Attributes**: Specialized fields for drug information, expiry dates, and batch tracking
- **Multi-Vendor Support**: Enable multiple pharmacies or suppliers on one platform
- **Mobile-First**: Native mobile apps for iOS and Android
- **Headless Commerce**: API-first architecture for custom frontends

## ✨ Features

### Core eCommerce Features
- 📦 Complete product catalog management
- 🛒 Advanced shopping cart and checkout
- 💳 Multiple payment gateway integrations
- 🚚 Flexible shipping methods
- 📊 Comprehensive order management
- 👥 Customer account management
- 🎯 Marketing and promotions engine
- 📈 Analytics and reporting

### Healthcare-Specific Features
- 💊 Prescription upload and verification
- ⚕️ Medical license verification for buyers
- 🔬 Batch and lot number tracking
- 📅 Expiry date management
- ⚠️ Drug interaction warnings
- 🌡️ Cold chain logistics support
- 📋 Controlled substance tracking
- 🏥 EHR integration capabilities

### Technical Features
- 🎨 Modular package-based architecture
- 🌍 Multi-language support (20+ languages)
- 💱 Multi-currency support
- 🔐 Role-based access control (RBAC)
- 📱 Progressive Web App (PWA) ready
- 🚀 High-performance caching
- 🔍 Elasticsearch integration
- 🎭 Multi-theme support

## 🚀 Installation

### Requirements

- PHP 8.2 or higher
- MySQL 8.0 or higher
- Composer
- Node.js & NPM
- Redis (optional, recommended)
- Elasticsearch 8.10+ (optional, for search)

### Quick Start

```bash
# Clone the repository
git clone https://github.com/medsdn/medsdn.git
cd medsdn

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env file
# Then run migrations
php artisan migrate

# Seed the database (optional)
php artisan db:seed

# Build frontend assets
npm run build

# Start the development server
php artisan serve
```

Visit `http://localhost:8000` to access your MedSDN installation.

### Docker Installation

```bash
# Using Laravel Sail
./vendor/bin/sail up

# Access the application at http://localhost
```

## 📚 Documentation

- **Installation Guide**: [docs.medsdn.com/installation](https://docs.medsdn.com/installation) (Coming Soon)
- **User Guide**: [docs.medsdn.com/user-guide](https://docs.medsdn.com/user-guide) (Coming Soon)
- **Developer Guide**: [docs.medsdn.com/developers](https://docs.medsdn.com/developers) (Coming Soon)
- **API Reference**: [docs.medsdn.com/api](https://docs.medsdn.com/api) (Coming Soon)

## 🏗️ Architecture

MedSDN follows a modular package-based architecture:

```
medsdn/
├── app/                    # Core application
├── packages/Webkul/        # Modular packages
│   ├── Admin/             # Admin panel
│   ├── Shop/              # Storefront
│   ├── Product/           # Product management
│   ├── Customer/          # Customer management
│   ├── Sales/             # Order processing
│   └── ...                # 30+ packages
├── config/                # Configuration
├── database/              # Migrations & seeders
├── lang/                  # Translations
└── public/                # Public assets
```

## 🛠️ Technology Stack

- **Backend**: Laravel 11, PHP 8.2+
- **Frontend**: Vue.js 3, Tailwind CSS
- **Database**: MySQL 8.0+
- **Search**: Elasticsearch 8.10+
- **Cache**: Redis
- **Queue**: Redis/Database
- **Build Tool**: Vite
- **Testing**: Pest, PHPUnit, Playwright

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

```bash
# Install dependencies
composer install
npm install

# Run tests
./vendor/bin/pest

# Fix code style
./vendor/bin/pint

# Run development server
npm run dev
php artisan serve
```

## 🔒 Security

If you discover a security vulnerability, please email security@medsdn.com. All security vulnerabilities will be promptly addressed.

## 📝 License

MedSDN is open-source software licensed under the [MIT License](LICENSE).

## 🙏 Acknowledgments

MedSDN is built on top of [Bagisto](https://bagisto.com), an excellent open-source eCommerce framework. We're grateful to the Bagisto team and community for their foundational work.

## 📞 Support

- **Email**: support@medsdn.com
- **Forum**: [forums.medsdn.com](https://forums.medsdn.com) (Coming Soon)
- **Issues**: [GitHub Issues](https://github.com/medsdn/medsdn/issues)

## 🗺️ Roadmap

### Version 2.4 (Q2 2026)
- [ ] Prescription verification system
- [ ] Medical license validation
- [ ] Enhanced batch tracking
- [ ] HIPAA compliance tools

### Version 2.5 (Q3 2026)
- [ ] EHR integration
- [ ] Telemedicine integration
- [ ] Insurance claim processing
- [ ] Advanced drug interaction database

### Version 3.0 (Q4 2026)
- [ ] AI-powered drug recommendations
- [ ] Blockchain-based supply chain tracking
- [ ] Advanced analytics dashboard
- [ ] Mobile app enhancements

---

<p align="center">
  Made with ❤️ for Healthcare Professionals
</p>

<p align="center">
  <a href="https://medsdn.com">Website</a> •
  <a href="https://docs.medsdn.com">Documentation</a> •
  <a href="https://forums.medsdn.com">Community</a>
</p>
