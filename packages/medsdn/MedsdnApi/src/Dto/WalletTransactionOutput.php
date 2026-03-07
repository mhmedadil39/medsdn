<?php

namespace Webkul\MedsdnApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class WalletTransactionOutput
{
    #[Groups(['read'])]
    #[ApiProperty(identifier: true, readable: true, writable: false)]
    public ?int $id = null;

    #[Groups(['read'])]
    public ?string $type = null;

    #[Groups(['read'])]
    public ?string $direction = null;

    #[Groups(['read'])]
    public ?float $amount = null;

    #[Groups(['read'])]
    public ?float $balanceBefore = null;

    #[Groups(['read'])]
    public ?float $balanceAfter = null;

    #[Groups(['read'])]
    public ?string $status = null;

    #[Groups(['read'])]
    public ?string $source = null;

    #[Groups(['read'])]
    public ?string $description = null;

    #[Groups(['read'])]
    public ?string $createdAt = null;
}
