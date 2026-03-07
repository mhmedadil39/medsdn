<?php

namespace Tests\Unit\Project;

use PHPUnit\Framework\TestCase;

class NewWalletPaymentCodeAvoidsConcordProxiesTest extends TestCase
{
    public function test_new_wallet_payment_and_bank_transfer_code_does_not_depend_on_new_concord_proxies(): void
    {
        $targets = [
            'packages/medsdn/Customer/src/Models/Customer.php',
            'packages/medsdn/Sales/src/Models/Order.php',
            'packages/medsdn/Wallet/src/Services/WalletService.php',
            'packages/medsdn/Wallet/src/Actions/CreateWalletTransactionAction.php',
            'packages/medsdn/Wallet/src/Http/Controllers/Admin/WalletController.php',
            'packages/medsdn/Payment/src/Services/PaymentService.php',
            'packages/medsdn/Payment/src/Http/Controllers/Admin/PaymentController.php',
            'packages/medsdn/Shop/src/Http/Controllers/Customer/Account/WalletController.php',
            'packages/medsdn/MedsdnApi/src/State/CustomerPaymentProvider.php',
            'packages/medsdn/MedsdnApi/src/State/WalletTransactionProvider.php',
            'packages/medsdn/BankTransfer/src/Models/BankTransferPayment.php',
            'packages/medsdn/Wallet/src/Models/Wallet.php',
            'packages/medsdn/Wallet/src/Models/WalletHold.php',
            'packages/medsdn/Wallet/src/Models/WalletTransaction.php',
        ];

        $forbiddenPatterns = [
            '/\bPaymentProxy::(?:query|modelClass)\(/',
            '/\bWalletProxy::(?:query|modelClass)\(/',
            '/\bWalletTransactionProxy::(?:query|modelClass)\(/',
            '/\bWalletHoldProxy::(?:query|modelClass)\(/',
        ];

        foreach ($targets as $target) {
            $source = file_get_contents(dirname(__DIR__, 3).'/'.$target);

            foreach ($forbiddenPatterns as $pattern) {
                $this->assertSame(0, preg_match($pattern, $source), $target.' should not match '.$pattern);
            }
        }
    }
}
