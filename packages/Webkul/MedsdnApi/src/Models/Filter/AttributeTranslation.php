<?php

namespace Webkul\MedsdnApi\Models\Filter;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [],
    graphQlOperations: []
)]
class AttributeTranslation extends \Webkul\Attribute\Models\AttributeTranslation
{
    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
