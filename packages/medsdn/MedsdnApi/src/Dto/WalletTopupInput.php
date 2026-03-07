<?php

namespace Webkul\MedsdnApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class WalletTopupInput
{
    #[Groups(['write'])]
    #[ApiProperty(readable: false, writable: true)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?float $amount = null;

    #[Groups(['write'])]
    #[ApiProperty(readable: false, writable: true)]
    #[Assert\NotBlank]
    public ?string $transactionReference = null;

    #[Groups(['write'])]
    #[ApiProperty(readable: false, writable: true)]
    public ?string $bankName = null;

    #[Groups(['write'])]
    #[ApiProperty(readable: false, writable: true)]
    public ?string $notes = null;

    #[Groups(['write'])]
    #[ApiProperty(readable: false, writable: true)]
    #[Assert\NotBlank]
    #[Assert\File(
        maxSize: '4M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
        mimeTypesMessage: 'Please upload a valid file (JPG, PNG, WEBP, or PDF)'
    )]
    public mixed $paymentProof = null;
}
