<?php

namespace Webkul\BankTransfer\Tests\Feature;

use Webkul\Admin\Tests\AdminTestCase;

class AdminBankTransferDataGridTest extends AdminTestCase
{
    public function test_admin_bank_transfer_datagrid_ajax_request_returns_successfully(): void
    {
        $this->loginAsAdmin();

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->get(route('admin.sales.bank-transfers.index', [
            'pagination' => [
                'page' => 1,
                'per_page' => 10,
            ],
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'columns',
            'records',
            'meta' => [
                'current_page',
                'per_page',
                'total',
            ],
        ]);
    }
}
