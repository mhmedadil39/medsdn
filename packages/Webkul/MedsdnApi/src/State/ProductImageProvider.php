<?php

namespace Webkul\MedsdnApi\State;

use Webkul\MedsdnApi\Models\ProductImage;

class ProductImageProvider extends AbstractNestedResourceProvider
{
    protected function getModelClass(): string
    {
        return ProductImage::class;
    }
}
