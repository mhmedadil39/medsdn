<?php

namespace Webkul\MedsdnApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BankTransferPaymentInput - Input DTO for Bank Transfer Payment Upload
 *
 * Input for uploading payment proof and creating order
 */
class BankTransferPaymentInput
{
    #[Groups(['write'])]
    #[ApiProperty(readable: false, writable: true)]
    #[Assert\NotBlank(message: 'Payment proof is required')]
    #[Assert\File(
        maxSize: '4M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
        mimeTypesMessage: 'Please upload a valid file (JPG, PNG, WEBP, or PDF)'
    )]
    public mixed $paymentProof = null;

    #[Groups(['write'])]
    #[ApiProperty(readable: false, writable: true)]
    #[Assert\Length(max: 255)]
    public ?string $transactionReference = null;

    #[Groups(['write'])]
    #[ApiProperty(readable: false, writable: true)]
    public ?string $cartToken = null;
}
