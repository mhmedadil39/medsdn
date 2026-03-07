<?php

namespace Webkul\MedsdnApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Request;
use Webkul\MedsdnApi\Dto\PaymentMethodOutput;
use Webkul\MedsdnApi\Exception\ResourceNotFoundException;
use Webkul\MedsdnApi\Facades\CartTokenFacade;
use Webkul\MedsdnApi\Facades\TokenHeaderFacade;
use Webkul\Payment\Facades\Payment;

/**
 * Provides available payment methods for a cart.
 */
class PaymentMethodsProvider implements ProviderInterface
{
    public function __construct() {}

    /**
     * Provide payment methods for the given cart token.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = Request::instance() ?? ($context['request'] ?? null);

        // Extract Bearer token from Authorization header
        $token = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

        if (! $token) {
            throw new ResourceNotFoundException(__('medsdnapi::app.graphql.cart.authentication-required'));
        }

        $cart = CartTokenFacade::getCartByToken($token);

        if (! $cart) {
            throw new ResourceNotFoundException(__('medsdnapi::app.graphql.cart.invalid-token'));
        }

        $methods = Payment::getSupportedPaymentMethods();

        if ($methods && is_array($methods) && isset($methods['payment_methods'])) {
            $methodsArray = $methods['payment_methods'];
        } elseif ($methods && is_object($methods) && property_exists($methods, 'payment_methods')) {
            $methodsArray = (array) $methods->payment_methods;
        } else {
            $methodsArray = [];
        }

        if (! $methodsArray || ! is_array($methodsArray)) {
            return [];
        }

        $outputs = [];
        foreach ($methodsArray as $key => $method) {
            $output = new PaymentMethodOutput;
            $method_data = is_object($method) ? (array) $method : $method;
            $output->id = (string) ($method_data['method'] ?? $key);
            $output->method = $method_data['method'] ?? null;
            $output->title = $method_data['method_title'] ?? null;
            $output->description = $method_data['description'] ?? null;
            $output->icon = $method_data['image'] ?? null;
            $output->isAllowed = $method_data['is_allowed'] ?? true;
            $output->additionalData = $method_data['additional_data'] ?? null;

            // Add bank transfer specific data
            if ($output->method === 'banktransfer') {
                $paymentMethod = Payment::getPaymentMethod('banktransfer');
                if ($paymentMethod) {
                    // Merge new data with existing additional_data to preserve existing keys
                    $bankTransferData = [
                        'bank_accounts' => $paymentMethod->getBankAccounts(),
                        'instructions' => core()->getConfigData('sales.payment_methods.banktransfer.instructions'),
                        'max_file_size' => '4MB',
                        'allowed_file_types' => ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
                    ];
                    
                    $output->additionalData = array_merge(
                        $output->additionalData ?? [],
                        $bankTransferData
                    );
                }
            }

            $outputs[] = $output;
        }

        return $outputs;
    }
}
