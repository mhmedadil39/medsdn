<?php

return [
    'api' => [
        'config' => [
            'success' => 'Bank transfer configuration retrieved successfully',
            'not-available' => 'Bank transfer payment method is not available',
            'fetch-failed' => 'Failed to fetch bank transfer configuration',
        ],

        'upload' => [
            'success' => 'Payment proof uploaded successfully. Your order is under review.',
            'validation' => [
                'proof-required' => 'Payment proof is required',
                'invalid-type' => 'Invalid file type. Allowed types: JPG, PNG, WEBP, PDF',
                'file-too-large' => 'File size must not exceed :size',
                'transaction-ref-max' => 'Transaction reference must not exceed 255 characters',
            ],
            'errors' => [
                'upload-failed' => 'Failed to upload payment proof',
                'order-creation-failed' => 'Failed to create order',
                'invalid-cart' => 'Invalid cart or cart is empty',
                'invalid-payment-method' => 'Invalid payment method selected',
                'cart-token-required' => 'Cart token is required',
                'invalid-token' => 'Invalid or expired cart token',
            ],
        ],

        'payments' => [
            'success' => 'Payments retrieved successfully',
            'fetch-failed' => 'Failed to fetch payments',
            'not-found' => 'Payment not found',
            'unauthorized' => 'You are not authorized to view this payment',
        ],

        'statistics' => [
            'success' => 'Statistics retrieved successfully',
            'fetch-failed' => 'Failed to fetch statistics',
        ],

        'auth' => [
            'required' => 'Authentication required',
            'unauthenticated' => 'You must be logged in to access this resource',
        ],

        'rate-limit' => [
            'exceeded' => 'Too many upload attempts. Please try again later.',
        ],
    ],
];
