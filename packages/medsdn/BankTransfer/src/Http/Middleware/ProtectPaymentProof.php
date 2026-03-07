<?php

namespace Webkul\BankTransfer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ProtectPaymentProof
{
    /**
     * Handle an incoming request to protect payment proof file access.
     *
     * Security measures:
     * - Verify user is authenticated as admin
     * - Check admin has sales.bank_transfers permission
     * - Log all file access attempts for audit trail
     * - Return 403 Forbidden for unauthorized access
     * - Prevent direct URL access to files
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log access attempt for security audit
        Log::info('Bank Transfer file access attempt', [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'user_id' => auth()->id(),
            'is_authenticated' => auth()->check(),
            'is_admin' => auth()->guard('admin')->check(),
        ]);

        // Check if user is authenticated as admin
        if (! auth()->guard('admin')->check()) {
            Log::warning('Bank Transfer file access denied - not authenticated', [
                'ip_address' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            return response()->json([
                'message' => trans('banktransfer::app.admin.errors.unauthorized-access'),
            ], 403);
        }

        // Get authenticated admin user
        $admin = auth()->guard('admin')->user();

        // Check if admin has permission to view bank transfers
        if (! bouncer()->hasPermission('sales.bank_transfers')) {
            Log::warning('Bank Transfer file access denied - insufficient permissions', [
                'admin_id' => $admin->id,
                'ip_address' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            return response()->json([
                'message' => trans('banktransfer::app.admin.errors.insufficient-permissions'),
            ], 403);
        }

        // Log successful authorization
        Log::info('Bank Transfer file access authorized', [
            'admin_id' => $admin->id,
            'ip_address' => $request->ip(),
            'url' => $request->fullUrl(),
        ]);

        return $next($request);
    }
}
