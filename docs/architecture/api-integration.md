# MedSDN API Integration Guide

## Purpose

هذه الوثيقة تشرح كيف يرتبط سطحا الـ API في المشروع بالحزم الأساسية:

- `MedsdnApi`
- `GraphQLAPI`

والفرق بينهما من زاوية المعمارية، التهيئة، ومصادر الدومين التي يستهلكانها.

## Two API Stacks

### MedsdnApi

- provider الرئيسي:
  - `packages/medsdn/MedsdnApi/src/Providers/MedsdnApiServiceProvider.php`
- التقنية:
  - `API Platform`
  - `API Platform GraphQL`
- النمط:
  - `ApiResource` classes داخل `src/Models`
  - `State` providers/processors
  - custom resolvers لبعض سيناريوهات GraphQL
  - middleware خاص بالمفاتيح والتوثيق والـ rate limiting
- surface:
  - REST
  - GraphQL
  - OpenAPI docs
  - playground/docs endpoints

### GraphQLAPI

- provider الرئيسي:
  - `packages/medsdn/GraphQLAPI/src/Providers/GraphQLAPIServiceProvider.php`
- التقنية:
  - `Lighthouse`
  - `GraphiQL`
- النمط:
  - GraphQL schema/queries/mutations/types/listeners/services
  - overrides لبعض models/controllers
  - admin extras مثل push notifications
- surface:
  - GraphQL only
  - admin/storefront GraphQL adjunct features

## Core Architectural Difference

- `MedsdnApi` يعامل الـ API كطبقة موارد عامة فوق الدومين:
  - resource model
  - provider
  - processor
  - middleware
  - serializer
  - docs
- `GraphQLAPI` يعامل الـ API كطبقة GraphQL تطبيقية خاصة:
  - schema-first behavior
  - mutations/queries مباشرة
  - model/controller overrides
  - event-driven cache/push integrations

## Runtime Entry Points

### MedsdnApi Entry Points

- routes مسجلة مباشرة داخل provider:
  - `/api`
  - `/api/shop`
  - `/api/admin`
  - `/api/shop/docs`
  - `/api/admin/docs`
  - `/api/graphiql`
  - `/api/graphql`
  - `/admin/graphiql`
- middleware aliases:
  - `storefront.key`
  - `VerifyStorefrontKey`
  - `VerifyGraphQLStorefrontKey`
  - `VerifyBearerToken`
  - `SetLocaleChannel`
  - `ForceApiJson`
  - `RateLimitApi`
- commands:
  - `medsdn-api-platform:install`
  - `medsdn-api:generate-key`
  - `medsdn-api:key:manage`
  - `medsdn-api:key:maintain`

### GraphQLAPI Entry Points

- routes:
  - `packages/medsdn/GraphQLAPI/src/Routes/web.php`
  - `packages/medsdn/GraphQLAPI/src/Routes/fcm-routes.php`
- commands:
  - `medsdn-graphql:install`
- config:
  - `lighthouse.php`
  - `graphiql.php`
  - `acl.php`
  - `menu.php`
  - `system.php`

## Domain Reuse Matrix

| Domain Package | MedsdnApi | GraphQLAPI | How It Is Used |
|---|---|---|---|
| `Core` | Yes | Yes | shared helpers, config, infrastructure |
| `Attribute` | Yes | Yes | resource/query/filter metadata |
| `Category` | Yes | Yes | tree queries, storefront navigation, SEO |
| `Product` | Yes | Yes | primary catalog payloads and mutations |
| `Inventory` | Indirect | Indirect | stock/salable data through product/order flows |
| `Tax` | Yes | Yes | totals and pricing context |
| `Checkout` | Yes | Yes | cart and checkout orchestration |
| `Customer` | Yes | Yes | auth/profile/address/wishlist/compare |
| `Sales` | Yes | Yes | orders, invoices, shipments, reorder/cancel |
| `Payment` | Yes | Yes | available payment methods |
| `Shipping` | Yes | Yes | shipping rates and checkout shipping |
| `Theme` | Yes | Limited | theme customization and storefront context |
| `CMS` | Yes | Limited | pages/content exposure |
| `BankTransfer` | Yes | Limited | bank transfer APIs and payment proof flow |
| `Admin` | Indirect | Stronger | admin GraphQL extensions and ACL/menu integration |
| `Shop` | Behavioral source | Stronger | existing storefront/controller behavior reused or overridden |

## MedsdnApi Integration by Package

### Product

- key classes:
  - `src/Models/Product.php`
  - `src/State/ProductMedsdnApiProvider.php`
  - `src/State/ProductGraphQLProvider.php`
  - `src/Resolver/SingleProductMedsdnApiResolver.php`
  - `src/Resolver/ProductCollectionResolver.php`
- integration style:
  - يستهلك product domain model and relations
  - يضيف `ApiResource` descriptors وserialization for REST/GraphQL
  - يفصل بين collection/item resolution وwrite processors

### Checkout

- key classes:
  - `src/State/CartTokenProcessor.php`
  - `src/State/CartTokenMutationProvider.php`
  - `src/State/CheckoutProcessor.php`
  - `src/State/CheckoutAddressProvider.php`
  - `src/State/GetCheckoutAddressCollectionProvider.php`
- integration style:
  - لا يعيد بناء cart domain
  - يضيف guest cart token layer فوق `Webkul\Checkout\Repositories\CartRepository`
  - يدير auth/guest crossover عبر `CartTokenService`

### Customer

- key classes:
  - `src/State/AuthenticatedCustomerProvider.php`
  - `src/State/CustomerProcessor.php`
  - `src/State/CustomerProfileProcessor.php`
  - `src/State/CustomerAddressProvider.php`
  - `src/State/WishlistProvider.php`
  - `src/State/CompareItemProvider.php`
- integration style:
  - reuse مباشر لـ customer domain + JWT/bearer auth layer
  - expose account operations as `ApiResource` mutations/queries

### Sales

- key classes:
  - `src/State/CustomerOrderProvider.php`
  - `src/State/CustomerInvoiceProvider.php`
  - `src/State/CustomerOrderShipmentProvider.php`
  - `src/State/CancelOrderProcessor.php`
  - `src/State/ReorderProcessor.php`
- integration style:
  - customer-facing order history and actions
  - لا توجد إعادة صياغة لمنطق order lifecycle، فقط API orchestration فوق repositories/models

### Category and Attribute

- key classes:
  - `CategoryTreeProvider`
  - `AttributeCollectionProvider`
  - `AttributeOptionCollectionProvider`
  - `FilterableAttributesProvider`
- integration style:
  - expose catalog metadata for storefront filters/navigation/search

### Payment and Shipping

- key classes:
  - `PaymentMethodsProvider`
  - `ShippingRatesProvider`
  - `CheckoutPaymentMethod`, `ShippingRates`, `EstimateShipping`
- integration style:
  - read-only and checkout-bound resources فوق domain services الحالية

### Theme and CMS

- key classes:
  - `ThemeCustomization`, `ThemeCustomizationTranslation`
  - `Page`, `PageTranslation`
- integration style:
  - expose storefront presentation/config content عبر API

### BankTransfer

- key classes:
  - `BankTransferPayment`
  - `BankTransferConfigProvider`
  - `BankTransferPaymentProvider`
  - `BankTransferPaymentProcessor`
- integration style:
  - extension API مخصص فوق payment method plugin

## GraphQLAPI Integration by Package

### Admin

- GraphQLAPI يدمج نفسه مع `Admin` مباشرة عبر:
  - `Config/menu.php`
  - `Config/acl.php`
  - `Config/system.php`
  - `Routes/fcm-routes.php`
- النتيجة:
  - عناصر إدارة جديدة داخل الـ sidebar/system config
  - push notification management في لوحة الإدارة

### Customer / Wishlist / CartRule

- `GraphQLAPIServiceProvider` يoverride:
  - `Webkul\User\Contracts\Admin`
  - `Webkul\Customer\Contracts\Customer`
  - `Webkul\CartRule\Contracts\CartRule`
- كما يعيد bind:
  - `Webkul\Shop\Http\Controllers\API\WishlistController`
- هذا يعني أن stack الـ GraphQL هنا ليس طبقة قراءة فقط، بل يتدخل في تنفيذ بعض flows.

### Product / Checkout / Sales

- الحزمة تحتوي عددًا كبيرًا من:
  - `Queries`
  - `Mutations`
  - `Types`
  - `Services`
- integration style:
  - schema-first GraphQL operations calling domain repositories/models مباشرة
  - أقل فصلًا من `MedsdnApi` بين resource metadata وbusiness orchestration

### Cache / Event Integration

- `GraphQLAPI` يضيف listeners وخدمات cache مثل:
  - `Listeners/ClearCache.php`
  - `Services/GraphQLCacheService.php`
- هذا يربطه strongly مع domain events من wishlist/product/category/marketing وغيرها.

## Installer Linkage

- `Installer` يعتبر الحزمتين APIs optional runtime layers تحتاج bootstrap إضافي:
  - `GraphQLAPI`:
    - `medsdn-graphql:install`
  - `MedsdnApi`:
    - `medsdn-api-platform:install`
    - `medsdn-api:generate-key`
- المرجع:
  - `packages/medsdn/Installer/src/Support/PackageBootstrap/PackageBootstrapRegistry.php`

## Which Stack To Read First

### If you are debugging storefront REST/GraphQL now

- ابدأ بـ `MedsdnApi`
- ثم:
  - provider
  - target `ApiResource`
  - corresponding `State` class
  - corresponding domain repository/model in the owning package

### If you are debugging legacy/custom GraphQL behavior

- ابدأ بـ `GraphQLAPI`
- ثم:
  - provider
  - route/config
  - mutation/query/type
  - referenced repository/model/controller override

## Practical Reading Paths

### Product API path

1. `packages/medsdn/MedsdnApi/src/Models/Product.php`
2. `packages/medsdn/MedsdnApi/src/State/ProductMedsdnApiProvider.php`
3. `packages/medsdn/MedsdnApi/src/Resolver/SingleProductMedsdnApiResolver.php`
4. `packages/medsdn/Product/src/*`

### Cart / checkout API path

1. `packages/medsdn/MedsdnApi/src/State/CartTokenProcessor.php`
2. `packages/medsdn/MedsdnApi/src/Services/CartTokenService.php`
3. `packages/medsdn/Checkout/src/*`
4. `packages/medsdn/Sales/src/*`

### Legacy GraphQL path

1. `packages/medsdn/GraphQLAPI/src/Providers/GraphQLAPIServiceProvider.php`
2. `packages/medsdn/GraphQLAPI/src/Routes/web.php`
3. relevant `Queries`/`Mutations`
4. target package repositories/models
