<?php

namespace Webkul\MedsdnApi\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Webkul\MedsdnApi\Dto\SubscribeToNewsletterInput;
use Webkul\MedsdnApi\Exception\AuthorizationException;
use Webkul\MedsdnApi\Exception\InvalidInputException;
use Webkul\Core\Models\SubscribersListProxy;

class NewsletterSubscriptionProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($operation->getName() !== 'create') {
            return (object) [
                'success' => false,
                'message' => __('medsdnapi::app.graphql.logout.invalid-operation'),
            ];
        }

        if (! ($data instanceof SubscribeToNewsletterInput)) {
            return (object) [
                'success' => false,
                'message' => __('medsdnapi::app.graphql.logout.invalid-input-data'),
            ];
        }

        $validator = Validator::make(['customerEmail' => $data->customerEmail], ['customerEmail' => 'required|email|unique:subscribers_list,email']);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);

            throw new InvalidInputException($errorMessage);
        }

        try {
            $customer = Auth::guard('sanctum')->user();

            if (! $customer) {
                throw new AuthorizationException(__('medsdnapi::app.graphql.logout.unauthenticated'));
            }

            $token = $customer->currentAccessToken();

            if (! $token) {
                throw new AuthorizationException(__('medsdnapi::app.graphql.logout.token-not-found-or-expired'));
            }

            SubscribersListProxy::create([
                'email'         => $data->customerEmail,
                'channel_id'    => $data?->channelId ?? core()->getCurrentChannel()->id,
                'is_subscribed' => 1,
                'token'         => uniqid(),
                'customer_id'   => $customer ? $customer?->id : null,
            ]);

            return (object) [
                'success'  => true,
                'message'  => __('shop::app.subscription.subscribe-success'),
            ];
        } catch (\Exception $e) {
            return (object) [
                'success' => false,
                'message' => __('medsdnapi::app.graphql.newsletter.error-during-subscription'),
            ];
        }
    }
}
