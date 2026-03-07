<?php

namespace Webkul\MedsdnApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class PaymentActionOutput
{
    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $success = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $message = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?array $data = null;
}
