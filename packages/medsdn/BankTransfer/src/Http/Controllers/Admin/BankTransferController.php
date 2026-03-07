<?php

namespace Webkul\BankTransfer\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\BankTransfer\DataGrids\BankTransferDataGrid;
use Webkul\BankTransfer\Repositories\BankTransferRepository;

class BankTransferController extends Controller
{
    public function __construct(
        protected BankTransferRepository $bankTransferRepository
    ) {
    }

    public function index()
    {
        if (request()->ajax()) {
            return app(BankTransferDataGrid::class)->toJson();
        }

        return view('banktransfer::admin.index');
    }

    public function view($id)
    {
        $payment = $this->bankTransferRepository
            ->with(['payment', 'order', 'customer', 'reviewer'])
            ->findOrFail($id);

        return view('banktransfer::admin.view', compact('payment'));
    }

    public function downloadFile($id)
    {
        $payment = $this->bankTransferRepository->findOrFail($id);

        if (! Storage::disk('private')->exists($payment->slip_path)) {
            abort(404, trans('banktransfer::app.admin.errors.file-not-found'));
        }

        return Storage::disk('private')->download($payment->slip_path);
    }

    public function approve($id): JsonResponse
    {
        try {
            $this->bankTransferRepository->approve(
                $id,
                auth()->guard('admin')->id(),
                request('admin_note')
            );

            return response()->json([
                'success' => true,
                'message' => trans('banktransfer::app.admin.messages.approved'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Bank Transfer approval failed', [
                'method' => 'approve',
                'payment_id' => $id,
                'admin_id' => auth()->guard('admin')->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('banktransfer::app.admin.errors.approval-failed'),
            ], 422);
        }
    }

    public function reject($id): JsonResponse
    {
        // Validate admin note is required and non-empty
        $validator = validator(request()->all(), [
            'admin_note' => 'required|string|min:1|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => trans('banktransfer::app.admin.errors.admin-note-required'),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $this->bankTransferRepository->reject(
                $id,
                auth()->guard('admin')->id(),
                request('admin_note')
            );

            return response()->json([
                'success' => true,
                'message' => trans('banktransfer::app.admin.messages.rejected'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Bank Transfer rejection failed', [
                'method' => 'reject',
                'payment_id' => $id,
                'admin_id' => auth()->guard('admin')->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('banktransfer::app.admin.errors.rejection-failed'),
            ], 422);
        }
    }
}
