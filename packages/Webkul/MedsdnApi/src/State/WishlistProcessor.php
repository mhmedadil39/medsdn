<?php

namespace Webkul\MedsdnApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\MedsdnApi\Dto\CreateWishlistInput;
use Webkul\MedsdnApi\Dto\DeleteWishlistInput;
use Webkul\MedsdnApi\Exception\AuthorizationException;
use Webkul\MedsdnApi\Exception\InvalidInputException;
use Webkul\MedsdnApi\Exception\ResourceNotFoundException;
use Webkul\MedsdnApi\Models\Wishlist;
use Webkul\MedsdnApi\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Http\Request;

/**
 * WishlistProcessor - Handles create/delete operations for wishlist items
 */
class WishlistProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private ?Request $request = null
    ) {}

    /**
     * Process wishlist item operations
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $operationName = $operation->getName();

        
        if (in_array($operationName, ['toggle'])) {
            return $this->handleToggle($data, $uriVariables, $context);
        }

        if ($data instanceof CreateWishlistInput) {
            return $this->handleCreate($data, $context);
        }

        /** Handle REST POST — model received instead of DTO */
        if ($data instanceof Wishlist && $operation instanceof \ApiPlatform\Metadata\Post) {
            $input = new CreateWishlistInput();
            $input->productId = request()->input('product_id') ?? request()->input('productId');

            return $this->handleCreate($input, $context);
        }

        if ($data instanceof DeleteWishlistInput) {
            return $this->handleDeleteFromInput($data, $context);
        }

        if ($operation instanceof \ApiPlatform\Metadata\Delete || in_array($operationName, ['delete', 'destroy'])) {
            return $this->handleDelete($data, $uriVariables, $context);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    /**
     * Handle create operation for wishlist items
     */
    private function handleCreate(CreateWishlistInput $input, array $context = []): Wishlist
    {
        if (empty($input->productId)) {
            throw new InvalidInputException(__('medsdnapi::app.graphql.wishlist.product-id-required'));
        }

        $product = Product::find($input->productId);
        if (! $product) {
            throw new ResourceNotFoundException(__('medsdnapi::app.graphql.wishlist.product-not-found'));
        }

        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            throw new AuthorizationException(__('medsdnapi::app.graphql.logout.unauthenticated'));
        }

        $customerId = $user->id;
        $channelId = core()->getCurrentChannel()->id;

        $existingItem = Wishlist::where('customer_id', $customerId)
            ->where('product_id', $input->productId)
            ->where('channel_id', $channelId)
            ->first();

        if ($existingItem) {
            throw new InvalidInputException(__('medsdnapi::app.graphql.wishlist.already-exists'));
        }

        Event::dispatch('customer.wishlist.create.before', $input->productId);

        $wishlistItem = Wishlist::create([
            'product_id'  => $input->productId,
            'customer_id' => $customerId,
            'channel_id'  => $channelId,
        ]);

        Event::dispatch('customer.wishlist.create.after', $wishlistItem);

        return $wishlistItem;
    }

    private function handleToggle(CreateWishlistInput $input, array $context = []): Wishlist
    {
        if (empty($input->productId)) {
            throw new InvalidInputException(__('medsdnapi::app.graphql.wishlist.product-id-required'));
        }

        $product = Product::find($input->productId);
        if (! $product) {
            throw new ResourceNotFoundException(__('medsdnapi::app.graphql.wishlist.product-not-found'));
        }

        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            throw new AuthorizationException(__('medsdnapi::app.graphql.logout.unauthenticated'));
        }

        $customerId = $user->id;
        $channelId = core()->getCurrentChannel()->id;

        $existingItem = Wishlist::where('customer_id', $customerId)
            ->where('product_id', $input->productId)
            ->where('channel_id', $channelId)
            ->first();

        if ($existingItem) {
            $existingItem->delete();

            Event::dispatch('customer.wishlist.delete.after', $existingItem);

            throw new InvalidInputException(__('medsdnapi::app.graphql.wishlist.removed'));
        }

        Event::dispatch('customer.wishlist.create.before', $input->productId);

        $wishlistItem = Wishlist::create([
            'product_id'  => $input->productId,
            'customer_id' => $customerId,
            'channel_id'  => $channelId,
        ]);

        Event::dispatch('customer.wishlist.create.after', $wishlistItem);

        return $wishlistItem;
    }

    
    /**
     * Handle delete operation from GraphQL mutation input
     */
    private function handleDeleteFromInput(DeleteWishlistInput $input, array $context): Wishlist
    {
        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            throw new AuthorizationException(__('medsdnapi::app.graphql.logout.unauthenticated'));
        }

        if (empty($input->id)) {
            throw new InvalidInputException(__('medsdnapi::app.graphql.wishlist.id-required'));
        }

        // Extract the numeric ID from the URI (format: /api/shop/wishlists/123)
        $wishlistItemId = basename($input->id);

        $wishlistItem = Wishlist::find($wishlistItemId);

        if (! $wishlistItem) {
            throw new ResourceNotFoundException(__('medsdnapi::app.graphql.wishlist.not-found'));
        }

        if ($wishlistItem->customer_id !== $user->id) {
            throw new AuthorizationException(__('medsdnapi::app.auth.cannot-update-other-profile'));
        }

        Event::dispatch('customer.wishlist.delete.before', $wishlistItemId);

        $wishlistItem->delete();

        Event::dispatch('customer.wishlist.delete.after', $wishlistItemId);

        return $wishlistItem;
    }

    /**
     * Handle delete operation for wishlist items with authorization
     */
    private function handleDelete(mixed $data, array $uriVariables, array $context): null
    {
        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            throw new AuthorizationException(__('medsdnapi::app.graphql.logout.unauthenticated'));
        }

        $wishlistItemId = $uriVariables['id'] ?? null;

        if (! $wishlistItemId) {
            throw new InvalidInputException(__('medsdnapi::app.graphql.wishlist.id-required'));
        }

        $wishlistItem = Wishlist::find($wishlistItemId);

        if (! $wishlistItem) {
            throw new ResourceNotFoundException(__('medsdnapi::app.graphql.wishlist.not-found'));
        }

        if ($wishlistItem->customer_id !== $user->id) {
            throw new AuthorizationException(__('medsdnapi::app.auth.cannot-update-other-profile'));
        }

        Event::dispatch('customer.wishlist.delete.before', $wishlistItemId);

        $wishlistItem->delete();

        Event::dispatch('customer.wishlist.delete.after', $wishlistItemId);

        return null;
    }
}

