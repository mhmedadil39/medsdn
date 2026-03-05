<?php

use Webkul\GraphQLAPI\MedSDNGraphql;

/**
 * MedSDN GraphQL Helper
 * 
 * This helper provides quick access to the MedSDN GraphQL API functionality.
 */
if (! function_exists('medsdn_graphql')) {
    function medsdn_graphql()
    {
        return app()->make(MedSDNGraphql::class);
    }
}
