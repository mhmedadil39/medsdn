<?php

namespace Webkul\BankTransfer\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class BankTransferDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('bank_transfer_payments')
            ->leftJoin('orders', 'bank_transfer_payments.order_id', '=', 'orders.id')
            ->leftJoin('customers', 'bank_transfer_payments.customer_id', '=', 'customers.id')
            ->leftJoin('admins', 'bank_transfer_payments.reviewed_by', '=', 'admins.id')
            ->select(
                'bank_transfer_payments.id',
                'bank_transfer_payments.order_id',
                'orders.increment_id as order_number',
                'orders.base_grand_total as order_total',
                'bank_transfer_payments.transaction_reference',
                'bank_transfer_payments.status',
                'bank_transfer_payments.created_at',
                DB::raw('CONCAT('.DB::getTablePrefix().'customers.first_name, " ", '.DB::getTablePrefix().'customers.last_name) as customer_name'),
                'customers.email as customer_email',
                'admins.name as reviewed_by_name'
            );

        $this->addFilter('customer_name', DB::raw('CONCAT('.DB::getTablePrefix().'customers.first_name, " ", '.DB::getTablePrefix().'customers.last_name)'));
        $this->addFilter('created_at', 'bank_transfer_payments.created_at');
        $this->addFilter('order_number', 'orders.increment_id');

        return $queryBuilder;
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('banktransfer::app.admin.datagrid.id'),
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'order_number',
            'label' => trans('banktransfer::app.admin.datagrid.order-number'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'customer_name',
            'label' => trans('banktransfer::app.admin.datagrid.customer-name'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'customer_email',
            'label' => trans('banktransfer::app.admin.datagrid.customer-email'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'order_total',
            'label' => trans('banktransfer::app.admin.datagrid.order-total'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return core()->formatBasePrice($row->order_total);
            },
        ]);

        $this->addColumn([
            'index' => 'transaction_reference',
            'label' => trans('banktransfer::app.admin.datagrid.transaction-reference'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return $row->transaction_reference ?: '-';
            },
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('banktransfer::app.admin.datagrid.status'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('banktransfer::app.admin.datagrid.pending'),
                    'value' => 'pending',
                ],
                [
                    'label' => trans('banktransfer::app.admin.datagrid.approved'),
                    'value' => 'approved',
                ],
                [
                    'label' => trans('banktransfer::app.admin.datagrid.rejected'),
                    'value' => 'rejected',
                ],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                switch ($row->status) {
                    case 'pending':
                        return '<p class="label-pending">'.trans('banktransfer::app.admin.datagrid.pending').'</p>';

                    case 'approved':
                        return '<p class="label-active">'.trans('banktransfer::app.admin.datagrid.approved').'</p>';

                    case 'rejected':
                        return '<p class="label-canceled">'.trans('banktransfer::app.admin.datagrid.rejected').'</p>';

                    default:
                        return '<p class="label-pending">'.ucfirst($row->status).'</p>';
                }
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('banktransfer::app.admin.datagrid.created-at'),
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('sales.bank_transfers')) {
            $this->addAction([
                'icon' => 'icon-view',
                'title' => trans('banktransfer::app.admin.datagrid.view'),
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.sales.bank-transfers.view', $row->id);
                },
            ]);
        }
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        // Mass actions can be added here if needed in the future
    }
}
