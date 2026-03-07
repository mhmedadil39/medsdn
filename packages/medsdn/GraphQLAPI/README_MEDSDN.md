# MedSDN GraphQL API

Powerful GraphQL API for MedSDN eCommerce platform, providing flexible and efficient data querying for medical and healthcare products.

## Features

- 🚀 Complete GraphQL API for shop and admin operations
- 🔐 JWT-based authentication
- 📱 Mobile-ready endpoints
- 🏥 Medical product support
- 🔍 Advanced filtering and search
- 📊 Real-time data queries
- 🎯 Type-safe schema

## Installation

### Requirements
- PHP 8.2+
- Laravel 11+
- MedSDN 2.3+

### Steps

1. **Install via Composer** (if published)
```bash
composer require medsdn/graphql-api
```

2. **Or use local package** (already included in MedSDN)
The package is already included in `packages/medsdn/GraphQLAPI/`

3. **Register Service Provider**

Add to `bootstrap/providers.php`:
```php
Webkul\GraphQLAPI\Providers\GraphQLAPIServiceProvider::class,
```

4. **Publish Configuration**
```bash
php artisan vendor:publish --provider="Webkul\GraphQLAPI\Providers\GraphQLAPIServiceProvider"
```

5. **Run Migrations**
```bash
php artisan migrate
```

## Usage

### GraphQL Playground

Access the interactive GraphQL playground:
```
http://your-domain.com/graphiql
```

### GraphQL Endpoint

Send queries to:
```
http://your-domain.com/graphql
```

## Example Queries

### Get Products

```graphql
query {
  products(first: 10) {
    data {
      id
      name
      sku
      price
      formattedPrice
      description
      images {
        url
      }
    }
    paginatorInfo {
      total
      currentPage
      lastPage
    }
  }
}
```

### Get Categories

```graphql
query {
  homeCategories {
    id
    name
    slug
    description
    children {
      id
      name
      slug
    }
  }
}
```

### Customer Registration

```graphql
mutation {
  customerRegister(
    input: {
      firstName: "John"
      lastName: "Doe"
      email: "john@example.com"
      password: "password123"
      passwordConfirmation: "password123"
    }
  ) {
    status
    success
    accessToken
    customer {
      id
      firstName
      lastName
      email
    }
  }
}
```

### Customer Login

```graphql
mutation {
  customerLogin(
    input: {
      email: "john@example.com"
      password: "password123"
    }
  ) {
    status
    success
    accessToken
    customer {
      id
      firstName
      lastName
      email
    }
  }
}
```

### Add to Cart

```graphql
mutation {
  addItemToCart(
    input: {
      productId: 1
      quantity: 2
    }
  ) {
    success
    message
    cart {
      id
      itemsCount
      grandTotal
      formattedGrandTotal
    }
  }
}
```

## Medical Features

### Query Products with Medical Attributes

```graphql
query {
  products(
    input: {
      categoryId: 1
      filters: {
        requiresPrescription: true
      }
    }
  ) {
    data {
      id
      name
      sku
      requiresPrescription
      batchNumber
      expiryDate
      manufacturer
    }
  }
}
```

### Upload Prescription (Planned)

```graphql
mutation {
  uploadPrescription(
    input: {
      customerId: 1
      file: "base64_encoded_file"
      productIds: [1, 2, 3]
    }
  ) {
    success
    message
    prescription {
      id
      status
      expiryDate
    }
  }
}
```

## Authentication

### Using JWT Token

After login, include the token in your requests:

**Header:**
```
Authorization: Bearer YOUR_JWT_TOKEN
```

**Example with cURL:**
```bash
curl -X POST http://your-domain.com/graphql \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{"query":"{ customer { id firstName lastName } }"}'
```

## Configuration

### Environment Variables

```env
# GraphQL Settings
GRAPHQL_PLAYGROUND_ENABLED=true
GRAPHQL_DEBUG=true
GRAPHQL_SCHEMA_CACHE=false

# JWT Settings
JWT_SECRET=your-secret-key
JWT_TTL=60
```

### Config File

Edit `config/lighthouse.php` for advanced configuration.

## Schema Documentation

The GraphQL schema is self-documenting. Use the GraphQL Playground to explore:

1. Open `http://your-domain.com/graphiql`
2. Click "Docs" on the right side
3. Browse available queries, mutations, and types

## Error Handling

GraphQL returns errors in a structured format:

```json
{
  "errors": [
    {
      "message": "Validation failed",
      "extensions": {
        "category": "validation",
        "validation": {
          "email": ["The email field is required."]
        }
      }
    }
  ]
}
```

## Performance

### Query Complexity

Queries are limited by complexity to prevent abuse:
- Maximum depth: 10
- Maximum complexity: 1000

### Caching

Enable query caching in production:
```env
GRAPHQL_SCHEMA_CACHE=true
```

## Testing

### Run Tests

```bash
# Run all GraphQL tests
php artisan test --filter=GraphQL

# Run specific test
php artisan test tests/Feature/GraphQL/ProductTest.php
```

### Example Test

```php
public function test_can_query_products()
{
    $response = $this->graphQL('
        query {
            products(first: 5) {
                data {
                    id
                    name
                }
            }
        }
    ');

    $response->assertJson([
        'data' => [
            'products' => [
                'data' => []
            ]
        ]
    ]);
}
```

## Troubleshooting

### Common Issues

**Issue: GraphQL endpoint returns 404**
```bash
# Clear route cache
php artisan route:clear
php artisan config:clear
```

**Issue: Schema not found**
```bash
# Verify schema path in config/lighthouse.php
# Default: vendor/medsdn/graphql-api/src/graphql/schema.graphql
```

**Issue: Authentication fails**
```bash
# Check JWT configuration
php artisan jwt:secret
```

## Migration from Bagisto

If migrating from Bagisto GraphQL API:

1. All queries remain compatible
2. Update package name in composer.json
3. Update namespace references if customized
4. Test all custom queries

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Write tests for new features
4. Submit a pull request

## Support

- **Documentation**: [docs.medsdn.com/graphql](https://docs.medsdn.com/graphql)
- **Issues**: [GitHub Issues](https://github.com/medsdn/medsdn/issues)
- **Email**: support@medsdn.com
- **Security**: security@medsdn.com

## License

MIT License - see LICENSE file for details

## Credits

Built on top of:
- [Lighthouse PHP](https://lighthouse-php.com/) - GraphQL server for Laravel
- [GraphQL](https://graphql.org/) - Query language for APIs
- Original Bagisto GraphQL API

---

**MedSDN** - Medical & Healthcare eCommerce Platform
