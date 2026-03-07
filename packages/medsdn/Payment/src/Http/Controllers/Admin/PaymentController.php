<?php

namespace Webkul\Payment\Http\Controllers\Admin;

use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Payment\Models\Payment;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::query()
            ->with(['customer', 'reviewer', 'payable'])
            ->latest()
            ->paginate(20);

        return view('payment::admin.index', compact('payments'));
    }

    public function view(int $id)
    {
        $payment = Payment::query()
            ->with(['customer', 'reviewer', 'payable'])
            ->findOrFail($id);

        return view('payment::admin.view', compact('payment'));
    }
}
