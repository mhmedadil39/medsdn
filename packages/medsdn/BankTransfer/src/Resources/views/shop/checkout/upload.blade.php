@extends('shop::layouts.master')

@section('page_title')
    @lang('banktransfer::app.shop.upload.page-title')
@endsection

@section('content-wrapper')
    <div class="container mt-8 px-[60px] max-lg:px-[30px] max-sm:px-[15px]">
        <div class="mx-auto max-w-4xl">
            <!-- Page Title -->
            <h1 class="mb-6 text-3xl font-semibold text-gray-800 dark:text-white">
                @lang('banktransfer::app.shop.upload.title')
            </h1>

            <!-- Upload Form Component -->
            <v-bank-transfer-upload></v-bank-transfer-upload>
        </div>
    </div>
@endsection

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-bank-transfer-upload-template"
    >
        <div>
            <!-- Bank Accounts Section -->
            <div class="mb-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-xl font-semibold text-gray-800 dark:text-white">
                    @lang('banktransfer::app.shop.checkout.bank-accounts-title')
                </h2>

                <p class="mb-6 text-sm text-gray-600 dark:text-gray-300">
                    @lang('banktransfer::app.shop.checkout.bank-accounts-description')
                </p>

                <!-- Bank Account Cards -->
                <div class="grid gap-4 md:grid-cols-2">
                    <div
                        v-for="(account, index) in bankAccounts"
                        :key="index"
                        class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800 dark:text-white">
                                @{{ account.bank_name }}
                            </h3>
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
                                    @click="copyToClipboard(account.account_number)"
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
                                    @click="copyToClipboard(account.iban)"
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

                <!-- Transfer Instructions -->
                <div v-if="instructions" class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
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
            </div>

            <!-- Upload Form -->
            <form @submit.prevent="submitForm" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-6 text-xl font-semibold text-gray-800 dark:text-white">
                    @lang('banktransfer::app.shop.checkout.upload-proof-title')
                </h2>

                <!-- Drag and Drop Zone -->
                <div class="mb-6">
                    <div
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop"
                        :class="{'border-blue-500 bg-blue-50 dark:bg-blue-900/20': isDragging}"
                        class="relative rounded-lg border-2 border-dashed border-gray-300 p-8 text-center transition-colors dark:border-gray-600"
                    >
                        <input
                            type="file"
                            ref="fileInput"
                            @change="handleFileSelect"
                            accept=".jpg,.jpeg,.png,.webp,.pdf"
                            class="hidden"
                            id="payment-proof-file"
                        />

                        <div v-if="!selectedFile">
                            <svg class="mx-auto mb-4 h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <p class="mb-2 text-sm text-gray-600 dark:text-gray-300">
                                <label for="payment-proof-file" class="cursor-pointer font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                    @lang('banktransfer::app.shop.checkout.click-to-upload')
                                </label>
                                @lang('banktransfer::app.shop.checkout.or-drag-and-drop')
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('banktransfer::app.shop.checkout.file-types')
                            </p>
                        </div>

                        <div v-else class="flex items-center justify-center">
                            <div class="flex items-center space-x-3 rounded-lg bg-white p-4 shadow dark:bg-gray-700">
                                <svg v-if="isPdf" class="h-10 w-10 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" />
                                </svg>
                                <svg v-else class="h-10 w-10 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                </svg>
                                <div class="text-left">
                                    <p class="font-medium text-gray-800 dark:text-white">@{{ selectedFile.name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">@{{ formatFileSize(selectedFile.size) }}</p>
                                </div>
                                <button
                                    type="button"
                                    @click="removeFile"
                                    class="text-red-600 hover:text-red-800 dark:text-red-400"
                                >
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <p v-if="fileError" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            @{{ fileError }}
                        </p>
                    </div>
                </div>

                <!-- Transaction Reference -->
                <div class="mb-6">
                    <label for="transaction-reference" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        @lang('banktransfer::app.shop.checkout.transaction-reference')
                        <span class="text-gray-500">(@lang('banktransfer::app.shop.checkout.optional'))</span>
                    </label>
                    <input
                        type="text"
                        id="transaction-reference"
                        v-model="transactionReference"
                        :placeholder="trans('banktransfer::app.shop.checkout.transaction-reference-placeholder')"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between">
                    <a
                        href="{{ route('shop.checkout.onepage.index') }}"
                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400"
                    >
                        @lang('banktransfer::app.shop.upload.back-to-checkout')
                    </a>

                    <button
                        type="submit"
                        :disabled="!selectedFile || isUploading"
                        class="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span v-if="!isUploading">@lang('banktransfer::app.shop.upload.submit')</span>
                        <span v-else>@lang('banktransfer::app.shop.upload.uploading')</span>
                    </button>
                </div>
            </form>
        </div>
    </script>

    <script type="module">
        app.component('v-bank-transfer-upload', {
            template: '#v-bank-transfer-upload-template',

            data() {
                return {
                    bankAccounts: @json($bankAccounts ?? []),
                    instructions: @json($instructions ?? ''),
                    selectedFile: null,
                    fileError: null,
                    isDragging: false,
                    transactionReference: '',
                    isUploading: false,
                    isPdf: false,
                };
            },

            methods: {
                copyToClipboard(text) {
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

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    this.validateAndSetFile(file);
                },

                handleDrop(event) {
                    this.isDragging = false;
                    const file = event.dataTransfer.files[0];
                    this.validateAndSetFile(file);
                },

                validateAndSetFile(file) {
                    this.fileError = null;

                    if (!file) {
                        return;
                    }

                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'application/pdf'];
                    if (!allowedTypes.includes(file.type)) {
                        this.fileError = '@lang('banktransfer::app.shop.checkout.invalid-file-type')';
                        return;
                    }

                    // Validate file size (4MB)
                    const maxSize = 4 * 1024 * 1024;
                    if (file.size > maxSize) {
                        this.fileError = '@lang('banktransfer::app.shop.checkout.file-too-large')';
                        return;
                    }

                    this.selectedFile = file;
                    this.isPdf = file.type === 'application/pdf';
                },

                removeFile() {
                    this.selectedFile = null;
                    this.fileError = null;
                    this.isPdf = false;
                    this.$refs.fileInput.value = '';
                },

                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
                },

                async submitForm() {
                    if (!this.selectedFile || this.isUploading) {
                        return;
                    }

                    this.isUploading = true;
                    this.fileError = null;

                    const formData = new FormData();
                    formData.append('payment_proof', this.selectedFile);
                    formData.append('transaction_reference', this.transactionReference);
                    formData.append('_token', '{{ csrf_token() }}');

                    try {
                        const response = await this.$axios.post(
                            '{{ route('shop.checkout.bank-transfer.upload') }}',
                            formData,
                            {
                                headers: {
                                    'Content-Type': 'multipart/form-data'
                                }
                            }
                        );

                        if (response.data.success) {
                            window.location.href = response.data.redirect_url;
                        }
                    } catch (error) {
                        this.isUploading = false;

                        if (error.response?.data?.message) {
                            this.fileError = error.response.data.message;
                        } else {
                            this.fileError = '@lang('banktransfer::app.shop.errors.upload-failed')';
                        }

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: this.fileError
                        });
                    }
                },

                trans(key) {
                    return window.trans ? window.trans(key) : key;
                }
            },
        });
    </script>
@endPushOnce
