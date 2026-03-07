<?php

namespace Webkul\MedsdnApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class OrderWalletPaymentInput
{
    #[Groups(['write'])]
    #[ApiProperty(readable: false, writable: true)]
    public ?int $orderId = null;

    #[Groups(['write'])]
    #[ApiProperty(readable: false, writable: true)]
    public ?string $notes = null;
}
