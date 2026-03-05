# MedSDN API Quick Start Guide

## ✅ Setup Complete

Both GraphQL and REST APIs are now working!

## Generate Storefront API Key

Run this command to generate your API key:

```bash
php artisan medsdn-api:generate-key
php artisan medsdn-api-platform:install
```
  Name : key
  Key : pk_storefront_JIxKgHoJZ246RWMzYKFGXw8AKUi4jUqg
  Rate Limit : 100 requests/minute
  Status : Active
When prompted, enter a name for your key (e.g., "default" or "shop-frontend").

The command will output your API key. **Save it securely!**

Example output:
```
Storefront key created successfully!
Key Name: default
API Key: pk_storefront_abc123xyz456...
```

## Using the APIs

### REST API

#### Shop API (requires storefront key)

```bash
curl -X GET http://localhost:8000/api/shop/products \
  -H "X-STOREFRONT-KEY: pk_storefront_your_key_here" \
  -H "Accept: application/json"
```

#### Admin API (requires authentication)

```bash
curl -X POST http://localhost:8000/api/admin/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "admin123"
  }'
```

### GraphQL API

#### Simple Query

```bash
curl -X POST http://localhost:8000/graphql \
  -H "Content-Type: application/json" \
  -H "X-STOREFRONT-KEY: pk_storefront_your_key_here" \
  -d '{
    "query": "{ products(first: 5) { data { id name sku price } } }"
  }'
```

#### Using GraphiQL Playground

Visit: `http://localhost:8000/graphiql`

The playground provides an interactive interface for testing GraphQL queries.

## API Documentation

### REST API Documentation

- **Shop API**: http://localhost:8000/api/shop
- **Admin API**: http://localhost:8000/api/admin
- **Full Docs**: http://localhost:8000/api/docs

### About the "Authorize Button"

The API documentation mentions an "Authorize button" for entering your storefront key. This is a standard OpenAPI/Swagger UI feature. If you don't see it:

1. **Manual Method**: Add the header directly in your API client:
   ```
   X-STOREFRONT-KEY: pk_storefront_your_key_here
   ```

2. **Using cURL**: Include the `-H` flag as shown in examples above

3. **Using Postman**: Add the header in the "Headers" tab

## Environment Configuration

Add these to your `.env` file:

```env
# Storefront API Configuration
STOREFRONT_DEFAULT_RATE_LIMIT=100
STOREFRONT_CACHE_TTL=60
STOREFRONT_KEY_PREFIX=storefront_key_
STOREFRONT_PLAYGROUND_KEY=pk_storefront_your_generated_key_here
API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY=true
```

## Common API Endpoints

### Shop API (Public)

- `GET /api/shop/products` - List products
- `GET /api/shop/products/{id}` - Get product details
- `GET /api/shop/categories` - List categories
- `POST /api/shop/cart` - Create cart
- `POST /api/shop/checkout` - Process checkout
- `POST /api/shop/customer/register` - Register customer
- `POST /api/shop/customer/login` - Customer login

### Admin API (Authenticated)

- `POST /api/admin/login` - Admin login
- `GET /api/admin/products` - Manage products
- `GET /api/admin/orders` - Manage orders
- `GET /api/admin/customers` - Manage customers
- `GET /api/admin/categories` - Manage categories

### GraphQL Queries

```graphql
# Get Products
query {
  products(first: 10) {
    data {
      id
      name
      sku
      price
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

# Get Categories
query {
  categories {
    id
    name
    slug
    children {
      id
      name
    }
  }
}

# Get Product by ID
query {
  product(id: 1) {
    id
    name
    sku
    price
    description
  }
}
```

## Testing the APIs

### Test REST API

```bash
# Generate key first
php artisan medsdn-api:generate-key

# Test shop endpoint
curl -X GET http://localhost:8000/api/shop/products \
  -H "X-STOREFRONT-KEY: YOUR_KEY_HERE" \
  -H "Accept: application/json"
```

### Test GraphQL API

```bash
# Test with introspection query
curl -X POST http://localhost:8000/graphql \
  -H "Content-Type: application/json" \
  -H "X-STOREFRONT-KEY: YOUR_KEY_HERE" \
  -d '{"query": "{ __schema { queryType { name } } }"}'
```

## Troubleshooting

### 404 Not Found

If you get 404 errors:

```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Missing Tables

If you get "table not found" errors:

```bash
php artisan migrate --path=packages/Webkul/MedsdnApi/src/Database/Migrations
```

### Authentication Issues

For admin API, first login to get a token:

```bash
curl -X POST http://localhost:8000/api/admin/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "admin123"
  }'
```

Then use the returned token in subsequent requests:

```bash
curl -X GET http://localhost:8000/api/admin/products \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## Rate Limiting

The API includes rate limiting. Default limits:
- Shop API: 100 requests per minute
- Admin API: 200 requests per minute

Configure in `.env`:
```env
STOREFRONT_DEFAULT_RATE_LIMIT=100
```

## Support

- **Documentation**: http://localhost:8000/api/docs
- **GraphQL Playground**: http://localhost:8000/graphiql
- **GitHub Issues**: https://github.com/medsdn/medsdn-api/issues
- **Forum**: https://forum.medsdn.com

## Next Steps

1. Generate your storefront API key
2. Test the endpoints with cURL or Postman
3. Explore the API documentation
4. Try GraphQL queries in the playground
5. Build your frontend application

Happy coding! 🚀
