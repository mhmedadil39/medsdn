<!-- gitnexus:start -->
# GitNexus MCP

This project is indexed by GitNexus as **medsdn** (17220 symbols, 42222 relationships, 300 execution flows).

## Always Start Here

1. **Read `gitnexus://repo/{name}/context`** — codebase overview + check index freshness
2. **Match your task to a skill below** and **read that skill file**
3. **Follow the skill's workflow and checklist**

> If step 1 warns the index is stale, run `npx gitnexus analyze` in the terminal first.

## Skills

| Task | Read this skill file |
|------|---------------------|
| Understand architecture / "How does X work?" | `.claude/skills/gitnexus/gitnexus-exploring/SKILL.md` |
| Blast radius / "What breaks if I change X?" | `.claude/skills/gitnexus/gitnexus-impact-analysis/SKILL.md` |
| Trace bugs / "Why is X failing?" | `.claude/skills/gitnexus/gitnexus-debugging/SKILL.md` |
| Rename / extract / split / refactor | `.claude/skills/gitnexus/gitnexus-refactoring/SKILL.md` |
| Tools, resources, schema reference | `.claude/skills/gitnexus/gitnexus-guide/SKILL.md` |
| Index, status, clean, wiki CLI commands | `.claude/skills/gitnexus/gitnexus-cli/SKILL.md` |

<!-- gitnexus:end -->

# MedSDN Package Architecture Reference

## Scope

- مجلد الحزم الفعلي هو `packages/medsdn/*` وليس `packages/Webkul/*`.
- أغلب namespaces ما زالت تشغيلًا تحت `Webkul\\...` رغم انتقال المسارات إلى `packages/medsdn`.
- الحزم تُحمَّل صراحة من `bootstrap/providers.php`، بينما autoload الرئيسي موجود في `composer.json`.
- الحزم التي لا تحتوي `composer.json` هي modules داخلية للمشروع وليست path packages مستقلة للنشر.

## Runtime Loading Model

- `bootstrap/providers.php` هو نقطة التحميل الفعلية لكل الحزم الأساسية وحزمتَي الـ API.
- `composer.json` في الجذر يربط `Webkul\\<Package>\\` إلى `packages/medsdn/<Package>/src`.
- النمط المتكرر لكل package:
  - `<Package>ServiceProvider`: boot/register للحزمة.
  - `ModuleServiceProvider`: تسجيل Models عبر Concord حين تكون الحزمة domain package.
  - `EventServiceProvider`: listeners/observers/event wiring للحزم التي تعتمد على events.
- `Webkul\Core\Providers\CoreModuleServiceProvider` هو الأساس المشترك لتسجيل نماذج Concord داخل أغلب الحزم التجارية.

## Package Groups

- Foundation / Platform:
  - `Core`, `User`, `Theme`, `DataGrid`, `Installer`, `Admin`, `Shop`
- Catalog & Commerce:
  - `Attribute`, `Category`, `Product`, `Inventory`, `Tax`, `Rule`, `CatalogRule`, `CartRule`, `Checkout`, `Sales`, `Shipping`, `Payment`, `Paypal`, `BankTransfer`, `BookingProduct`
- Experience / Content / Extensions:
  - `Customer`, `CMS`, `Marketing`, `Notification`, `DataTransfer`, `GDPR`, `FPC`, `Sitemap`, `SocialLogin`, `SocialShare`, `MagicAI`, `DebugBar`
- API Surface:
  - `MedsdnApi`, `GraphQLAPI`

## API Linkage Model

- `MedsdnApi` هو سطح الـ API الأوسع والأحدث:
  - مبني على `api-platform/laravel` و`api-platform/graphql`
  - يعرف `ApiResource` classes داخل `packages/medsdn/MedsdnApi/src/Models`
  - يربط الموارد إلى `State` providers/processors و`Resolver` classes
  - يستهلك repositories/models/contracts من الحزم التجارية الأساسية بدل إعادة تنفيذ الدومين
  - يضيف middleware خاص بالمفاتيح (`VerifyStorefrontKey`, `VerifyGraphQLStorefrontKey`) وواجهات docs/playground
- `GraphQLAPI` هو سطح GraphQL مستقل/قديم نسبيًا:
  - مبني على `Lighthouse` و`GraphiQL`
  - يضيف mutations/queries/schema/config خاص به
  - يoverride بعض النماذج والعقود عبر Concord وبعض controllers بدل استعمال API Platform
  - يضيف وظائف جانبية مثل push notifications وgraphql cache events
- `Shop` و`Admin` ليسا packages API، لكنهما المصدر الوظيفي الرئيسي الذي تستهلكه حزمتا الـ API:
  - `Admin` يوفّر ACL/menu/system/data grids وعمليات الإدارة
  - `Shop` يوفّر storefront routes وcheckout/customer flows
- `Installer` يربط الحزم بالـ API bootstrap:
  - `GraphQLAPI` عبر `medsdn-graphql:install`
  - `MedsdnApi` عبر `medsdn-api-platform:install` ثم `medsdn-api:generate-key`
  - `Product` و`CatalogRule` عبر index bootstrapping حتى تصبح البيانات جاهزة للـ API

## Package-by-Package Reference

### Admin

- المسؤولية:
  - لوحة الإدارة، ACL، القوائم، صفحات الإعدادات والمبيعات والعملاء والتقارير.
- نقاط الدخول:
  - `packages/medsdn/Admin/src/Providers/AdminServiceProvider.php`
  - routes كثيرة تحت `packages/medsdn/Admin/src/Routes/*.php`
  - config في `Config/acl.php`, `Config/menu.php`, `Config/system.php`
- الربط:
  - يعتمد على `Core` للـ helpers/menu/config وعلى `DataGrid` لعرض الجداول.
  - يستهلك domain packages كلها تقريبًا عبر controllers/data grids.
  - `GraphQLAPI` يدمج config إضافية داخل `menu.admin` و`acl` ويضيف admin push-notification UI.
  - `MedsdnApi` لا يعتمد على views الخاصة به، لكنه يعكس نفس الدومين الإداري عبر موارد API منفصلة.

### Attribute

- المسؤولية:
  - تعريف attributes, families, groups, options والترجمات الخاصة بها.
- نقاط الدخول:
  - `packages/medsdn/Attribute/src/Providers/AttributeServiceProvider.php`
  - `packages/medsdn/Attribute/src/Providers/ModuleServiceProvider.php`
- الربط:
  - أساس `Product`, `Category`, `DataTransfer`, `CatalogRule`, وواجهات Admin catalog.
  - `MedsdnApi` يعرّض `Attribute`, `AttributeOption`, `AttributeValue`, `AttributeFamily` كموارد API.
  - `GraphQLAPI` يستهلكه في schema/resolvers الخاصة بالمنتجات والفلاتر.

### BankTransfer

- المسؤولية:
  - وسيلة دفع تحويل بنكي مع admin/shop/api routes خاصة بها.
- نقاط الدخول:
  - `packages/medsdn/BankTransfer/src/Providers/BankTransferServiceProvider.php`
  - routes: `admin-routes.php`, `shop-routes.php`, `api-routes.php`
  - config: `payment-methods.php`, `banktransfer.php`, `menu.php`, `acl.php`, `system.php`
- الربط:
  - يعتمد على `Payment`, `Checkout`, `Sales`, `Customer`.
  - يضيف جداول/Jobs لمتابعة إثباتات الدفع.
  - `MedsdnApi` يملك موردًا مباشرًا `BankTransferPayment` مع providers/processors مخصصين.

### BookingProduct

- المسؤولية:
  - نماذج ومنطق المنتجات القابلة للحجز وجدولة مخزون/availability.
- نقاط الدخول:
  - `packages/medsdn/BookingProduct/src/Providers/BookingProductServiceProvider.php`
  - `ModuleServiceProvider` و`EventServiceProvider`
- الربط:
  - يتقاطع مع `Product`, `Checkout`, `Sales`.
  - يظهر في Admin/Shop كنوع منتج متخصص.
  - `MedsdnApi` يعرّض `BookingProduct` ضمن موارده لتوسيع product API.

### CMS

- المسؤولية:
  - الصفحات الثابتة ومحتوى CMS.
- نقاط الدخول:
  - `packages/medsdn/CMS/src/Providers/CMSServiceProvider.php`
  - `ModuleServiceProvider`
- الربط:
  - يستهلكه `Admin` للإدارة و`Shop` للعرض.
  - `Marketing`, `Sitemap`, و`FPC` تعتمد عليه في rewrite/cache/sitemap/content rendering.
  - `MedsdnApi` يعرّض `Page` و`PageTranslation`.

### CartRule

- المسؤولية:
  - قواعد الخصم على السلة والقسائم.
- نقاط الدخول:
  - `packages/medsdn/CartRule/src/Providers/CartRuleServiceProvider.php`
  - `EventServiceProvider`, `ModuleServiceProvider`
- الربط:
  - يعتمد على `Rule`, `Checkout`, `Customer`, `Tax`, و`Sales`.
  - ينعكس على checkout totals في `Shop` و`Admin`.
  - `GraphQLAPI` و`MedsdnApi` يستهلكان نتائجه داخل cart/checkout flows بدل منطق منفصل.

### CatalogRule

- المسؤولية:
  - قواعد تسعير الكتالوج والفهرسة السعرية قبل الشراء.
- نقاط الدخول:
  - `packages/medsdn/CatalogRule/src/Providers/CatalogRuleServiceProvider.php`
  - `EventServiceProvider`, `ModuleServiceProvider`
- الربط:
  - يعتمد على `Rule`, `Product`, `Category`, `Attribute`, `Customer`, `Tax`.
  - يضيف indexing jobs ويحتاج `product:price-rule:index` عند التثبيت.
  - يؤثر مباشرة على أسعار `Shop`, `Admin`, و`MedsdnApi` product outputs.

### Category

- المسؤولية:
  - شجرة الفئات، الترجمات، الربط مع المنتجات.
- نقاط الدخول:
  - `packages/medsdn/Category/src/Providers/CategoryServiceProvider.php`
  - `ModuleServiceProvider`
- الربط:
  - أساس navigation/storefront/filtering وadmin category management.
  - `Marketing`, `Sitemap`, `FPC`, `Product`, `Shop` تعتمد عليه.
  - `MedsdnApi` يعرّض `Category`, `CategoryTranslation`, و`CategoryTreeProvider`.

### Checkout

- المسؤولية:
  - cart models, addresses, totals, payment/shipping selection, checkout lifecycle.
- نقاط الدخول:
  - `packages/medsdn/Checkout/src/Providers/CheckoutServiceProvider.php`
  - `EventServiceProvider`, `ModuleServiceProvider`
- الربط:
  - محور أساسي بين `Product`, `Customer`, `Shipping`, `Payment`, `Tax`, `Sales`.
  - `Shop` يستخدمه مباشرة في checkout-routes.
  - `MedsdnApi` يبني فوقه cart token, estimate shipping, checkout processors.
  - `GraphQLAPI` يستهلكه في cart/order mutations.

### Core

- المسؤولية:
  - الأساس المشترك: helpers, repositories, config system, channels/locales/currencies, search, visitors, maintenance overrides, Blade compiler overrides.
- نقاط الدخول:
  - `packages/medsdn/Core/src/Providers/CoreServiceProvider.php`
  - `CoreModuleServiceProvider`, `EventServiceProvider`, `ImageServiceProvider`, `VisitorServiceProvider`
- الربط:
  - جميع الحزم تقريبًا تعتمد عليه.
  - يوفر commands مركزية مثل `medsdn:version`, `medsdn:translations:check`, `exchange-rate:update`, `invoice:cron`.
  - يحقن middleware/exception/blade/search hooks التي تعتمد عليها `Admin`, `Shop`, و`API`.

### Customer

- المسؤولية:
  - العملاء، العناوين، wishlist, compare, مراجعات الحساب وارتباطات storefront.
- نقاط الدخول:
  - `packages/medsdn/Customer/src/Providers/CustomerServiceProvider.php`
  - `ModuleServiceProvider`
- الربط:
  - يتكامل مع `Shop`, `Checkout`, `Sales`, `Product`.
  - `SocialLogin` و`GraphQLAPI` و`MedsdnApi` يبنون فوق نماذجه وتوكنات/عمليات الحساب.
  - `MedsdnApi` يعرّض customer profile, addresses, wishlist, compare, downloadable assets, orders, invoices.

### DataGrid

- المسؤولية:
  - بنية الجداول المشتركة للإدارة مع filters/actions/pagination.
- نقاط الدخول:
  - `packages/medsdn/DataGrid/src/Providers/DataGridServiceProvider.php`
  - `ModuleServiceProvider`
- الربط:
  - `Admin` و`GraphQLAPI` يعتمدـان عليه لصفحات الإدارة التي تعرض قوائم.
  - ليس package domain بحد ذاته، بل UI/infra layer للإدارة.

### DataTransfer

- المسؤولية:
  - الاستيراد/التصدير وربط importers بالموديلات.
- نقاط الدخول:
  - `packages/medsdn/DataTransfer/src/Providers/DataTransferServiceProvider.php`
  - config: `Config/importers.php`
- الربط:
  - يستهلك `Attribute`, `Category`, `Customer`, `Inventory`, `Product`, `Tax`.
  - `Admin` يستخدمه لواجهات data import/export.
  - لا يضيف API surface مباشر، لكنه يؤثر على البيانات التي تراها `MedsdnApi` و`GraphQLAPI`.

### DebugBar

- المسؤولية:
  - دمج debug tooling في بيئات التطوير.
- نقاط الدخول:
  - `packages/medsdn/DebugBar/src/Providers/DebugBarServiceProvider.php`
- الربط:
  - package تطوير داخلي فقط؛ لا يمثل دومين تجاري.

### FPC

- المسؤولية:
  - full-page cache وربط invalidation مع storefront entities.
- نقاط الدخول:
  - `packages/medsdn/FPC/src/Providers/FPCServiceProvider.php`
  - `EventServiceProvider`
- الربط:
  - يعتمد على `Shop`, `Theme`, `CMS`, `Category`, `Product`, `Marketing`.
  - يعالج caching للمتجر التقليدي؛ `MedsdnApi` لديه cache profile منفصل (`ApiAwareResponseCache`) داخل حزمتـه.

### GDPR

- المسؤولية:
  - GDPR requests/data handling.
- نقاط الدخول:
  - `packages/medsdn/GDPR/src/Providers/GDPRServiceProvider.php`
  - `ModuleServiceProvider`
- الربط:
  - يظهر في `Admin` و`Customer` flows.
  - `GraphQLAPI` يدمج ACL/menu/system config إضافية مرتبطة به.
  - لا يملك surface API واسع منفصل، لكن يمكن أن تستهلكه APIs عبر customer/account policies.

### GraphQLAPI

- المسؤولية:
  - GraphQL stack مبني على Lighthouse + GraphiQL + custom mutations/queries/cache/push notifications.
- نقاط الدخول:
  - `packages/medsdn/GraphQLAPI/src/Providers/GraphQLAPIServiceProvider.php`
  - configs: `lighthouse.php`, `graphiql.php`, `acl.php`, `menu.php`, `system.php`
  - routes: `web.php`, `fcm-routes.php`
- الربط:
  - يoverride models لـ `Admin`, `Customer`, `CartRule` ويعيد binding لبعض controllers.
  - يعتمد على أغلب الحزم التجارية والإدارية ويستهلكها مباشرة في schema/resolvers/mutations.
  - ليس مجرد wrapper لـ `MedsdnApi`؛ هو stack مختلف موازٍ له.
  - `Installer` يفعّله عبر `medsdn-graphql:install`.

### Installer

- المسؤولية:
  - orchestration layer لتثبيت المشروع وحزمـه وbootstrap data/indexes.
- نقاط الدخول:
  - `packages/medsdn/Installer/src/Providers/InstallerServiceProvider.php`
  - routes: `Routes/web.php`
  - pipeline: `Support/PackageBootstrap/PackageBootstrapRegistry.php`
- الربط:
  - يسجّل bootstrap لكل الحزم.
  - مهم جدًا لفهم ترتيب التهيئة بين `Core`, `User`, `Theme`, `Product`, `CatalogRule`, `GraphQLAPI`, `MedsdnApi`.

### Inventory

- المسؤولية:
  - inventory sources والمخزون الأساسي.
- نقاط الدخول:
  - `packages/medsdn/Inventory/src/Providers/InventoryServiceProvider.php`
  - `ModuleServiceProvider`
- الربط:
  - يعتمد عليه `Product`, `Checkout`, `Sales`, وAdmin inventory screens.
  - بياناته تنعكس في product availability داخل `Shop`, `MedsdnApi`, و`GraphQLAPI`.

### MagicAI

- المسؤولية:
  - دمج الذكاء الاصطناعي والـ OpenAI features.
- نقاط الدخول:
  - `packages/medsdn/MagicAI/src/Providers/MagicAIServiceProvider.php`
- الربط:
  - حزمة وظيفية مستقلة نسبيًا، ترتبط أكثر مع `Admin`/content tooling من الدومين التجاري الأساسي.

### Marketing

- المسؤولية:
  - campaigns, subscribers, communication events, SEO/search terms/url rewrites.
- نقاط الدخول:
  - `packages/medsdn/Marketing/src/Providers/MarketingServiceProvider.php`
  - `EventServiceProvider`, `ModuleServiceProvider`
- الربط:
  - يعتمد على `CMS`, `Category`, `Customer`, `Product`, `Sitemap`.
  - `Admin` يوفّر UI لإدارته.
  - بعض مخرجاته تظهر في `Shop`, `FPC`, `Sitemap`.

### MedsdnApi

- المسؤولية:
  - REST + GraphQL API موحدة مبنية على API Platform مع موارد storefront/admin مخصصة.
- نقاط الدخول:
  - `packages/medsdn/MedsdnApi/src/Providers/MedsdnApiServiceProvider.php`
  - commands: `medsdn-api-platform:install`, `medsdn-api:generate-key`, `medsdn-api:key:manage`, `medsdn-api:key:maintain`
  - resources تحت `src/Models`, state تحت `src/State`, resolvers تحت `src/Resolver`, middleware/controllers/services داخل `src`
- الربط:
  - يستهلك repositories/models من `Checkout`, `Customer`, `Sales`, `Product`, `Category`, `Attribute`, `Theme`, `Payment`, `Shipping`, `BankTransfer`, `CMS`.
  - يضيف storefront key layer, GraphQL playground/docs, OpenAPI split docs, cart token lifecycle, auth token handling.
  - هذا هو package الربط المركزي مع كل الحزم الأخرى عندما يكون الاستهلاك عبر API بدل views التقليدية.

### Notification

- المسؤولية:
  - notifications domain وbinding إلى sales/events.
- نقاط الدخول:
  - `packages/medsdn/Notification/src/Providers/NotificationServiceProvider.php`
  - `EventServiceProvider`, `ModuleServiceProvider`
- الربط:
  - مرتبط خصوصًا بـ `Sales` وعمليات الإدارة.
  - UI الخاص به يظهر داخل `Admin`; push/Firebase-related flows تظهر أكثر في `GraphQLAPI`.

### Payment

- المسؤولية:
  - payment abstraction layer وتعريف payment methods.
- نقاط الدخول:
  - `packages/medsdn/Payment/src/Providers/PaymentServiceProvider.php`
  - config: `Config/paymentmethods.php`
- الربط:
  - يعتمد عليه `Checkout`, `Sales`, `Paypal`, `BankTransfer`.
  - `MedsdnApi` يعرّض payment methods عبر `PaymentMethodsProvider`.

### Paypal

- المسؤولية:
  - Paypal payment method integration.
- نقاط الدخول:
  - `packages/medsdn/Paypal/src/Providers/PaypalServiceProvider.php`
  - config: `Config/paymentmethods.php`
- الربط:
  - امتداد فوق `Payment` و`Checkout` و`Sales`.
  - ينعكس تلقائيًا في قوائم الدفع داخل `Shop` و`MedsdnApi`.

### Product

- المسؤولية:
  - المنتج بكل أنواعه وأسعاره وصوره وفيدوهاته وروابط التحميل والمراجعات والمخزون المفهرس.
- نقاط الدخول:
  - `packages/medsdn/Product/src/Providers/ProductServiceProvider.php`
  - `EventServiceProvider`, `ModuleServiceProvider`
  - config: `Config/product_types.php`
- الربط:
  - أكثر package مركزية بعد `Core`.
  - تعتمد عليه `Admin`, `Shop`, `CatalogRule`, `Checkout`, `Sales`, `Marketing`, `Inventory`, `Attribute`, `Category`, `Tax`.
  - `MedsdnApi` يعرّضه بأكبر قدر من الموارد/resolvers/providers.
  - `GraphQLAPI` يبني queries/mutations كثيرة فوقه.

### Rule

- المسؤولية:
  - طبقة rule abstractions المشتركة بين cart/catalog rules.
- نقاط الدخول:
  - `packages/medsdn/Rule/src/Providers/RuleServiceProvider.php`
  - `ModuleServiceProvider`
- الربط:
  - package قاعدة تستند إليها `CatalogRule` و`CartRule`.

### Sales

- المسؤولية:
  - orders, invoices, shipments, refunds, transactions, downloadable entitlements.
- نقاط الدخول:
  - `packages/medsdn/Sales/src/Providers/SalesServiceProvider.php`
  - `ModuleServiceProvider`
- الربط:
  - يعتمد على `Checkout`, `Customer`, `Inventory`, `Product`.
  - `Admin` يوفّر الإدارة الكاملة له.
  - `MedsdnApi` يعرّض customer orders/invoices/shipments/reorder/cancel.
  - `GraphQLAPI` يملك customer/admin order mutations وqueries مرتبطة به.

### Shipping

- المسؤولية:
  - shipping abstraction وتعريف carriers/rates.
- نقاط الدخول:
  - `packages/medsdn/Shipping/src/Providers/ShippingServiceProvider.php`
  - config: `Config/carriers.php`
- الربط:
  - يتكامل مع `Checkout`, `Sales`, `MedsdnApi` shipping estimators/providers, و`Shop` checkout.

### Shop

- المسؤولية:
  - storefront التقليدي: web routes, customer account, checkout pages, middleware theme/locale/currency.
- نقاط الدخول:
  - `packages/medsdn/Shop/src/Providers/ShopServiceProvider.php`
  - routes: `web.php`, `api.php`, `checkout-routes.php`, `customer-routes.php`, `store-front-routes.php`
  - config: `Config/menu.php`
- الربط:
  - يستهلك أغلب الحزم التجارية من منظور المستخدم النهائي.
  - يمثل المرجع السلوكي الذي يبني عليه كل من `GraphQLAPI` و`MedsdnApi` عند exposing نفس العمليات عبر API.

### Sitemap

- المسؤولية:
  - sitemap generation وربطها بـ SEO entities.
- نقاط الدخول:
  - `packages/medsdn/Sitemap/src/Providers/SitemapServiceProvider.php`
  - `ModuleServiceProvider`
- الربط:
  - يعتمد على `CMS`, `Category`, `Marketing`, `Product`.
  - يخدم storefront indexing أكثر من APIs مباشرة.

### SocialLogin

- المسؤولية:
  - social auth flow للعملاء.
- نقاط الدخول:
  - `packages/medsdn/SocialLogin/src/Providers/SocialLoginServiceProvider.php`
  - `EventServiceProvider`, `ModuleServiceProvider`
- الربط:
  - مبني فوق `Customer` و`Core`.
  - ينعكس على flows المتجر والحساب، ويمكن أن يهم `GraphQLAPI` أكثر من `MedsdnApi` بحسب نقطة الاستهلاك.

### SocialShare

- المسؤولية:
  - social sharing utilities للمنتجات/الصفحات.
- نقاط الدخول:
  - `packages/medsdn/SocialShare/src/Providers/SocialShareServiceProvider.php`
  - `EventServiceProvider`
- الربط:
  - package طرفي/واجهة أكثر من كونه domain package.
  - يتصل غالبًا بـ `Shop` وواجهات العرض.

### Tax

- المسؤولية:
  - tax categories/rates/logic.
- نقاط الدخول:
  - `packages/medsdn/Tax/src/Providers/TaxServiceProvider.php`
  - `ModuleServiceProvider`
- الربط:
  - مستخدم في `Checkout`, `Product`, `CartRule`, `CatalogRule`, `Admin`.
  - `MedsdnApi` يستهلكه ضمن shipping/totals/product pricing.

### Theme

- المسؤولية:
  - theme assets, customization records, view event manager, theming layer.
- نقاط الدخول:
  - `packages/medsdn/Theme/src/Providers/ThemeServiceProvider.php`
  - `ModuleServiceProvider`
- الربط:
  - أساسي لـ `Shop` ومرتبط أيضًا بـ `Admin` من ناحية view events/assets.
  - `MedsdnApi` يعرّض `ThemeCustomization` و`ThemeCustomizationTranslation`.

### User

- المسؤولية:
  - admin users, roles, permissions/bouncer.
- نقاط الدخول:
  - `packages/medsdn/User/src/Providers/UserServiceProvider.php`
  - `ModuleServiceProvider`
- الربط:
  - أساسي لـ `Admin` authentication/authorization.
  - `GraphQLAPI` يoverride Admin model منه.
  - `Installer` يزرع role/admin defaults.

## Internal-Only Packages Without composer.json

- هذه الحزم موجودة تحت `packages/medsdn` لكنها modules داخلية لا path packages مستقلة:
  - `BookingProduct`
  - `DataGrid`
  - `DataTransfer`
  - `DebugBar`
  - `FPC`
  - `GDPR`
  - `MagicAI`
  - `Marketing`
  - `Notification`
  - `Sitemap`
- يجب التعامل معها كجزء من monorepo runtime، لا كحزم Composer مستقلة.

## API Dependency Reading Order

- إذا أردت فهم API end-to-end بسرعة:
  - ابدأ بـ `packages/medsdn/MedsdnApi/src/Providers/MedsdnApiServiceProvider.php`
  - ثم انتقل إلى `src/Models` لمعرفة `ApiResource` classes
  - ثم `src/State` و`src/Resolver` لمعرفة منطق القراءة/الكتابة
  - ثم ارجع إلى repositories/models في الحزم التجارية الأساسية (`Checkout`, `Product`, `Customer`, `Sales`, `Category`, `Attribute`, `Payment`, `Shipping`)
- لفهم GraphQL legacy stack:
  - ابدأ بـ `packages/medsdn/GraphQLAPI/src/Providers/GraphQLAPIServiceProvider.php`
  - ثم `Routes/web.php` وconfig `lighthouse.php`
  - ثم `Queries`, `Mutations`, `Types`, `Listeners`

## Package Bootstrap Order That Matters

- الترتيب الأكثر أهمية عمليًا:
  - `Core` ثم `User` ثم `Theme`
  - بعدها `Attribute`, `Category`, `Product`, `Inventory`, `Tax`
  - بعدها `Checkout`, `Payment`, `Shipping`, `Sales`
  - ثم `Admin` و`Shop`
  - ثم الحزم الجانبية والامتدادات
  - وأخيرًا `GraphQLAPI` و`MedsdnApi` كسطوح استهلاك فوق الدومين الموجود
- `Installer` هو المرجع الرسمي لهذا الترتيب في fresh install.

## Deep References

- مرجع الاعتمادات بين الحزم:
  - [docs/architecture/package-dependency-map.md](/home/hmam/Documents/NEW/medsdn/docs/architecture/package-dependency-map.md)
- مرجع ربط الـ API بالحزم:
  - [docs/architecture/api-integration.md](/home/hmam/Documents/NEW/medsdn/docs/architecture/api-integration.md)
