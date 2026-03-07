<?php

namespace Webkul\MedsdnApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * BankTransferConfigOutput - Output DTO for Bank Transfer Configuration
 *
 * Returns bank account details and upload requirements
 */
class BankTransferConfigOutput
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
