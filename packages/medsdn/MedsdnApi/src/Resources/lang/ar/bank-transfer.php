<?php

return [
    'api' => [
        'config' => [
            'success' => 'تم استرجاع إعدادات التحويل البنكي بنجاح',
            'not-available' => 'طريقة الدفع بالتحويل البنكي غير متاحة',
            'fetch-failed' => 'فشل في جلب إعدادات التحويل البنكي',
        ],

        'upload' => [
            'success' => 'تم رفع إثبات الدفع بنجاح. طلبك قيد المراجعة.',
            'validation' => [
                'proof-required' => 'إثبات الدفع مطلوب',
                'invalid-type' => 'نوع الملف غير صالح. الأنواع المسموحة: JPG، PNG، WEBP، PDF',
                'file-too-large' => 'يجب ألا يتجاوز حجم الملف :size',
                'transaction-ref-max' => 'يجب ألا يتجاوز رقم المعاملة 255 حرفاً',
            ],
            'errors' => [
                'upload-failed' => 'فشل في رفع إثبات الدفع',
                'order-creation-failed' => 'فشل في إنشاء الطلب',
                'invalid-cart' => 'السلة غير صالحة أو فارغة',
                'invalid-payment-method' => 'طريقة الدفع المحددة غير صالحة',
                'cart-token-required' => 'رمز السلة مطلوب',
                'invalid-token' => 'رمز السلة غير صالح أو منتهي الصلاحية',
            ],
        ],

        'payments' => [
            'success' => 'تم استرجاع المدفوعات بنجاح',
            'fetch-failed' => 'فشل في جلب المدفوعات',
            'not-found' => 'الدفعة غير موجودة',
            'unauthorized' => 'غير مصرح لك بعرض هذه الدفعة',
        ],

        'statistics' => [
            'success' => 'تم استرجاع الإحصائيات بنجاح',
            'fetch-failed' => 'فشل في جلب الإحصائيات',
        ],

        'auth' => [
            'required' => 'المصادقة مطلوبة',
            'unauthenticated' => 'يجب تسجيل الدخول للوصول إلى هذا المورد',
        ],

        'rate-limit' => [
            'exceeded' => 'عدد كبير جداً من محاولات الرفع. يرجى المحاولة لاحقاً.',
        ],
    ],
];
