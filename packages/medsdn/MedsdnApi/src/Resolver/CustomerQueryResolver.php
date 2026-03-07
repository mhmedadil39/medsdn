<?php

namespace Webkul\MedsdnApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\MedsdnApi\Exception\AuthorizationException;
use Webkul\MedsdnApi\Helper\CustomerProfileHelper;
use Webkul\MedsdnApi\Models\CustomerProfile as CustomerProfileModel;
use Webkul\Customer\Models\Customer;

/**
 * GraphQL resolver for single Customer queries
 * Requires authentication - only authenticated users can view customer data
 */
class CustomerQueryResolver implements QueryItemResolverInterface
{
    public function __invoke(?object $item, array $context): CustomerProfileModel
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('medsdnapi::app.graphql.logout.unauthenticated'));
        }

        $token = $customer->currentAccessToken();

        if (! $token) {
            throw new AuthorizationException(__('medsdnapi::app.graphql.logout.token-not-found-or-expired'));
        }

        return CustomerProfileHelper::mapCustomerToProfile($customer);
    }
}
