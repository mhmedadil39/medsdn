<?php

namespace Webkul\MedsdnApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class WalletTopupOutput
{
    #[Groups(['read'])]
    #[ApiProperty(identifier: true, readable: true, writable: false)]
    public ?int $paymentId = null;

    #[Groups(['read'])]
    public ?bool $success = null;

    #[Groups(['read'])]
    public ?string $message = null;

    #[Groups(['read'])]
    public ?string $status = null;

    #[Groups(['read'])]
    public ?float $amount = null;
}
