# دليل اختبار API في MedSDN

## التحقق من تحميل Service Providers

### 1. التحقق من Service Providers المسجلة

```bash
# عرض جميع Service Providers
php artisan about 2>&1 | grep -v "PHP Warning"

# أو بشكل أكثر تفصيلاً
php artisan list | grep -i "api\|graphql"
```

**ما يجب أن تراه:**
- `Webkul\BagistoApi\Providers\BagistoApiServiceProvider`
- `Webkul\GraphQLAPI\Providers\GraphQLAPIServiceProvider`

### 2. التحقق من Routes المسجلة

```bash
# عرض جميع routes الخاصة بالـ API
php artisan route:list --path=api 2>&1 | grep -v "PHP Warning"

# عرض GraphQL routes
php artisan route:list --path=graphql 2>&1 | grep -v "PHP Warning"
```

**ما يجب أن تراه:**
- `/api/shop/*` - Shop API endpoints
- `/api/admin/*` - Admin API endpoints  
- `/graphql` - GraphQL endpoint
- `/graphiql` - GraphQL Playground

## اختبار REST API

### 1. اختبار Shop API

#### الحصول على قائمة المنتجات
```bash
curl -X GET "http://localhost:8000/api/shop/products" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json"
```

#### الحصول على معلومات المتجر
```bash
curl -X GET "http://localhost:8000/api/shop/core/config" \
  -H "Accept: application/json"
```

### 2. اختبار Admin API

#### تسجيل دخول Admin
```bash
curl -X POST "http://localhost:8000/api/admin/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "admin123"
  }'
```

**النتيجة المتوقعة:**
```json
{
  "token": "your-jwt-token-here",
  "message": "Logged in successfully."
}
```

#### الحصول على قائمة المنتجات (Admin)
```bash
# استبدل YOUR_TOKEN بالـ token من الخطوة السابقة
curl -X GET "http://localhost:8000/api/admin/catalog/products" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## اختبار GraphQL API

### 1. الوصول إلى GraphQL Playground

افتح المتصفح وانتقل إلى:
```
http://localhost:8000/graphiql
```

**ما يجب أن تراه:**
- واجهة GraphQL Playground التفاعلية
- Documentation Explorer على الجانب
- محرر الاستعلامات

### 2. اختبار استعلام بسيط

في GraphQL Playground، جرب هذا الاستعلام:

```graphql
query {
  homeCategories {
    id
    name
    slug
    description
  }
}
```

### 3. اختبار استعلام المنتجات

```graphql
query {
  products(first: 10) {
    data {
      id
      name
      sku
      price
      formattedPrice
    }
    paginatorInfo {
      total
      currentPage
      lastPage
    }
  }
}
```

### 4. اختبار Mutation (تسجيل عميل جديد)

```graphql
mutation {
  customerRegister(
    input: {
      firstName: "Test"
      lastName: "User"
      email: "test@example.com"
      password: "password123"
      passwordConfirmation: "password123"
    }
  ) {
    status
    success
    accessToken
  }
}
```

## اختبار متقدم باستخدام Postman

### 1. استيراد Collection

إذا كان هناك ملف Postman collection في المشروع:
```bash
# ابحث عن ملف postman
find . -name "*.postman_collection.json"
```

### 2. إنشاء Collection يدوياً

#### Environment Variables
```json
{
  "base_url": "http://localhost:8000",
  "admin_token": "",
  "customer_token": ""
}
```

#### Request 1: Admin Login
- **Method**: POST
- **URL**: `{{base_url}}/api/admin/login`
- **Body** (JSON):
```json
{
  "email": "admin@example.com",
  "password": "admin123"
}
```
- **Tests** (لحفظ الـ token):
```javascript
pm.environment.set("admin_token", pm.response.json().token);
```

#### Request 2: Get Products
- **Method**: GET
- **URL**: `{{base_url}}/api/admin/catalog/products`
- **Headers**:
  - `Authorization`: `Bearer {{admin_token}}`
  - `Accept`: `application/json`

## التحقق من الأخطاء الشائعة

### 1. Service Provider غير محمل

**الأعراض:**
```
ERROR  There are no commands defined in the "bagisto-api-platform" namespace.
```

**الحل:**
```bash
# تحقق من bootstrap/providers.php
cat bootstrap/providers.php | grep -i "bagisto\|graphql"

# يجب أن ترى:
# Webkul\BagistoApi\Providers\BagistoApiServiceProvider::class,
# Webkul\GraphQLAPI\Providers\GraphQLAPIServiceProvider::class,
```

### 2. Routes غير موجودة

**الأعراض:**
```
404 Not Found
```

**الحل:**
```bash
# مسح cache الـ routes
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# إعادة تحميل
php artisan route:cache
```

### 3. Database غير جاهزة

**الأعراض:**
```
SQLSTATE[42S02]: Base table or view not found
```

**الحل:**
```bash
# تشغيل migrations
php artisan migrate --force
```

## اختبار شامل - Checklist

### ✅ Pre-requisites
- [ ] المشروع يعمل: `php artisan serve`
- [ ] Database متصلة ومهيأة
- [ ] Migrations تم تشغيلها
- [ ] Admin user موجود

### ✅ REST API Tests
- [ ] Shop API يستجيب: `GET /api/shop/products`
- [ ] Admin Login يعمل: `POST /api/admin/login`
- [ ] Admin API يستجيب مع token: `GET /api/admin/catalog/products`
- [ ] Error handling يعمل: محاولة الوصول بدون token

### ✅ GraphQL API Tests
- [ ] GraphQL Playground يفتح: `http://localhost:8000/graphiql`
- [ ] Schema Documentation متاحة
- [ ] Query بسيط يعمل: `homeCategories`
- [ ] Query معقد يعمل: `products` مع pagination
- [ ] Mutation يعمل: `customerRegister`

### ✅ Performance Tests
- [ ] Response time < 500ms للـ queries البسيطة
- [ ] Pagination تعمل بشكل صحيح
- [ ] Rate limiting يعمل (إذا مفعل)

## أدوات مساعدة

### 1. تفعيل API Logging

في `.env`:
```env
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

ثم راقب الـ logs:
```bash
tail -f storage/logs/laravel.log
```

### 2. تفعيل Query Logging

في `config/database.php`:
```php
'connections' => [
    'mysql' => [
        // ...
        'options' => [
            PDO::ATTR_EMULATE_PREPARES => true,
        ],
    ],
],
```

### 3. استخدام Laravel Telescope (اختياري)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

ثم افتح: `http://localhost:8000/telescope`

## نصائح للتطوير

### 1. استخدام API Documentation

إذا كانت متاحة:
```
http://localhost:8000/api/documentation
```

### 2. استخدام GraphQL Introspection

```graphql
query {
  __schema {
    types {
      name
      description
    }
  }
}
```

### 3. تفعيل CORS للتطوير

في `config/cors.php`:
```php
'paths' => ['api/*', 'graphql', 'graphiql'],
'allowed_origins' => ['*'],
```

## الخلاصة

للتأكد من أن كل شيء يعمل:

```bash
# 1. تشغيل المشروع
php artisan serve

# 2. في terminal آخر، اختبر REST API
curl http://localhost:8000/api/shop/products

# 3. افتح المتصفح
# http://localhost:8000/graphiql

# 4. جرب استعلام في GraphQL Playground
```

إذا نجحت جميع الاختبارات، فإن الـ API packages تعمل بشكل صحيح! ✅

---

**ملاحظة**: إذا واجهت أي مشاكل، راجع:
- `storage/logs/laravel.log` للأخطاء
- `php artisan route:list` للتحقق من الـ routes
- `php artisan about` للتحقق من الإعدادات
