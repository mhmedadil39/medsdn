<?php

namespace Webkul\MedsdnApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * BankTransferPaymentOutput - Output DTO for Bank Transfer Payment
 *
 * Output for payment details and upload response
 */
class BankTransferPaymentOutput
{
    #[Groups(['read'])]
    #[ApiProperty(identifier: true, readable: true, writable: false)]
    public ?int $id = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $success = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $message = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?int $orderId = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?int $paymentId = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?int $customerId = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $methodCode = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $transactionReference = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $status = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $paymentStatus = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $statusLabel = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?int $reviewedBy = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $reviewedAt = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $adminNote = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $createdAt = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $updatedAt = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $isPending = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $isApproved = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $isRejected = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?array $order = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?array $reviewer = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?array $data = null;
}
