<x-shop::layouts.account>
    <x-slot:title>
        Top up wallet
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="mx-4 flex-auto max-md:mx-6 max-sm:mx-4">
        <div class="mb-8 max-md:flex max-md:items-center">
            <a
                class="grid md:hidden"
                href="{{ route('shop.customers.account.wallet.index') }}"
            >
                <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
            </a>

            <h2 class="text-2xl font-medium max-sm:text-base ltr:ml-2.5 md:ltr:ml-0 rtl:mr-2.5 md:rtl:mr-0">
                Top up wallet
            </h2>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <h3 class="text-lg font-semibold">Submit bank transfer proof</h3>
                <p class="mt-2 text-sm text-zinc-500">Your request will stay pending until an administrator reviews and approves it.</p>

                <x-shop::form
                    method="POST"
                    enctype="multipart/form-data"
                    :action="route('shop.customers.account.wallet.topup.store')"
                    class="mt-6 space-y-4"
                >
                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label class="required">
                            Amount
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="number"
                            name="amount"
                            min="0.01"
                            step="0.01"
                            rules="required|decimal|min_value:0.01"
                            :value="old('amount')"
                        />

                        <x-shop::form.control-group.error control-name="amount" />
                    </x-shop::form.control-group>

                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label>
                            Destination bank
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="text"
                            name="bank_name"
                            :value="old('bank_name')"
                        />
                    </x-shop::form.control-group>

                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label class="required">
                            Transaction reference
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="text"
                            name="transaction_reference"
                            rules="required"
                            :value="old('transaction_reference')"
                        />

                        <x-shop::form.control-group.error control-name="transaction_reference" />
                    </x-shop::form.control-group>

                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label class="required">
                            Payment proof
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="file"
                            name="payment_proof"
                            accept=".jpg,.jpeg,.png,.webp,.pdf"
                            rules="required"
                        />

                        <x-shop::form.control-group.error control-name="payment_proof" />
                    </x-shop::form.control-group>

                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label>
                            Notes
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="textarea"
                            name="notes"
                        >{{ old('notes') }}</x-shop::form.control-group.control>
                    </x-shop::form.control-group>

                    <button
                        type="submit"
                        class="primary-button"
                    >
                        Submit topup request
                    </button>
                </x-shop::form>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <h3 class="text-lg font-semibold">Bank transfer details</h3>

                @if ($instructions)
                    <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900 whitespace-pre-line">{{ $instructions }}</div>
                @endif

                <div class="mt-6 space-y-4">
                    @forelse ($bankAccounts as $account)
                        <div class="rounded-xl border border-zinc-200 p-4">
                            <p class="font-semibold text-zinc-900">{{ $account['bank_name'] ?? 'Bank account' }}</p>
                            <p class="mt-2 text-sm text-zinc-600">Account holder: {{ $account['account_holder'] ?? '-' }}</p>
                            <p class="mt-1 text-sm text-zinc-600">Account number: {{ $account['account_number'] ?? '-' }}</p>
                            @if (! empty($account['iban']))
                                <p class="mt-1 text-sm text-zinc-600">IBAN: {{ $account['iban'] }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500">No bank accounts configured for bank transfer.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-shop::layouts.account>
