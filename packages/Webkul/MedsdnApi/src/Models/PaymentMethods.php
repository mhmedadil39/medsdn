<?php

namespace Webkul\MedsdnApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\MedsdnApi\Dto\PaymentMethodOutput;
use Webkul\MedsdnApi\State\PaymentMethodsProvider;

/**
 * PaymentMethods - GraphQL API Resource for Payment Methods
 *
 * Provides query for fetching available payment methods during checkout
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'PaymentMethods',
    operations: [],
    graphQlOperations: [
        new QueryCollection(
            name: 'collection',
            output: PaymentMethodOutput::class,
            provider: PaymentMethodsProvider::class,
            paginationEnabled: false,
            description: 'Get available payment methods for a cart by token',
        ),
    ]
)]
class PaymentMethods {}
