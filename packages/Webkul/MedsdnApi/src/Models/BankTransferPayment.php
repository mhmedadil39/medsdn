<?php

namespace Webkul\MedsdnApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\MedsdnApi\Dto\BankTransferConfigOutput;
use Webkul\MedsdnApi\Dto\BankTransferPaymentInput;
use Webkul\MedsdnApi\Dto\BankTransferPaymentOutput;
use Webkul\MedsdnApi\Dto\BankTransferStatisticsOutput;
use Webkul\MedsdnApi\State\BankTransferConfigProvider;
use Webkul\MedsdnApi\State\BankTransferPaymentProcessor;
use Webkul\MedsdnApi\State\BankTransferPaymentProvider;
use Webkul\MedsdnApi\State\BankTransferStatisticsProvider;

/**
 * BankTransferPayment - API Resource for Bank Transfer Payment Method
 *
 * Provides REST and GraphQL APIs for bank transfer payment operations
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'BankTransferPayment',
    operations: [
        // REST: Get bank transfer configuration
        new Get(
            uriTemplate: '/bank-transfer/config',
            output: BankTransferConfigOutput::class,
            provider: BankTransferConfigProvider::class,
        ),
        // REST: Upload payment proof (public endpoint for guest checkout)
        new Post(
            uriTemplate: '/bank-transfer/upload',
            input: BankTransferPaymentInput::class,
            output: BankTransferPaymentOutput::class,
            processor: BankTransferPaymentProcessor::class,
        ),
        // REST: Get customer payments
        new GetCollection(
            uriTemplate: '/bank-transfer/payments',
            output: BankTransferPaymentOutput::class,
            provider: BankTransferPaymentProvider::class,
            security: "is_granted('ROLE_CUSTOMER')",
        ),
        // REST: Get payment details
        new Get(
            uriTemplate: '/bank-transfer/payments/{id}',
            output: BankTransferPaymentOutput::class,
            provider: BankTransferPaymentProvider::class,
            security: "is_granted('ROLE_CUSTOMER')",
        ),
        // REST: Get statistics
        new Get(
            uriTemplate: '/bank-transfer/statistics',
            output: BankTransferStatisticsOutput::class,
            provider: BankTransferStatisticsProvider::class,
            security: "is_granted('ROLE_CUSTOMER')",
        ),
    ],
    graphQlOperations: [
        // GraphQL: Query configuration
        new Query(
            name: 'config',
            output: BankTransferConfigOutput::class,
            provider: BankTransferConfigProvider::class,
            description: 'Get bank transfer payment method configuration',
        ),
        // GraphQL: Query customer payments
        new QueryCollection(
            name: 'payments',
            output: BankTransferPaymentOutput::class,
            provider: BankTransferPaymentProvider::class,
            security: "is_granted('ROLE_CUSTOMER')",
            description: 'Get authenticated customer\'s bank transfer payments',
        ),
        // GraphQL: Query payment details
        new Query(
            name: 'payment',
            output: BankTransferPaymentOutput::class,
            provider: BankTransferPaymentProvider::class,
            security: "is_granted('ROLE_CUSTOMER')",
            description: 'Get specific payment details',
        ),
        // GraphQL: Query statistics
        new Query(
            name: 'statistics',
            output: BankTransferStatisticsOutput::class,
            provider: BankTransferStatisticsProvider::class,
            security: "is_granted('ROLE_CUSTOMER')",
            description: 'Get payment statistics for authenticated customer',
        ),
        // GraphQL: Mutation upload
        new Mutation(
            name: 'upload',
            input: BankTransferPaymentInput::class,
            output: BankTransferPaymentOutput::class,
            processor: BankTransferPaymentProcessor::class,
            description: 'Upload payment proof and create order',
        ),
    ],
)]
class BankTransferPayment
{
    #[ApiProperty(identifier: true, readable: true, writable: false)]
    #[Groups(['read'])]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['read'])]
    public ?int $orderId = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['read'])]
    public ?int $customerId = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['read'])]
    public ?string $methodCode = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['read'])]
    public ?string $transactionReference = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['read'])]
    public ?string $status = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['read'])]
    public ?string $statusLabel = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['read'])]
    public ?int $reviewedBy = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['read'])]
    public ?string $reviewedAt = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['read'])]
    public ?string $adminNote = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['read'])]
    public ?string $createdAt = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['read'])]
    public ?string $updatedAt = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['read'])]
    public ?bool $isPending = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['read'])]
    public ?bool $isApproved = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['read'])]
    public ?bool $isRejected = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['read'])]
    public ?array $order = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['read'])]
    public ?array $reviewer = null;
}
