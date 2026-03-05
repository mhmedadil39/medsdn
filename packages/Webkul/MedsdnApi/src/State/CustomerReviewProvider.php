<?php

namespace Webkul\MedsdnApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\MedsdnApi\Exception\AuthorizationException;
use Webkul\MedsdnApi\Exception\ResourceNotFoundException;
use Webkul\MedsdnApi\Models\CustomerReview;

/**
 * CustomerReviewProvider — Retrieves reviews authored by the authenticated customer
 *
 * Supports cursor-based pagination, status and rating filters.
 * All queries are scoped to the current customer for multi-tenant isolation.
 */
class CustomerReviewProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    /**
     * Provide customer reviews for collection or single-item operations
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('medsdnapi::app.graphql.logout.unauthenticated'));
        }

        /** Single item — GET /api/shop/customer-reviews/{id} */
        if (! $operation instanceof GetCollection && ! ($operation instanceof \ApiPlatform\Metadata\GraphQl\QueryCollection)) {
            return $this->provideItem($customer, $uriVariables);
        }

        return $this->provideCollection($customer, $context);
    }

    /**
     * Return a single review owned by the customer
     */
    private function provideItem(object $customer, array $uriVariables): CustomerReview
    {
        $id = $uriVariables['id'] ?? null;

        if (! $id) {
            throw new ResourceNotFoundException(__('medsdnapi::app.graphql.customer-review.id-required'));
        }

        $review = CustomerReview::where('customer_id', $customer->id)
            ->with(['product', 'customer'])
            ->find($id);

        if (! $review) {
            throw new ResourceNotFoundException(
                __('medsdnapi::app.graphql.customer-review.not-found', ['id' => $id])
            );
        }

        return $review;
    }

    /**
     * Return a paginated collection of reviews owned by the customer
     */
    private function provideCollection(object $customer, array $context): Paginator
    {
        $args = $context['args'] ?? [];
        $filters = $context['filters'] ?? [];

        $query = CustomerReview::where('customer_id', $customer->id)
            ->with(['product', 'customer']);

        /** Apply optional filters */
        $status = $args['status'] ?? $filters['status'] ?? null;
        if ($status !== null) {
            $query->where('status', (string) $status);
        }

        $rating = $args['rating'] ?? $filters['rating'] ?? null;
        if ($rating !== null) {
            $query->where('rating', (int) $rating);
        }

        /** Cursor-based pagination */
        $first  = isset($args['first']) ? (int) $args['first'] : null;
        $last   = isset($args['last']) ? (int) $args['last'] : null;
        $after  = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        $perPage = $first ?? $last ?? 10;

        $query->orderBy('id', 'desc');

        if ($after) {
            $afterId = (int) base64_decode($after);
            $query->where('id', '<', $afterId);
        } elseif ($before) {
            $beforeId = (int) base64_decode($before);
            $query->where('id', '>', $beforeId);
            $query->orderBy('id', 'asc');
        }

        $laravelPaginator = $query->paginate($perPage);

        return new Paginator($laravelPaginator);
    }
}
