<?php

namespace Webkul\MedsdnApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CustomerPaymentOutput
{
    #[Groups(['read'])]
    #[ApiProperty(identifier: true, readable: true, writable: false)]
    public ?int $id = null;

    #[Groups(['read'])]
    public ?string $paymentMethod = null;

    #[Groups(['read'])]
    public ?string $purpose = null;

    #[Groups(['read'])]
    public ?float $amount = null;

    #[Groups(['read'])]
    public ?string $currency = null;

    #[Groups(['read'])]
    public ?string $status = null;

    #[Groups(['read'])]
    public ?string $externalReference = null;

    #[Groups(['read'])]
    public ?string $notes = null;

    #[Groups(['read'])]
    public ?string $createdAt = null;

    #[Groups(['read'])]
    public ?string $paidAt = null;

    #[Groups(['read'])]
    public ?array $payable = null;
}
