<?php

namespace Webkul\MedsdnApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\MedsdnApi\Dto\WalletTransactionOutput;
use Webkul\MedsdnApi\Exception\AuthenticationException;
use Webkul\Wallet\Models\WalletTransaction;

class WalletTransactionProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = $this->resolveCustomer();

        if (! $customer) {
            throw new AuthenticationException(trans('shop::app.customer.account.auth.unauthenticated'));
        }

        if (isset($uriVariables['id'])) {
            $transaction = WalletTransaction::query()
                ->where('customer_id', $customer->id)
                ->find($uriVariables['id']);

            return $transaction ? $this->map($transaction) : null;
        }

        $paginator = WalletTransaction::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate($context['filters']['per_page'] ?? 15);

        $items = [];

        foreach ($paginator->items() as $transaction) {
            $items[] = $this->map($transaction);
        }

        return [
            'data' => $items,
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    protected function map(object $transaction): WalletTransactionOutput
    {
        $output = new WalletTransactionOutput();
        $output->id = $transaction->id;
        $output->type = $transaction->type->value;
        $output->direction = $transaction->direction->value;
        $output->amount = (float) $transaction->amount;
        $output->balanceBefore = (float) $transaction->balance_before;
        $output->balanceAfter = (float) $transaction->balance_after;
        $output->status = $transaction->status->value;
        $output->source = $transaction->source;
        $output->description = $transaction->description;
        $output->createdAt = $transaction->created_at?->toIso8601String();

        return $output;
    }

    protected function resolveCustomer(): ?object
    {
        return Auth::guard('sanctum')->user()
            ?: Auth::guard('api')->user()
            ?: Auth::guard('customer')->user();
    }
}
