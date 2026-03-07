<?php

namespace Webkul\MedsdnApi\State;

use Webkul\MedsdnApi\Models\ProductCustomerGroupPrice;

class ProductCustomerGroupPriceProvider extends AbstractNestedResourceProvider
{
    protected function getModelClass(): string
    {
        return ProductCustomerGroupPrice::class;
    }
}
