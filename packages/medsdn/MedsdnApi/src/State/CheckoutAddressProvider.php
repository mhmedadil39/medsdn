<?php

namespace Webkul\MedsdnApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Request;
use Webkul\MedsdnApi\Dto\CheckoutAddressOutput;
use Webkul\MedsdnApi\Exception\AuthenticationException;
use Webkul\MedsdnApi\Exception\ResourceNotFoundException;
use Webkul\MedsdnApi\Facades\CartTokenFacade;
use Webkul\MedsdnApi\Facades\TokenHeaderFacade;

/**
 * Provides address data from MedsdnApi queries.
 */
class CheckoutAddressProvider implements ProviderInterface
{
    public function __construct() {}

    /**
     * Provide address data from MedsdnApi context.
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): object|array|null {
        $request = Request::instance() ?? ($context['request'] ?? null);

        // Extract Bearer token from Authorization header
        $token = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

        if (! $token) {
            throw new AuthenticationException(__('medsdnapi::app.graphql.cart.authentication-required'));
        }

        $cart = CartTokenFacade::getCartByToken($token);

        if (! $cart) {
            throw new ResourceNotFoundException(__('medsdnapi::app.graphql.cart.invalid-token'));
        }

        return $this->buildAddressOutput($cart);
    }

    /**
     * Build address output from cart.
     */
    private function buildAddressOutput($cart): CheckoutAddressOutput
    {
        $output = new CheckoutAddressOutput;

        $output->id = $cart->id;
        $output->cartToken = $cart->guest_cart_token ?? $cart->customer_id;
        $output->customerId = $cart->customer_id;

        $billingAddress = $cart->billing_address;
        if ($billingAddress) {
            $output->billingFirstName = $billingAddress->first_name;
            $output->billingLastName = $billingAddress->last_name;
            $output->billingEmail = $billingAddress->email;
            $output->billingCompanyName = $billingAddress->company_name;
            $output->billingAddress = $billingAddress->address;
            $output->billingCountry = $billingAddress->country;
            $output->billingState = $billingAddress->state;
            $output->billingCity = $billingAddress->city;
            $output->billingPostcode = $billingAddress->postcode;
            $output->billingPhoneNumber = $billingAddress->phone;
        }

        $shippingAddress = $cart->shipping_address;
        if ($shippingAddress) {
            $output->shippingFirstName = $shippingAddress->first_name;
            $output->shippingLastName = $shippingAddress->last_name;
            $output->shippingEmail = $shippingAddress->email;
            $output->shippingCompanyName = $shippingAddress->company_name;
            $output->shippingAddress = $shippingAddress->address;
            $output->shippingCountry = $shippingAddress->country;
            $output->shippingState = $shippingAddress->state;
            $output->shippingCity = $shippingAddress->city;
            $output->shippingPostcode = $shippingAddress->postcode;
            $output->shippingPhoneNumber = $shippingAddress->phone;
        }

        $output->success = true;
        $output->message = __('medsdnapi::app.graphql.address.retrieved');

        return $output;
    }
}
