<?php

namespace Webkul\Shop\Http\Requests\Customer\Account;

use Illuminate\Foundation\Http\FormRequest;

class WalletTopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('customer')->check();
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'transaction_reference' => ['required', 'string', 'max:255'],
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
