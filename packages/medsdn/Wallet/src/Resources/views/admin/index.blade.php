<x-admin::layouts>
    <x-slot:title>
        Wallets
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Wallets
        </p>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.customers.wallets.index', ['action' => 'credit']) }}#wallet-management-form"
                class="primary-button"
            >
                Add Credit
            </a>

            <a
                href="{{ route('admin.customers.wallets.index', ['action' => 'debit']) }}#wallet-management-form"
                class="secondary-button"
            >
                Debit
            </a>
        </div>
    </div>

    <div id="wallet-management-form" class="mt-4 rounded box-shadow bg-white dark:bg-gray-900">
        <p class="border-b p-4 font-semibold text-gray-800 dark:border-gray-800 dark:text-white">
            Wallet Management
        </p>

        <x-admin::form
            method="POST"
            :action="route('admin.customers.wallets.manage')"
            class="grid gap-4 p-4 md:grid-cols-2"
        >
            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    Customer
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="select"
                    name="customer_id"
                    rules="required"
                >
                    <option value="">Select a customer</option>

                    @foreach ($customers as $customer)
                        <option
                            value="{{ $customer->id }}"
                            @selected($selectedCustomerId === $customer->id)
                        >
                            {{ trim($customer->first_name.' '.$customer->last_name) }} ({{ $customer->email }})
                        </option>
                    @endforeach
                </x-admin::form.control-group.control>

                <x-admin::form.control-group.error control-name="customer_id" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    Action
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="select"
                    name="action_type"
                    rules="required"
                >
                    <option value="credit" @selected($selectedAction === 'credit')>Add Credit</option>
                    <option value="debit" @selected($selectedAction === 'debit')>Debit</option>
                </x-admin::form.control-group.control>

                <x-admin::form.control-group.error control-name="action_type" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    Amount
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="number"
                    name="amount"
                    step="0.01"
                    rules="required"
                />

                <x-admin::form.control-group.error control-name="amount" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    Description
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="textarea"
                    name="description"
                    rows="3"
                    rules="required"
                />

                <x-admin::form.control-group.error control-name="description" />
            </x-admin::form.control-group>

            <div class="md:col-span-2 flex items-center gap-2">
                <button type="submit" class="primary-button">
                    Apply Wallet Action
                </button>

                <a
                    href="{{ route('admin.sales.wallet_transactions.index') }}"
                    class="transparent-button"
                >
                    Transactions
                </a>
            </div>
        </x-admin::form>
    </div>

    <div class="mt-6 rounded box-shadow bg-white dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="border-b dark:border-gray-800">
                    <tr class="[&>*]:px-4 [&>*]:py-3 text-sm text-gray-600 dark:text-gray-300">
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Currency</th>
                        <th>Balance</th>
                        <th>Available</th>
                        <th>Held</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($wallets as $wallet)
                        <tr class="border-b align-top dark:border-gray-800 [&>*]:px-4 [&>*]:py-3">
                            <td>#{{ $wallet->id }}</td>
                            <td>{{ $wallet->customer?->name ?? '-' }}</td>
                            <td>{{ $wallet->currency }}</td>
                            <td>{{ core()->formatBasePrice((float) $wallet->balance) }}</td>
                            <td>{{ core()->formatBasePrice((float) $wallet->available_balance) }}</td>
                            <td>{{ core()->formatBasePrice((float) $wallet->held_balance) }}</td>
                            <td>{{ ucfirst($wallet->status->value) }}</td>
                            <td class="whitespace-nowrap">
                                <a
                                    href="{{ route('admin.customers.wallets.view', $wallet->id) }}"
                                    class="text-blue-600"
                                >
                                    View Wallet
                                </a>

                                <span class="mx-2 text-gray-300">|</span>

                                <a
                                    href="{{ route('admin.sales.wallet_transactions.index', ['wallet_id' => $wallet->id, 'customer_id' => $wallet->customer_id]) }}"
                                    class="text-blue-600"
                                >
                                    Transactions
                                </a>

                                <span class="mx-2 text-gray-300">|</span>

                                <a
                                    href="{{ route('admin.customers.wallets.index', ['customer_id' => $wallet->customer_id, 'action' => 'credit']) }}#wallet-management-form"
                                    class="text-blue-600"
                                >
                                    Add Credit
                                </a>

                                <span class="mx-2 text-gray-300">|</span>

                                <a
                                    href="{{ route('admin.customers.wallets.index', ['customer_id' => $wallet->customer_id, 'action' => 'debit']) }}#wallet-management-form"
                                    class="text-blue-600"
                                >
                                    Debit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">
                                No wallets found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $wallets->links() }}
    </div>
</x-admin::layouts>
