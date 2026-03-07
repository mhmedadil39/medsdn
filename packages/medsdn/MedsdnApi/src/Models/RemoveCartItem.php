<?php

namespace Webkul\MedsdnApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\MedsdnApi\Dto\CartData;
use Webkul\MedsdnApi\Dto\CartInput;
use Webkul\MedsdnApi\State\CartTokenMutationProvider;
use Webkul\MedsdnApi\State\CartTokenProcessor;

/**
 * RemoveCartItem - GraphQL API Resource for Removing Cart Items
 *
 * Provides mutation for removing cart items without requiring resource ID.
 * Uses 'create' operation name to bypass API Platform's ID requirement.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'RemoveCartItem',
    operations: [
        new Post(
            name: 'removeItem',
            uriTemplate: '/remove-cart-item',
            input: CartInput::class,
            output: CartData::class,
            provider: CartTokenMutationProvider::class,
            processor: CartTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Remove item from cart. Use token and cartItemId.',
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CartInput::class,
            output: CartData::class,
            provider: CartTokenMutationProvider::class,
            processor: CartTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Remove item from cart. Use token and cartItemId.',
        ),
    ]
)]
class RemoveCartItem
{
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $cartToken = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?int $itemsCount = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $subtotal = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $grandTotal = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?float $discountAmount = null;
}
