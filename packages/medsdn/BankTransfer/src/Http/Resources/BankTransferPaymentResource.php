<?php

namespace Webkul\BankTransfer\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BankTransferPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'payment_id' => $this->payment_id,
            'order' => $this->when($this->relationLoaded('order'), function () {
                return [
                    'id' => $this->order->id,
                    'increment_id' => $this->order->increment_id,
                    'status' => $this->order->status,
                    'grand_total' => $this->order->grand_total,
                    'grand_total_formatted' => core()->formatPrice($this->order->grand_total, $this->order->order_currency_code),
                    'created_at' => $this->order->created_at->toIso8601String(),
                ];
            }),
            'customer_id' => $this->customer_id,
            'method_code' => $this->method_code,
            'transaction_reference' => $this->transaction_reference,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'payment_status' => $this->payment?->status?->value ?? null,
            'reviewed_by' => $this->reviewed_by,
            'reviewer' => $this->when($this->relationLoaded('reviewer') && $this->reviewer, function () {
                return [
                    'id' => $this->reviewer->id,
                    'name' => $this->reviewer->name,
                ];
            }),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'admin_note' => $this->admin_note,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'is_pending' => $this->isPending(),
            'is_approved' => $this->isApproved(),
            'is_rejected' => $this->isRejected(),
        ];
    }

    /**
     * Get status label.
     *
     * @return string
     */
    protected function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => trans('banktransfer::app.admin.datagrid.pending'),
            'approved' => trans('banktransfer::app.admin.datagrid.approved'),
            'rejected' => trans('banktransfer::app.admin.datagrid.rejected'),
            default => $this->status,
        };
    }
}
