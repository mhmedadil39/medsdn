@if (
    request()->routeIs('shop.checkout.onepage.index')
    && (bool) core()->getConfigData('sales.payment_methods.banktransfer.active')
)
    @php
        $paymentMethod = payment()->getPaymentMethod('banktransfer');
        $bankAccounts = $paymentMethod ? $paymentMethod->getBankAccounts() : [];
        $instructions = core()->getConfigData('sales.payment_methods.banktransfer.instructions');
    @endphp

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-bank-transfer-payment-template"
        >
            <div class="mt-8">
                {!! view_render_event('banktransfer.shop.checkout.payment.before') !!}

                <!-- Bank Accounts Section -->
                <div class="mb-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                        @lang('banktransfer::app.shop.checkout.bank-accounts-title')
                    </h3>

                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                        @lang('banktransfer::app.shop.checkout.bank-accounts-description')
                    </p>

                    <!-- Bank Account Cards -->
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="(account, index) in bankAccounts"
                            :key="index"
                            class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div class="mb-3 flex items-center justify-between">
                                <h4 class="font-semibold text-gray-800 dark:text-white">
                                    @{{ account.bank_name }}
                                </h4>
                                <span class="rounded bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                    @lang('banktransfer::app.shop.checkout.account') @{{ index + 1 }}
                                </span>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div v-if="account.branch_name">
                                    <span class="text-gray-500 dark:text-gray-400">@lang('banktransfer::app.shop.checkout.branch'):</span>
                                    <span class="ml-1 text-gray-800 dark:text-gray-200">@{{ account.branch_name }}</span>
                                </div>

                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">@lang('banktransfer::app.shop.checkout.account-holder'):</span>
                                    <span class="ml-1 text-gray-800 dark:text-gray-200">@{{ account.account_holder }}</span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">@lang('banktransfer::app.shop.checkout.account-number'):</span>
                                        <span class="ml-1 font-mono text-gray-800 dark:text-gray-200">@{{ account.account_number }}</span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="copyToClipboard(account.account_number, 'account')"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                        :title="trans('banktransfer::app.shop.checkout.copy')"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>

                                <div v-if="account.iban" class="flex items-center justify-between">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">@lang('banktransfer::app.shop.checkout.iban'):</span>
                                        <span class="ml-1 font-mono text-gray-800 dark:text-gray-200">@{{ account.iban }}</span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="copyToClipboard(account.iban, 'iban')"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                        :title="trans('banktransfer::app.shop.checkout.copy')"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transfer Instructions -->
                <div v-if="instructions" class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                    <div class="flex">
                        <svg class="mr-3 h-6 w-6 flex-shrink-0 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h4 class="mb-2 font-semibold text-blue-800 dark:text-blue-300">
                                @lang('banktransfer::app.shop.checkout.instructions-title')
                            </h4>
                            <div class="text-sm text-blue-700 dark:text-blue-300 whitespace-pre-line" v-text="instructions"></div>
                        </div>
                    </div>
                </div>

                {!! view_render_event('banktransfer.shop.checkout.payment.after') !!}
            </div>
        </script>

        <script type="module">
            app.component('v-bank-transfer-payment', {
                template: '#v-bank-transfer-payment-template',

                data() {
                    return {
                        bankAccounts: @json($bankAccounts),
                        instructions: @json($instructions),
                    };
                },

                methods: {
                    copyToClipboard(text, type) {
                        navigator.clipboard.writeText(text).then(() => {
                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: '@lang('banktransfer::app.shop.checkout.copied-to-clipboard')'
                            });
                        }).catch(() => {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: '@lang('banktransfer::app.shop.checkout.copy-failed')'
                            });
                        });
                    },

                    trans(key) {
                        return window.trans ? window.trans(key) : key;
                    }
                },
            });
        </script>
    @endPushOnce

    <!-- Render Component -->
    <v-bank-transfer-payment></v-bank-transfer-payment>
@endif
