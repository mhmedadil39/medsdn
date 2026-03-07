<x-admin::layouts>
    <x-slot:title>
        @lang('banktransfer::app.admin.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('banktransfer::app.admin.index.title')
        </p>
    </div>

    <x-admin::datagrid :src="route('admin.sales.bank-transfers.index')" />
</x-admin::layouts>
