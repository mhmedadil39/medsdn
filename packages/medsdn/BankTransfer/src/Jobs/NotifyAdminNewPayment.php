<?php

namespace Webkul\BankTransfer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Webkul\BankTransfer\Models\BankTransferPayment;
use Webkul\User\Models\Admin;

class NotifyAdminNewPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     *
     * @param  \Webkul\BankTransfer\Models\BankTransferPayment  $payment
     * @return void
     */
    public function __construct(
        public BankTransferPayment $payment
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            // Get all admins with their roles
            $allAdmins = Admin::with('role')->get();

            // Filter admins who have bank transfer permission
            $admins = $allAdmins->filter(function ($admin) {
                if (!$admin->role) {
                    return false;
                }

                $permissions = $admin->role->permissions;
                
                // Check if permissions is an array and contains the required permission
                return is_array($permissions) && in_array('sales.bank_transfers', $permissions);
            });

            // If no admins with specific permission, use all admins
            if ($admins->isEmpty()) {
                $admins = $allAdmins;
            }

            // Send email notification to each admin
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(
                    new \Webkul\BankTransfer\Mail\NewPaymentForReview($this->payment)
                );
            }

            // Log notification sent
            Log::info('Admin notification sent for new bank transfer payment', [
                'payment_id' => $this->payment->id,
                'order_id' => $this->payment->order_id,
                'admin_count' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the job
            Log::error('Failed to send admin notification for bank transfer payment', [
                'payment_id' => $this->payment->id,
                'order_id' => $this->payment->order_id,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to allow retry
            throw $e;
        }
    }
}
