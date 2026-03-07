<?php

namespace Webkul\Wallet\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class WalletManagementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('admin')->check();
    }

    public function rules(): array
    {
        return [
            'customer_id'  => ['required', 'integer', 'exists:customers,id'],
            'action_type'  => ['required', 'in:credit,debit'],
            'amount'       => ['required', 'numeric', 'gt:0'],
            'description'  => ['required', 'string', 'max:500'],
        ];
    }
}
