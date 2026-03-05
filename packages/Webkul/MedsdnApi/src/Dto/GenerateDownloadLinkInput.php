<?php

namespace Webkul\MedsdnApi\Dto;

use ApiPlatform\Metadata\ApiProperty;

/**
 * GenerateDownloadLinkInput
 *
 * DTO for generating download links for purchased downloadable products
 */
class GenerateDownloadLinkInput
{
    #[ApiProperty(description: 'ID of the purchased downloadable link')]
    public int $downloadableLinkPurchasedId;

    public function __construct(int $downloadableLinkPurchasedId)
    {
        $this->downloadableLinkPurchasedId = $downloadableLinkPurchasedId;
    }
}
