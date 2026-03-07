<?php

namespace Webkul\MedsdnApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use Webkul\MedsdnApi\Dto\OrderWalletPaymentInput;
use Webkul\MedsdnApi\Dto\PaymentActionOutput;
use Webkul\MedsdnApi\State\OrderWalletPaymentProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'OrderWalletPayment',
    operations: [
        new Post(
            uriTemplate: '/payments/orders/{id}/wallet',
            input: OrderWalletPaymentInput::class,
            output: PaymentActionOutput::class,
            processor: OrderWalletPaymentProcessor::class,
            security: "is_granted('ROLE_CUSTOMER')",
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'payOrderWithWallet',
            input: OrderWalletPaymentInput::class,
            output: PaymentActionOutput::class,
            processor: OrderWalletPaymentProcessor::class,
            security: "is_granted('ROLE_CUSTOMER')",
            description: 'Pay an order using wallet balance',
        ),
    ],
)]
class OrderWalletPayment {}
