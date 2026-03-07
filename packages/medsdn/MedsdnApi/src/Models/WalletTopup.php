<?php

namespace Webkul\MedsdnApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use Webkul\MedsdnApi\Dto\WalletTopupInput;
use Webkul\MedsdnApi\Dto\WalletTopupOutput;
use Webkul\MedsdnApi\State\WalletTopupProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'WalletTopup',
    operations: [
        new Post(
            uriTemplate: '/wallet/topups',
            input: WalletTopupInput::class,
            output: WalletTopupOutput::class,
            processor: WalletTopupProcessor::class,
            security: "is_granted('ROLE_CUSTOMER')",
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'createWalletTopupPayment',
            input: WalletTopupInput::class,
            output: WalletTopupOutput::class,
            processor: WalletTopupProcessor::class,
            security: "is_granted('ROLE_CUSTOMER')",
            description: 'Create a wallet topup payment using manual bank transfer',
        ),
    ],
)]
class WalletTopup {}
