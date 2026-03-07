<?php

namespace Webkul\MedsdnApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Webkul\BankTransfer\Repositories\BankTransferRepository;
use Webkul\MedsdnApi\Dto\BankTransferStatisticsOutput;
use Webkul\MedsdnApi\Exception\AuthenticationException;
use Webkul\MedsdnApi\Exception\OperationFailedException;

/**
 * Provides bank transfer payment statistics for customers.
 */
class BankTransferStatisticsProvider implements ProviderInterface
{
    public function __construct(
        protected BankTransferRepository $bankTransferRepository
    ) {
    }

    /**
     * Provide payment statistics.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        try {
            $customer = Auth::guard('sanctum')->user()
                ?: Auth::guard('api')->user()
                ?: Auth::guard('customer')->user();

            if (! $customer) {
                throw new AuthenticationException(
                    trans('shop::app.customer.account.auth.unauthenticated')
                );
            }

            $statistics = [
                'total' => $this->bankTransferRepository
                    ->where('customer_id', $customer->id)
                    ->count(),
                'pending' => $this->bankTransferRepository
                    ->where('customer_id', $customer->id)
                    ->where('status', 'pending')
                    ->count(),
                'approved' => $this->bankTransferRepository
                    ->where('customer_id', $customer->id)
                    ->where('status', 'approved')
                    ->count(),
                'rejected' => $this->bankTransferRepository
                    ->where('customer_id', $customer->id)
                    ->where('status', 'rejected')
                    ->count(),
            ];

            $output = new BankTransferStatisticsOutput();
            $output->success = true;
            $output->data = $statistics;

            return $output;
        } catch (AuthenticationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $resolvedCustomer = Auth::guard('sanctum')->user()
                ?: Auth::guard('api')->user()
                ?: Auth::guard('customer')->user();

            Log::error('Bank Transfer API - Get Statistics Error', [
                'error' => $e->getMessage(),
                'customer_id' => $resolvedCustomer->id ?? null,
            ]);

            throw new OperationFailedException(
                trans('banktransfer::app.shop.errors.fetch-statistics-failed')
            );
        }
    }
}
