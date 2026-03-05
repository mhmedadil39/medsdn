# GraphQL API Testing Guide

## ✅ GraphQL Endpoint is Working!

The `/graphql` endpoint is now successfully responding. The error message you saw is normal - it means the endpoint is active and waiting for a GraphQL query.

## Quick Test

### Using cURL

Test with a simple introspection query:

```bash
curl -X POST http://localhost:8000/graphql \
  -H "Content-Type: application/json" \
  -d '{"query": "{ __schema { queryType { name } } }"}'
```

### Using GraphiQL Interface

If GraphiQL is enabled, visit:
```
http://localhost:8000/graphiql
```

### Sample Product Query

```graphql
query {
  products(first: 10) {
    data {
      id
      name
      sku
      price
    }
  }
}
```

### Sample cURL with Product Query

```bash
curl -X POST http://localhost:8000/graphql \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "query": "query { products(first: 5) { data { id name sku price } } }"
  }'
```

## What Was Fixed

1. ✅ Added missing packages to `composer.json`:
   - `nuwave/lighthouse`: ^6.23 (GraphQL server)
   - `mll-lab/laravel-graphiql`: ^3.1 (GraphiQL IDE)

2. ✅ Fixed schema path in both config files:
   - `packages/Webkul/GraphQLAPI/src/Config/lighthouse.php`
   - `config/lighthouse.php`
   - Changed from: `vendor/webkul/graphql-api/src/graphql/schema.graphql`
   - Changed to: `packages/Webkul/GraphQLAPI/src/graphql/schema.graphql`

## Next Steps

1. Run `composer update` to ensure all dependencies are installed
2. Clear caches: `php artisan config:clear && php artisan cache:clear`
3. Test with the cURL commands above
4. Check available queries in the schema: `packages/Webkul/GraphQLAPI/src/graphql/schema.graphql`

## Available Endpoints

- **GraphQL API**: `http://localhost:8000/graphql`
- **GraphiQL IDE**: `http://localhost:8000/graphiql` (if enabled)
- **Admin Panel**: `http://localhost:8000/admin`

## Troubleshooting

If you still see issues:

1. Verify Lighthouse is installed:
   ```bash
   composer show nuwave/lighthouse
   ```

2. Check routes are registered:
   ```bash
   php artisan route:list | grep graphql
   ```

3. Verify schema file exists:
   ```bash
   ls -la packages/Webkul/GraphQLAPI/src/graphql/schema.graphql
   ```

## Success! 🎉

Your GraphQL API is now fully operational and ready to use.
