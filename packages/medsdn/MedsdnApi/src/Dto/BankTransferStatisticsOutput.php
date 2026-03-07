<?php

namespace Webkul\MedsdnApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * BankTransferStatisticsOutput - Output DTO for Bank Transfer Statistics
 *
 * Returns payment count by status for customer
 */
class BankTransferStatisticsOutput
{
    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $success = null;

    #[Groups(['read'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?array $data = null;
}
