<?php

namespace Webkul\BankTransfer\Tests\Unit;

use PHPUnit\Framework\TestCase;

class BankTransferDataGridSourceTest extends TestCase
{
    public function test_created_at_datetime_column_uses_datetime_range_filter(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/src/DataGrids/BankTransferDataGrid.php');

        $this->assertStringContainsString("'index' => 'created_at'", $source);
        $this->assertStringContainsString("'type' => 'datetime'", $source);
        $this->assertStringContainsString("'filterable_type' => 'datetime_range'", $source);
    }
}
