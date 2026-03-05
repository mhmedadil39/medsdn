<?php

namespace Webkul\MedsdnApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\MedsdnApi\Dto\DeleteAllCompareItemsInput;
use Webkul\MedsdnApi\Exception\AuthorizationException;
use Webkul\MedsdnApi\Models\CompareItem;
use Webkul\MedsdnApi\Models\DeleteAllCompareItems;
use Illuminate\Support\Facades\Auth;

/**
 * DeleteAllCompareItemsProcessor - Deletes all compare items for the authenticated customer
 */
class DeleteAllCompareItemsProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
    ) {}

    /**
     * Process delete all compare items operation
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof DeleteAllCompareItemsInput) {
            return $this->handleDeleteAll();
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    /**
     * Delete all compare items for the authenticated customer
     */
    private function handleDeleteAll(): DeleteAllCompareItems
    {
        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            throw new AuthorizationException(__('medsdnapi::app.graphql.logout.unauthenticated'));
        }

        $deletedCount = CompareItem::where('customer_id', $user->id)->count();

        CompareItem::where('customer_id', $user->id)->delete();

        return new DeleteAllCompareItems(
            __('medsdnapi::app.graphql.compare-item.delete-all-success'),
            $deletedCount,
            $user->id
        );
    }
}
