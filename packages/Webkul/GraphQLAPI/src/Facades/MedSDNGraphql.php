<?php

namespace Webkul\GraphQLAPI\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * MedSDN GraphQL Facade
 * 
 * Provides access to the MedSDN GraphQL API functionality.
 */
class MedSDNGraphql extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'medsdn_graphql';
    }
}
