<?php

namespace Webkul\MedsdnApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class WalletSummaryOutput
{
    #[Groups(['read'])]
    #[ApiProperty(identifier: true, readable: true, writable: false)]
    public ?int $id = null;

    #[Groups(['read'])]
    public ?string $currency = null;

    #[Groups(['read'])]
    public ?string $status = null;

    #[Groups(['read'])]
    public ?float $balance = null;

    #[Groups(['read'])]
    public ?float $availableBalance = null;

    #[Groups(['read'])]
    public ?float $heldBalance = null;
}
