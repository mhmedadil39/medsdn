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
- **GraphQL Endpoint**: `https://your-domain.com/graphql`
- **GraphQL Playground**: [https://your-domain.com/graphqli](https://api-demo.medsdn.com/api/graphiql?)

## Features

### Payment Methods

#### Bank Transfer Payment Method

Complete API support for bank transfer payment method with payment proof upload and admin review workflow.

**REST API Endpoints:**
- `GET /api/shop/bank-transfer/config` - Get bank transfer configuration
- `POST /api/shop/bank-transfer/upload` - Upload payment proof and create order
- `GET /api/shop/bank-transfer/payments` - Get customer's payments (authenticated)
- `GET /api/shop/bank-transfer/payments/{id}` - Get payment details (authenticated)
- `GET /api/shop/bank-transfer/statistics` - Get payment statistics (authenticated)

**GraphQL Operations:**
- Query: `bankTransferConfig` - Get configuration
- Query: `bankTransferPayments` - Get customer's payments
- Query: `bankTransferPayment(id)` - Get payment details
- Query: `bankTransferStatistics` - Get statistics
- Mutation: `uploadBankTransferPayment` - Upload payment proof

**Features:**
- Secure file upload (JPG, PNG, WEBP, PDF up to 4MB)
- Bank account information display
- Transaction reference tracking
- Payment status tracking (pending, approved, rejected)
- Rate limiting (5 uploads per minute)
- Multi-language support (English & Arabic)
- Comprehensive error handling

**Documentation:**
- REST API: See `packages/Webkul/BankTransfer/API_DOCUMENTATION.md`
- GraphQL API: See `packages/Webkul/MedsdnApi/BANK_TRANSFER_GRAPHQL.md`

**Example Usage:**

```bash
# Get configuration
curl -X GET "https://your-domain.com/api/shop/bank-transfer/config"

# Upload payment proof
curl -X POST "https://your-domain.com/api/shop/bank-transfer/upload" \
  -H "Authorization: Bearer {token}" \
  -F "payment_proof=@receipt.jpg" \
  -F "transaction_reference=TXN123456"
```

**GraphQL Example:**

```graphql
# Get configuration
query {
  bankTransferConfig {
    success
    data {
      title
      bankAccounts {
        bankName
        accountNumber
      }
    }
  }
}

# Upload payment proof
mutation {
  uploadBankTransferPayment(input: {
    paymentProof: $file
    transactionReference: "TXN123456"
    cartToken: "your_cart_token"
  }) {
    success
    message
    data {
      order {
        id
        incrementId
      }
      payment {
        id
        status
      }
    }
  }
}
```

## Documentation
- MedSDN API: [Demo Page](https://api-demo.medsdn.com/api) 
- API Documentation: [MedSDN API Docs](https://api-docs.medsdn.com/)
- GraphQL Playground: [Interactive Playground](https://api-demo.medsdn.com/graphiql)
- Bank Transfer REST API: `packages/Webkul/BankTransfer/API_DOCUMENTATION.md`
- Bank Transfer GraphQL API: `packages/Webkul/MedsdnApi/BANK_TRANSFER_GRAPHQL.md`
 
## Support

For issues and questions, please visit:
- [GitHub Issues](https://github.com/medsdn/medsdn-api-platform/issues)
- [MedSDN Documentation](https://medsdn.com/docs)
- [Community Forum](https://forum.medsdn.com)

## 📝 License

The MedSDN API Platform is open-source software licensed under the [MIT license](LICENSE).

 
