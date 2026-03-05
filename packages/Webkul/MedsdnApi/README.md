# MedSDN API Platform

Comprehensive REST and GraphQL APIs for seamless e-commerce integration and extensibility.

## Installation

### Method 1: Quick Start (Composer Installation – Recommended)

The fastest way to get started:

```bash
composer require medsdn/medsdn-api
php artisan medsdn-api-platform:install
```

Your APIs are now ready! Access them at:
- **REST API Docs**: `https://your-domain.com/api/docs`
- **GraphQL Playground**: `https://your-domain.com/graphql`
 
### Method 2: Manual Installation

Use this method if you need more control over the setup.

#### Step 1: Download and Extract

1. Download the MedsdnApi package from [GitHub](https://github.com/medsdn/medsdn-api)
2. Extract it to: `packages/Webkul/MedsdnApi/`

#### Step 2: Register Service Provider

Edit `bootstrap/providers.php`:

```php
<?php

return [
    // ...existing providers...
    Webkul\MedsdnApi\Providers\MedsdnApiServiceProvider::class,
    // ...rest of providers...
];
```

#### Step 3: Update Autoloading

Edit `composer.json` and update the `autoload` section:

```json
{
  "autoload": {
    "psr-4": {
      "Webkul\\MedsdnApi\\": "packages/Webkul/MedsdnApi/src"
    }
  }
}
```

#### Step 4: Install Dependencies

```bash
# Install required packages
composer require api-platform/laravel:v4.1.25
composer require api-platform/graphql:v4.2.3
```

#### Step 5: Run the installation
```bash
php artisan medsdn-api-platform:install
```

#### Step 9: Environment Setup (Update in the .env)
```bash
STOREFRONT_DEFAULT_RATE_LIMIT=100
STOREFRONT_CACHE_TTL=60
STOREFRONT_KEY_PREFIX=storefront_key_
STOREFRONT_PLAYGROUND_KEY=pk_storefront_xxxxxxxxxxxxxxxxxxxxxxxxxx 
API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY=true
```
### Access Points

Once verified, access the APIs at:

- **REST API (Shop)**: [https://your-domain.com/api/shop/](https://api-demo.medsdn.com/api/shop)
- **REST API (Admin)**: [https://your-domain.com/api/admin/](https://api-demo.medsdn.com/api/admin)
- **GraphQL Endpoint**: https://your-domain.com/graphql`
- **GraphQL Playground**: [https://your-domain.com/graphqli](https://api-demo.medsdn.com/api/graphiql?)

## Documentation
- MedSDN API: [Demo Page](https://api-demo.medsdn.com/api) 
- API Documentation: [MedSDN API Docs](https://api-docs.medsdn.com/)
- GraphQL Playground: [Interactive Playground](https://api-demo.medsdn.com/graphiql)
 
## Support

For issues and questions, please visit:
- [GitHub Issues](https://github.com/medsdn/medsdn-api-platform/issues)
- [MedSDN Documentation](https://medsdn.com/docs)
- [Community Forum](https://forum.medsdn.com)

## 📝 License

The MedSDN API Platform is open-source software licensed under the [MIT license](LICENSE).

 
