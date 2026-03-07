<?php

namespace Webkul\MedsdnApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\MedsdnApi\Dto\CustomerPaymentOutput;
use Webkul\MedsdnApi\State\CustomerPaymentProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerPayment',
    operations: [
        new GetCollection(
            uriTemplate: '/payments',
            output: CustomerPaymentOutput::class,
            provider: CustomerPaymentProvider::class,
            security: "is_granted('ROLE_CUSTOMER')",
        ),
        new Get(
            uriTemplate: '/payments/{id}',
            output: CustomerPaymentOutput::class,
            provider: CustomerPaymentProvider::class,
            security: "is_granted('ROLE_CUSTOMER')",
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            name: 'payments',
            output: CustomerPaymentOutput::class,
            provider: CustomerPaymentProvider::class,
            security: "is_granted('ROLE_CUSTOMER')",
            description: 'Get authenticated customer payments',
        ),
        new Query(
            name: 'payment',
            output: CustomerPaymentOutput::class,
            provider: CustomerPaymentProvider::class,
            security: "is_granted('ROLE_CUSTOMER')",
            description: 'Get a customer payment by id',
        ),
    ],
)]
class CustomerPayment {}
