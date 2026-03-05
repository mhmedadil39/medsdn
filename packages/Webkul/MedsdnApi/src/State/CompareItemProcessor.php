<?php

namespace Webkul\MedsdnApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\MedsdnApi\Dto\CreateCompareItemInput;
use Webkul\MedsdnApi\Dto\DeleteCompareItemInput;
use Webkul\MedsdnApi\Exception\AuthorizationException;
use Webkul\MedsdnApi\Exception\InvalidInputException;
use Webkul\MedsdnApi\Exception\ResourceNotFoundException;
use Webkul\MedsdnApi\Models\CompareItem;
use Webkul\MedsdnApi\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/**
 * CompareItemProcessor - Handles create/delete operations for compare items
 */
class CompareItemProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private ?Request $request = null
    ) {}

    /**
     * Process compare item operations
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof CreateCompareItemInput) {
            return $this->handleCreate($data, $context);
        }

        /** Handle REST POST — model received instead of DTO */
        if ($data instanceof CompareItem && $operation instanceof \ApiPlatform\Metadata\Post) {
            $input = new CreateCompareItemInput();
            $input->productId = request()->input('product_id') ?? request()->input('productId');

            return $this->handleCreate($input, $context);
        }

        if ($data instanceof DeleteCompareItemInput) {
            return $this->handleDelete($data);
        }

        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        if ($result instanceof CompareItem && $result->id) {
            $result->loadMissing(['product', 'customer']);
        }

        return $result;
    }

    /**
     * Handle create operation for compare items
     */
    private function handleCreate(CreateCompareItemInput $input, array $context = []): CompareItem
    {
        if (empty($input->productId)) {
            throw new InvalidInputException(__('medsdnapi::app.graphql.compare-item.product-id-required'));
        }

        $product = Product::find($input->productId);
        if (! $product) {
            throw new ResourceNotFoundException(__('medsdnapi::app.graphql.compare-item.product-not-found'));
        }
 
        $user = Auth::guard('sanctum')->user();
            
        if (! $user) {
            throw new AuthorizationException(__('medsdnapi::app.graphql.logout.unauthenticated'));
        }

        $customerId = $user->id;

        $existingItem = CompareItem::where('customer_id', $customerId)
            ->where('product_id', $input->productId)
            ->first();

        if ($existingItem) {
            throw new InvalidInputException(__('medsdnapi::app.graphql.compare-item.already-exists'));
        }

        $compareItem = CompareItem::create([
            'product_id' => $input->productId,
            'customer_id' => $customerId,
        ]);

        $compareItem->load(['product', 'customer']);

        return $compareItem;
    }

    /**
     * Handle delete operation for compare items
     */
    private function handleDelete(DeleteCompareItemInput $input): CompareItem
    {
        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            throw new AuthorizationException(__('medsdnapi::app.graphql.logout.unauthenticated'));
        }

        if (empty($input->id)) {
            throw new InvalidInputException(__('medsdnapi::app.graphql.compare-item.id-required'));
        }

        $compareItemId = basename($input->id);

        $compareItem = CompareItem::find($compareItemId);

        if (! $compareItem) {
            throw new ResourceNotFoundException(__('medsdnapi::app.graphql.compare-item.not-found'));
        }

        if ($compareItem->customer_id !== $user->id) {
            throw new AuthorizationException(__('medsdnapi::app.auth.cannot-update-other-profile'));
        }

        $compareItem->load(['product', 'customer']);
        $compareItem->delete();

        return $compareItem;
    }
}
