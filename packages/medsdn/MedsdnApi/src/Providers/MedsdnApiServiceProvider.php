<?php

namespace Webkul\MedsdnApi\Providers;

use ApiPlatform\GraphQl\Resolver\Factory\ResolverFactoryInterface;
use ApiPlatform\GraphQl\Resolver\QueryCollectionResolverInterface;
use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use ApiPlatform\Metadata\IdentifiersExtractorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\ServiceProvider;
use Webkul\MedsdnApi\Console\Commands\GenerateStorefrontKey;
use Webkul\MedsdnApi\Facades\CartTokenFacade;
use Webkul\MedsdnApi\Http\Controllers\AdminGraphQLPlaygroundController;
use Webkul\MedsdnApi\Http\Controllers\ApiPlatformAssetController;
use Webkul\MedsdnApi\Http\Controllers\GraphQLPlaygroundController;
use Webkul\MedsdnApi\Http\Middleware\VerifyStorefrontKey;
use Webkul\MedsdnApi\Metadata\CustomIdentifiersExtractor;
use Webkul\MedsdnApi\OpenApi\SplitOpenApiFactory;
use Webkul\MedsdnApi\Repositories\GuestCartTokensRepository;
use Webkul\MedsdnApi\Resolver\BaseQueryItemResolver;
use Webkul\MedsdnApi\Resolver\CategoryCollectionResolver;
use Webkul\MedsdnApi\Resolver\CustomerQueryResolver;
use Webkul\MedsdnApi\Resolver\Factory\ProductRelationResolverFactory;
use Webkul\MedsdnApi\Resolver\ProductCollectionResolver;
use Webkul\MedsdnApi\Resolver\SingleProductMedsdnApiResolver;
use Webkul\MedsdnApi\Resolver\PageByUrlKeyResolver;
use Webkul\MedsdnApi\Routing\CustomIriConverter;
use Webkul\MedsdnApi\Serializer\TokenHeaderDenormalizer;
use Webkul\MedsdnApi\Services\CartTokenService;
use Webkul\MedsdnApi\Services\StorefrontKeyService;
use Webkul\MedsdnApi\Services\TokenHeaderService;
use Webkul\MedsdnApi\State\AttributeCollectionProvider;
use Webkul\MedsdnApi\State\AttributeOptionCollectionProvider;
use Webkul\MedsdnApi\State\AttributeOptionQueryProvider;
use Webkul\MedsdnApi\State\AttributeValueProcessor;
use Webkul\MedsdnApi\State\AuthenticatedCustomerProvider;
use Webkul\MedsdnApi\State\BundleOptionProductsProvider;
use Webkul\MedsdnApi\State\CartTokenMutationProvider;
use Webkul\MedsdnApi\State\CartTokenProcessor;
use Webkul\MedsdnApi\State\CategoryTreeProvider;
use Webkul\MedsdnApi\State\ChannelProvider;
use Webkul\MedsdnApi\State\CheckoutAddressProvider;
use Webkul\MedsdnApi\State\CheckoutProcessor;
use Webkul\MedsdnApi\State\CompareItemProcessor;
use Webkul\MedsdnApi\State\CountryStateCollectionProvider;
use Webkul\MedsdnApi\State\CountryStateQueryProvider;
use Webkul\MedsdnApi\State\CustomerAddressProvider;
use Webkul\MedsdnApi\State\CustomerAddressTokenProcessor;
use Webkul\MedsdnApi\State\CustomerProcessor;
use Webkul\MedsdnApi\State\CustomerProfileProcessor;
use Webkul\MedsdnApi\State\DefaultChannelProvider;
use Webkul\MedsdnApi\State\DownloadableLinksProvider;
use Webkul\MedsdnApi\State\DownloadableProductProcessor;
use Webkul\MedsdnApi\State\DownloadableSamplesProvider;
use Webkul\MedsdnApi\State\FilterableAttributesProvider;
use Webkul\MedsdnApi\State\ForgotPasswordProcessor;
use Webkul\MedsdnApi\State\GetCheckoutAddressCollectionProvider;
use Webkul\MedsdnApi\State\GroupedProductsProvider;
use Webkul\MedsdnApi\State\LoginProcessor;
use Webkul\MedsdnApi\State\LogoutProcessor;
use Webkul\MedsdnApi\State\PaymentMethodsProvider;
use Webkul\MedsdnApi\State\Processor\NewsletterSubscriptionProcessor;
use Webkul\MedsdnApi\State\Processor\ContactUsProcessor;
use Webkul\MedsdnApi\State\ProductMedsdnApiProvider;
use Webkul\MedsdnApi\State\ProductGraphQLProvider;
use Webkul\MedsdnApi\State\ProductCustomerGroupPriceProcessor;
use Webkul\MedsdnApi\State\ProductCustomerGroupPriceProvider;
use Webkul\MedsdnApi\State\ProductProcessor;
use Webkul\MedsdnApi\State\ProductRelationProvider;
use Webkul\MedsdnApi\State\ProductReviewProcessor;
use Webkul\MedsdnApi\State\ProductReviewProvider;
use Webkul\MedsdnApi\State\ShippingRatesProvider;
use Webkul\MedsdnApi\State\VerifyTokenProcessor;
use Webkul\MedsdnApi\State\CompareItemProvider;
use Webkul\MedsdnApi\State\CustomerDownloadableProductProvider;
use Webkul\MedsdnApi\State\CustomerInvoiceProvider;
use Webkul\MedsdnApi\State\CustomerOrderProvider;
use Webkul\MedsdnApi\State\CustomerOrderShipmentProvider;
use Webkul\MedsdnApi\State\CustomerReviewProvider;
use Webkul\MedsdnApi\State\WishlistProcessor;
use Webkul\MedsdnApi\State\WishlistProvider;
use Webkul\MedsdnApi\State\MoveWishlistToCartProcessor;
use Webkul\MedsdnApi\State\DeleteAllWishlistsProcessor;
use Webkul\MedsdnApi\State\CancelOrderProcessor;
use Webkul\MedsdnApi\State\ReorderProcessor;
use Webkul\MedsdnApi\State\DeleteAllCompareItemsProcessor;
use ApiPlatform\GraphQl\Serializer\SerializerContextBuilder as GraphQlSerializerContextBuilder;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Webkul\MedsdnApi\GraphQl\Serializer\FixedSerializerContextBuilder;

class MedsdnApiServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider bindings.
     */
    public function register(): void
    {
        $this->app->register(ModuleServiceProvider::class);

        $this->app->singleton(StorefrontKeyService::class, function ($app) {
            return new StorefrontKeyService;
        });

        $this->app->extend(OpenApiFactoryInterface::class, function ($openApiFactory) {
            return new SplitOpenApiFactory($openApiFactory);
        });

        $this->app->singleton(TokenHeaderDenormalizer::class);

        $this->app->singleton('token-header-service', function ($app) {
            return new TokenHeaderService;
        });

        $this->app->alias('token-header-service', 'Webkul\MedsdnApi\Services\TokenHeaderService');

        $this->app->singleton('cart-token-service', function ($app) {
            return new CartTokenService(
                $app->make('Webkul\Checkout\Repositories\CartRepository'),
                $app->make('Webkul\MedsdnApi\Repositories\GuestCartTokensRepository'),
                $app->make('Webkul\Customer\Repositories\CustomerRepository')
            );
        });

        $this->app->alias('cart-token-service', CartTokenFacade::class);

        $this->app->singleton('Webkul\MedsdnApi\Repositories\GuestCartTokensRepository', function ($app) {
            return new GuestCartTokensRepository($app);
        });

        $this->app->tag(ProductProcessor::class, ProcessorInterface::class);
        $this->app->tag(AttributeValueProcessor::class, ProcessorInterface::class);
        $this->app->tag(ProductCustomerGroupPriceProcessor::class, ProcessorInterface::class);
        $this->app->tag(CustomerProcessor::class, ProcessorInterface::class);
        $this->app->tag(LoginProcessor::class, ProcessorInterface::class);
        $this->app->tag(VerifyTokenProcessor::class, ProcessorInterface::class);
        $this->app->tag(LogoutProcessor::class, ProcessorInterface::class);
        $this->app->tag(ForgotPasswordProcessor::class, ProcessorInterface::class);
        $this->app->tag(CustomerProfileProcessor::class, ProcessorInterface::class);
        $this->app->tag(CustomerAddressTokenProcessor::class, ProcessorInterface::class);
        $this->app->tag(CartTokenProcessor::class, ProcessorInterface::class);
        $this->app->tag(CheckoutProcessor::class, ProcessorInterface::class);
        $this->app->tag(ProductReviewProcessor::class, ProcessorInterface::class);
        $this->app->tag(CompareItemProcessor::class, ProcessorInterface::class);
        $this->app->tag(DownloadableProductProcessor::class, ProcessorInterface::class);
        $this->app->tag(NewsletterSubscriptionProcessor::class, ProcessorInterface::class);
        $this->app->tag(WishlistProcessor::class, ProcessorInterface::class);
        $this->app->tag(MoveWishlistToCartProcessor::class, ProcessorInterface::class);
        $this->app->tag(DeleteAllWishlistsProcessor::class, ProcessorInterface::class);
        $this->app->tag(DeleteAllCompareItemsProcessor::class, ProcessorInterface::class);
        $this->app->tag(CancelOrderProcessor::class, ProcessorInterface::class);
        $this->app->tag(ReorderProcessor::class, ProcessorInterface::class);
        $this->app->tag(ContactUsProcessor::class, ProcessorInterface::class);

        $this->app->tag(TokenHeaderDenormalizer::class, 'serializer.normalizer');

        $this->app->singleton(ProductCustomerGroupPriceProcessor::class, function ($app) {
            return new ProductCustomerGroupPriceProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(CustomerProcessor::class, function ($app) {
            return new CustomerProcessor(
                $app->make('Webkul\Customer\Repositories\CustomerRepository'),
                $app->make('Webkul\MedsdnApi\Validators\CustomerValidator')
            );
        });

        $this->app->singleton(LoginProcessor::class, function ($app) {
            return new LoginProcessor(
                $app->make('Webkul\MedsdnApi\Validators\LoginValidator')
            );
        });

        $this->app->singleton(CustomerProfileProcessor::class, function ($app) {
            return new CustomerProfileProcessor(
                $app->make('Webkul\MedsdnApi\Validators\CustomerValidator')
            );
        });

        $this->app->singleton(CartTokenProcessor::class, function ($app) {
            return new CartTokenProcessor(
                $app->make('Webkul\Checkout\Repositories\CartRepository'),
                $app->make('Webkul\MedsdnApi\Repositories\GuestCartTokensRepository')
            );
        });

        $this->app->singleton(CheckoutProcessor::class, function ($app) {
            return new CheckoutProcessor(
                $app->make('Webkul\Customer\Repositories\CustomerRepository'),
                $app->make('Webkul\Sales\Repositories\OrderRepository'),
                $app->make('Webkul\Checkout\Repositories\CartRepository')
            );
        });

        $this->app->singleton(ProductReviewProcessor::class, function ($app) {
            return new ProductReviewProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(CompareItemProcessor::class, function ($app) {
            return new CompareItemProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(WishlistProcessor::class, function ($app) {
            return new WishlistProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(MoveWishlistToCartProcessor::class, function ($app) {
            return new MoveWishlistToCartProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(DeleteAllWishlistsProcessor::class, function ($app) {
            return new DeleteAllWishlistsProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(DeleteAllCompareItemsProcessor::class, function ($app) {
            return new DeleteAllCompareItemsProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(CancelOrderProcessor::class, function ($app) {
            return new CancelOrderProcessor(
                $app->make(PersistProcessor::class),
                $app->make('Webkul\Sales\Repositories\OrderRepository')
            );
        });

        $this->app->singleton(ReorderProcessor::class, function ($app) {
            return new ReorderProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->tag(CheckoutAddressProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerAddressProvider::class, ProviderInterface::class);
        $this->app->tag(GetCheckoutAddressCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(PaymentMethodsProvider::class, ProviderInterface::class);
        $this->app->tag(ShippingRatesProvider::class, ProviderInterface::class);
        $this->app->tag(AuthenticatedCustomerProvider::class, ProviderInterface::class);
        $this->app->tag(CartTokenMutationProvider::class, ProviderInterface::class);
        $this->app->tag(ChannelProvider::class, ProviderInterface::class);
        $this->app->tag(DefaultChannelProvider::class, ProviderInterface::class);
        $this->app->tag(ProductMedsdnApiProvider::class, ProviderInterface::class);
        $this->app->tag(ProductGraphQLProvider::class, ProviderInterface::class);
        $this->app->tag(ProductCustomerGroupPriceProvider::class, ProviderInterface::class);
        $this->app->tag(ProductRelationProvider::class, ProviderInterface::class);
        $this->app->tag(BundleOptionProductsProvider::class, ProviderInterface::class);
        $this->app->tag(GroupedProductsProvider::class, ProviderInterface::class);
        $this->app->tag(DownloadableLinksProvider::class, ProviderInterface::class);
        $this->app->tag(DownloadableSamplesProvider::class, ProviderInterface::class);
        $this->app->tag(ProductReviewProvider::class, ProviderInterface::class);
        $this->app->tag(FilterableAttributesProvider::class, ProviderInterface::class);
        $this->app->tag(AttributeCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AttributeOptionCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AttributeOptionQueryProvider::class, ProviderInterface::class);
        $this->app->tag(CountryStateCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(CountryStateQueryProvider::class, ProviderInterface::class);
        $this->app->tag(CategoryTreeProvider::class, ProviderInterface::class);
        $this->app->tag(WishlistProvider::class, ProviderInterface::class);
        $this->app->tag(CompareItemProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerReviewProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerOrderProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerDownloadableProductProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerInvoiceProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerOrderShipmentProvider::class, ProviderInterface::class);

        $this->app->singleton(GetCheckoutAddressCollectionProvider::class, function ($app) {
            return new GetCheckoutAddressCollectionProvider(
                $app->make('ApiPlatform\State\Pagination\Pagination')
            );
        });

        $this->app->singleton(WishlistProvider::class, function ($app) {
            return new WishlistProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CompareItemProvider::class, function ($app) {
            return new CompareItemProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerReviewProvider::class, function ($app) {
            return new CustomerReviewProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerOrderProvider::class, function ($app) {
            return new CustomerOrderProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerDownloadableProductProvider::class, function ($app) {
            return new CustomerDownloadableProductProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerInvoiceProvider::class, function ($app) {
            return new CustomerInvoiceProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerOrderShipmentProvider::class, function ($app) {
            return new CustomerOrderShipmentProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerAddressProvider::class, function ($app) {
            return new CustomerAddressProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(ProductMedsdnApiProvider::class, function ($app) {
            return new ProductMedsdnApiProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(ProductGraphQLProvider::class, function ($app) {
            return new ProductGraphQLProvider(
                $app->make(Pagination::class)
            );
        });
        
        $this->app->singleton(ProductRelationProvider::class, function ($app) {
            return new ProductRelationProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(ProductReviewProvider::class, function ($app) {
            return new ProductReviewProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(GroupedProductsProvider::class, function ($app) {
            return new GroupedProductsProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(DownloadableLinksProvider::class, function ($app) {
            return new DownloadableLinksProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(DownloadableSamplesProvider::class, function ($app) {
            return new DownloadableSamplesProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(FilterableAttributesProvider::class, function ($app) {
            return new FilterableAttributesProvider(
                $app->make(\ApiPlatform\State\Pagination\Pagination::class)
            );
        });

        $this->app->singleton(AttributeCollectionProvider::class, function ($app) {
            return new AttributeCollectionProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(AttributeOptionCollectionProvider::class, function ($app) {
            return new AttributeOptionCollectionProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CountryStateCollectionProvider::class, function ($app) {
            return new CountryStateCollectionProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(ProductCollectionResolver::class);
        $this->app->tag(SingleProductMedsdnApiResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(CategoryCollectionResolver::class, QueryCollectionResolverInterface::class);
        $this->app->tag(BaseQueryItemResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(CustomerQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(PageByUrlKeyResolver::class, QueryCollectionResolverInterface::class);

        $this->app->extend(ResolverFactoryInterface::class, function ($resolverFactory, $app) {
            return new ProductRelationResolverFactory(
                $resolverFactory,
                $app->make(ProductRelationProvider::class)
            );
        });

        $this->app->extend(IdentifiersExtractorInterface::class, function ($extractor) {
            return new CustomIdentifiersExtractor($extractor);
        });

        $this->app->extend(IriConverterInterface::class, function ($converter, $app) {
            return new CustomIriConverter(
                $converter,
                $app->make(\ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface::class)
            );
        });

        $this->app->extend(GraphQlSerializerContextBuilder::class, function ($builder, $app) {
            return new FixedSerializerContextBuilder(
                $builder,
                $app->make(NameConverterInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'medsdnapi');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'webkul');

        if ($this->isRunningAsVendorPackage()) {
            $this->publishes([
                __DIR__.'/../config/api-platform-vendor.php' => config_path('api-platform.php'),
            ], 'medsdnapi-config');
        } else {
            $this->publishes([
                __DIR__.'/../config/api-platform.php' => config_path('api-platform.php'),
            ], 'medsdnapi-config');        
        }

        $this->publishes([
            __DIR__.'/../config/graphql-auth.php' => config_path('graphql-auth.php'),
            __DIR__.'/../config/storefront.php'   => config_path('storefront.php'),
        ], 'medsdnapi-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/webkul'),
        ], 'medsdnapi-views');

        $this->publishes([
            __DIR__.'/../Resources/assets' => public_path('themes/admin/default/assets'),
        ], 'medsdnapi-assets');

        $this->runInstallationIfNeeded();
        $this->registerApiResources();
        $this->registerApiDocumentationRoutes();
        $this->registerMiddlewareAliases();
        $this->registerServiceProviders();

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * Register API documentation routes.
     */
    protected function registerApiDocumentationRoutes(): void
    {
        \Illuminate\Support\Facades\Route::get('/vendor/api-platform/{path}', ApiPlatformAssetController::class)
            ->where('path', '.*')
            ->name('medsdnapi.vendor-assets');

        \Illuminate\Support\Facades\Route::get('/api', \Webkul\MedsdnApi\Http\Controllers\ApiEntrypointController::class)
            ->name('medsdnapi.docs-index');

        \Illuminate\Support\Facades\Route::get('/api/shop', [
            \Webkul\MedsdnApi\Http\Controllers\SwaggerUIController::class, 'shopApi',
        ])->name('medsdnapi.shop-docs')->where('_format', '^(?!json|xml|csv)');

        \Illuminate\Support\Facades\Route::get('/api/admin', [
            \Webkul\MedsdnApi\Http\Controllers\SwaggerUIController::class, 'adminApi',
        ])->name('medsdnapi.admin-docs')->where('_format', '^(?!json|xml|csv)');

        \Illuminate\Support\Facades\Route::get('/api/shop/docs', [
            \Webkul\MedsdnApi\Http\Controllers\SwaggerUIController::class, 'shopApiDocs',
        ])->name('medsdnapi.shop-api-spec');

        \Illuminate\Support\Facades\Route::get('/api/admin/docs', [
            \Webkul\MedsdnApi\Http\Controllers\SwaggerUIController::class, 'adminApiDocs',
        ])->name('medsdnapi.admin-api-spec');

        \Illuminate\Support\Facades\Route::get('/api/graphiql', GraphQLPlaygroundController::class)
            ->name('medsdnapi.graphql-playground');

        \Illuminate\Support\Facades\Route::get('/api/graphql', GraphQLPlaygroundController::class)
            ->name('medsdnapi.api-graphql-playground');

        \Illuminate\Support\Facades\Route::get('/admin/graphiql', AdminGraphQLPlaygroundController::class)
            ->name('medsdnapi.admin-graphql-playground');

        \Illuminate\Support\Facades\Route::get('/api/shop/customer-invoices/{id}/pdf', \Webkul\MedsdnApi\Http\Controllers\InvoicePdfController::class)
            ->where('id', '[0-9]+')
            ->middleware(['Webkul\MedsdnApi\Http\Middleware\VerifyStorefrontKey'])
            ->name('medsdnapi.customer-invoice-pdf');
    }

    /**
     * Register API resources.
     */
    protected function registerApiResources(): void
    {
        if ($this->app->bound('api_platform.metadata_factory')) {
        }
    }

    /**
     * Run installation if needed.
     */
    protected function runInstallationIfNeeded(): void
    {
        if (file_exists(config_path('api-platform.php'))) {
            return;
        }

        if (! $this->app->runningInConsole() || ! $this->isComposerOperation()) {
            return;
        }

        try {
            $this->app['artisan']->call('medsdn-api-platform:install', ['--quiet' => true]);
        } catch (\Exception) {
            // Installation can be run manually if needed
        }
    }

    /**
     * Determine if running via Composer.
     */
    protected function isComposerOperation(): bool
    {
        $composerMemory = getenv('COMPOSER_MEMORY_LIMIT');
        $composerAuth = getenv('COMPOSER_AUTH');

        return ! empty($composerMemory) || ! empty($composerAuth) || defined('COMPOSER_BINARY_PATH');
    }

    /**
     * Register middleware aliases.
     */
    protected function registerMiddlewareAliases(): void
    {
        $this->app['router']->aliasMiddleware('storefront.key', VerifyStorefrontKey::class);
        $this->app['router']->aliasMiddleware('api.locale-channel', \Webkul\MedsdnApi\Http\Middleware\SetLocaleChannel::class);
        $this->app['router']->aliasMiddleware('api.rate-limit', \Webkul\MedsdnApi\Http\Middleware\RateLimitApi::class);
        $this->app['router']->aliasMiddleware('api.security-headers', \Webkul\MedsdnApi\Http\Middleware\SecurityHeaders::class);
        $this->app['router']->aliasMiddleware('api.log-requests', \Webkul\MedsdnApi\Http\Middleware\LogApiRequests::class);
    }

    /**
     * Register service providers.
     */
    protected function registerServiceProviders(): void
    {
        $this->app->register(ApiPlatformExceptionHandlerServiceProvider::class);
        $this->app->register(DatabaseQueryLoggingProvider::class);
        $this->app->register(ExceptionHandlerServiceProvider::class);
    }

    /**
     * Register console commands.
     */
    protected function registerCommands(): void
    {
        $this->commands([
            \Webkul\MedsdnApi\Console\Commands\InstallApiPlatformCommand::class,
            GenerateStorefrontKey::class,
            \Webkul\MedsdnApi\Console\Commands\ApiKeyManagementCommand::class,
            \Webkul\MedsdnApi\Console\Commands\ApiKeyMaintenanceCommand::class,
        ]);
    }

    /**
     * Check if the package is running as a vendor package.
     */
    protected function isRunningAsVendorPackage(): bool
    {
        return str_contains(__DIR__, 'vendor');
    }
}
