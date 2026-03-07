<?php

namespace Webkul\MedsdnApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\MedsdnApi\Dto\WalletTransactionOutput;
use Webkul\MedsdnApi\State\WalletTransactionProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'WalletTransaction',
    operations: [
        new GetCollection(
            uriTemplate: '/wallet/transactions',
            output: WalletTransactionOutput::class,
            provider: WalletTransactionProvider::class,
            security: "is_granted('ROLE_CUSTOMER')",
        ),
        new Get(
            uriTemplate: '/wallet/transactions/{id}',
            output: WalletTransactionOutput::class,
            provider: WalletTransactionProvider::class,
            security: "is_granted('ROLE_CUSTOMER')",
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            name: 'walletTransactions',
            output: WalletTransactionOutput::class,
            provider: WalletTransactionProvider::class,
            security: "is_granted('ROLE_CUSTOMER')",
            description: 'Get authenticated customer wallet transactions',
        ),
        new Query(
            name: 'walletTransaction',
            output: WalletTransactionOutput::class,
            provider: WalletTransactionProvider::class,
            security: "is_granted('ROLE_CUSTOMER')",
            description: 'Get a wallet transaction by id',
        ),
    ],
)]
class WalletTransaction {}
