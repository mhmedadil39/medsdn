<?php

namespace Webkul\MedsdnApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Query;
use Webkul\MedsdnApi\Dto\WalletSummaryOutput;
use Webkul\MedsdnApi\State\WalletSummaryProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'WalletSummary',
    operations: [
        new Get(
            uriTemplate: '/wallet',
            output: WalletSummaryOutput::class,
            provider: WalletSummaryProvider::class,
            security: "is_granted('ROLE_CUSTOMER')",
        ),
    ],
    graphQlOperations: [
        new Query(
            name: 'wallet',
            output: WalletSummaryOutput::class,
            provider: WalletSummaryProvider::class,
            security: "is_granted('ROLE_CUSTOMER')",
            description: 'Get authenticated customer wallet summary',
        ),
    ],
)]
class WalletSummary {}
