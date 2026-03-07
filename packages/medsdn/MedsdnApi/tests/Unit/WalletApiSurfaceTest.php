<?php

namespace Webkul\MedsdnApi\Tests\Unit;

use PHPUnit\Framework\TestCase;

class WalletApiSurfaceTest extends TestCase
{
    public function test_duplicate_aggregate_wallet_resource_is_not_present(): void
    {
        $this->assertFileDoesNotExist($this->path('src/Models/Wallet.php'));
    }

    public function test_wallet_topup_resource_uses_generic_wallet_topups_contract(): void
    {
        $source = file_get_contents($this->path('src/Models/WalletTopup.php'));

        $this->assertStringContainsString("uriTemplate: '/wallet/topups'", $source);
        $this->assertStringContainsString('output: WalletTopupOutput::class', $source);
    }

    public function test_wallet_graphql_queries_use_the_stable_v1_names(): void
    {
        $walletSummary = file_get_contents($this->path('src/Models/WalletSummary.php'));
        $customerPayments = file_get_contents($this->path('src/Models/CustomerPayment.php'));
        $bankTransferPayments = file_get_contents($this->path('src/Models/BankTransferPayment.php'));

        $this->assertStringContainsString("name: 'wallet'", $walletSummary);
        $this->assertStringContainsString("name: 'payments'", $customerPayments);
        $this->assertStringContainsString("name: 'payment'", $customerPayments);
        $this->assertStringNotContainsString("name: 'payments'", $bankTransferPayments);
        $this->assertStringNotContainsString("name: 'payment'", $bankTransferPayments);
    }

    public function test_wallet_transaction_provider_supports_single_item_requests(): void
    {
        $source = file_get_contents($this->path('src/State/WalletTransactionProvider.php'));

        $this->assertStringContainsString("if (isset(\$uriVariables['id']))", $source);
    }

    protected function path(string $suffix): string
    {
        return dirname(__DIR__, 2).'/'.$suffix;
    }
}
