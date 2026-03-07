<?php

namespace Webkul\MedsdnApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Log;
use Webkul\MedsdnApi\Dto\BankTransferConfigOutput;
use Webkul\MedsdnApi\Exception\ResourceNotFoundException;
use Webkul\Payment\Facades\Payment;

/**
 * Provides bank transfer payment method configuration.
 */
class BankTransferConfigProvider implements ProviderInterface
{
    /**
     * Provide bank transfer configuration.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        try {
            $paymentMethod = Payment::getPaymentMethod('banktransfer');

            if (! $paymentMethod || ! $paymentMethod->isAvailable()) {
                throw new ResourceNotFoundException(
                    trans('banktransfer::app.shop.errors.payment-method-not-available')
                );
            }

            $bankAccounts = $paymentMethod->getBankAccounts();
            $instructions = core()->getConfigData('sales.payment_methods.banktransfer.instructions');

            $output = new BankTransferConfigOutput();
            $output->success = true;
            $output->data = [
                'title' => $paymentMethod->getTitle(),
                'description' => $paymentMethod->getDescription(),
                'bank_accounts' => $bankAccounts,
                'instructions' => $instructions,
                'max_file_size' => '4MB',
                'allowed_file_types' => ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
            ];

            return $output;
        } catch (\Exception $e) {
            // Preserve and rethrow existing ResourceNotFoundException
            if ($e instanceof ResourceNotFoundException) {
                throw $e;
            }

            Log::error('Bank Transfer API - Get Config Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new ResourceNotFoundException(
                trans('banktransfer::app.shop.errors.config-fetch-failed')
            );
        }
    }
}
