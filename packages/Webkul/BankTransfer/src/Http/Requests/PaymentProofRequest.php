<?php

namespace Webkul\BankTransfer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentProofRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'payment_proof' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:4096', // 4MB in kilobytes
                function ($attribute, $value, $fail) {
                    // Additional server-side validation using FileHelper
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $validation = \Webkul\BankTransfer\Helpers\FileHelper::validate($value);
                        
                        if (! $validation['valid']) {
                            $fail($validation['error']);
                        }
                    }
                },
            ],
            'transaction_reference' => [
                'nullable',
                'string',
                'max:255',
                // Sanitize to prevent XSS
                function ($attribute, $value, $fail) {
                    if ($value && preg_match('/<[^>]*>/', $value)) {
                        $fail(trans('banktransfer::app.shop.errors.invalid-transaction-reference'));
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'payment_proof.required' => trans('banktransfer::app.shop.errors.payment-proof-required'),
            'payment_proof.file' => trans('banktransfer::app.shop.errors.invalid-file'),
            'payment_proof.mimes' => trans('banktransfer::app.shop.errors.invalid-file-type'),
            'payment_proof.max' => trans('banktransfer::app.shop.errors.file-too-large', ['size' => '4MB']),
            'transaction_reference.string' => trans('banktransfer::app.shop.errors.invalid-transaction-reference'),
            'transaction_reference.max' => trans('banktransfer::app.shop.errors.transaction-reference-too-long'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'payment_proof' => trans('banktransfer::app.shop.checkout.upload-proof-title'),
            'transaction_reference' => trans('banktransfer::app.shop.checkout.transaction-reference'),
        ];
    }
}
