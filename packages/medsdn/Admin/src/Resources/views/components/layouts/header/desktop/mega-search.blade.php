<v-mega-search>
    <div class="relative flex w-[550px] max-w-[550px] items-center max-lg:w-[400px] ltr:ml-2.5 rtl:mr-2.5">
        <i class="icon-search absolute top-2 flex items-center text-2xl ltr:left-3 rtl:right-3"></i>

        <input
            type="text"
            class="block w-full rounded-3xl border bg-white px-10 py-1.5 leading-6 text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
            placeholder="@lang('admin::app.components.layouts.header.mega-search.title')"
        >
    </div>
</v-mega-search>

<script
    type="text/x-template"
    id="v-mega-search-template"
>
        <div class="relative flex w-[550px] max-w-[550px] items-center max-lg:w-[400px] ltr:ml-2.5 rtl:mr-2.5">
            <i class="icon-search absolute top-2 flex items-center text-2xl ltr:left-3 rtl:right-3"></i>

            <input
                type="text"
                class="peer block w-full rounded-3xl border bg-white px-10 py-1.5 leading-6 text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                :class="{'border-gray-400': isDropdownOpen}"
                placeholder="@lang('admin::app.components.layouts.header.mega-search.title')"
                v-model.lazy="searchTerm"
                @click="searchTerm.length >= 2 ? isDropdownOpen = true : {}"
                v-debounce="500"
            >

            <div
                class="absolute top-10 z-10 w-full rounded-lg border bg-white shadow-[0px_0px_0px_0px_rgba(0,0,0,0.10),0px_1px_3px_0px_rgba(0,0,0,0.10),0px_5px_5px_0px_rgba(0,0,0,0.09),0px_12px_7px_0px_rgba(0,0,0,0.05),0px_22px_9px_0px_rgba(0,0,0,0.01),0px_34px_9px_0px_rgba(0,0,0,0.00)] dark:border-gray-800 dark:bg-gray-900"
                v-if="isDropdownOpen"
            >
                <div class="flex overflow-x-auto border-b text-sm text-gray-600 dark:border-gray-800 dark:text-gray-300">
                    <div
                        class="cursor-pointer p-4 hover:bg-gray-100 dark:hover:bg-gray-950"
                        :class="{ 'border-b-2 border-brandColor': activeTab === tab.key }"
                        v-for="tab in tabs"
                        @click="activeTab = tab.key; search();"
                    >
                        @{{ tab.title }}
                    </div>
                </div>

                <template v-if="activeTab === 'products'">
                    <template v-if="isLoading">
                        <x-admin::shimmer.header.mega-search.products />
                    </template>

                    <template v-else>
                        <div class="grid max-h-[400px] overflow-y-auto">
                            <a
                                :href="'{{ route('admin.catalog.products.edit', ':id') }}'.replace(':id', product.id)"
                                class="flex cursor-pointer justify-between gap-2.5 border-b border-slate-300 p-4 last:border-b-0 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-gray-950"
                                v-for="product in searchedResults.products.data"
                            >
                                <div class="flex gap-2.5">
                                    <div
                                        class="relative h-[60px] max-h-[60px] w-[60px] max-w-[60px] overflow-hidden rounded"
                                        :class="{'overflow-hidden rounded border border-dashed border-gray-300 dark:border-gray-800 dark:mix-blend-exclusion dark:invert': !product.images.length}"
                                    >
                                        <template v-if="!product.images.length">
                                            <img src="{{ bagisto_asset('images/product-placeholders/front.svg') }}" class="h-full w-full object-cover">
                                        </template>

                                        <template v-else>
                                            <img :src="product.images[0].url" class="h-full w-full object-cover">
                                        </template>
                                    </div>

                                    <div class="grid place-content-start gap-1.5">
                                        <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                                            @{{ product.name }}
                                        </p>

                                        <p class="text-sm text-gray-500">
                                            @{{ "@lang('admin::app.components.layouts.header.mega-search.sku')".replace(':sku', product.sku) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid place-content-center gap-1 text-right">
                                    <p class="font-semibold text-gray-600 dark:text-gray-300">
                                        @{{ product.formatted_price }}
                                    </p>
                                </div>
                            </a>
                        </div>
                    </template>
                </template>

                <template v-if="activeTab === 'orders'">
                    <template v-if="isLoading">
                        <x-admin::shimmer.header.mega-search.orders />
                    </template>

                    <template v-else>
                        <div class="grid max-h-[400px] overflow-y-auto">
                            <a
                                :href="'{{ route('admin.sales.orders.view', ':id') }}'.replace(':id', order.id)"
                                class="grid cursor-pointer place-content-start gap-1.5 border-b border-slate-300 p-4 last:border-b-0 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-gray-950"
                                v-for="order in searchedResults.orders.data"
                            >
                                <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                                    #@{{ order.increment_id }}
                                </p>

                                <p class="text-sm text-gray-500 dark:text-gray-300">
                                    @{{ order.formatted_created_at + ', ' + order.status_label + ', ' + order.customer_full_name }}
                                </p>
                            </a>
                        </div>
                    </template>
                </template>

                <template v-if="activeTab === 'categories'">
                    <template v-if="isLoading">
                        <x-admin::shimmer.header.mega-search.categories />
                    </template>

                    <template v-else>
                        <div class="grid max-h-[400px] overflow-y-auto">
                            <a
                                :href="'{{ route('admin.catalog.categories.edit', ':id') }}'.replace(':id', category.id)"
                                class="cursor-pointer border-b p-4 text-sm font-semibold text-gray-600 last:border-b-0 hover:bg-gray-100 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950"
                                v-for="category in searchedResults.categories.data"
                            >
                                @{{ category.name }}
                            </a>
                        </div>
                    </template>
                </template>

                <template v-if="activeTab === 'customers'">
                    <template v-if="isLoading">
                        <x-admin::shimmer.header.mega-search.customers />
                    </template>

                    <template v-else>
                        <div class="grid max-h-[400px] overflow-y-auto">
                            <a
                                :href="'{{ route('admin.customers.customers.view', ':id') }}'.replace(':id', customer.id)"
                                class="grid cursor-pointer place-content-start gap-1.5 border-b border-slate-300 p-4 last:border-b-0 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-gray-950"
                                v-for="customer in searchedResults.customers.data"
                            >
                                <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                                    @{{ customer.first_name + ' ' + customer.last_name }}
                                </p>

                                <p class="text-sm text-gray-500">
                                    @{{ customer.email }}
                                </p>
                            </a>
                        </div>
                    </template>
                </template>

                <div class="flex border-t p-3 dark:border-gray-800">
                    <a
                        v-if="activeTab === 'products' && searchedResults.products.data?.length"
                        :href="'{{ route('admin.catalog.products.index') }}?search=:query'.replace(':query', searchTerm)"
                        class="cursor-pointer text-xs font-semibold text-brandColor transition-all hover:underline"
                    >
                        @{{ "@lang('admin::app.components.layouts.header.mega-search.explore-all-matching-products')".replace(':query', searchTerm).replace(':count', searchedResults.products.meta.total) }}
                    </a>

                    <a
                        v-else-if="activeTab === 'orders' && searchedResults.orders.data?.length"
                        :href="'{{ route('admin.sales.orders.index') }}?search=:query'.replace(':query', searchTerm)"
                        class="cursor-pointer text-xs font-semibold text-brandColor transition-all hover:underline"
                    >
                        @{{ "@lang('admin::app.components.layouts.header.mega-search.explore-all-matching-orders')".replace(':query', searchTerm).replace(':count', searchedResults.orders.total) }}
                    </a>

                    <a
                        v-else-if="activeTab === 'categories' && searchedResults.categories.data?.length"
                        :href="'{{ route('admin.catalog.categories.index') }}?search=:query'.replace(':query', searchTerm)"
                        class="cursor-pointer text-xs font-semibold text-brandColor transition-all hover:underline"
                    >
                        @{{ "@lang('admin::app.components.layouts.header.mega-search.explore-all-matching-categories')".replace(':query', searchTerm).replace(':count', searchedResults.categories.total) }}
                    </a>

                    <a
                        v-else-if="activeTab === 'customers' && searchedResults.customers.data?.length"
                        :href="'{{ route('admin.customers.customers.index') }}?search=:query'.replace(':query', searchTerm)"
                        class="cursor-pointer text-xs font-semibold text-brandColor transition-all hover:underline"
                    >
                        @{{ "@lang('admin::app.components.layouts.header.mega-search.explore-all-matching-customers')".replace(':query', searchTerm).replace(':count', searchedResults.customers.total) }}
                    </a>
                </div>
            </div>
        </div>
    </script>

<script type="module">
    app.component('v-mega-search', {
        template: '#v-mega-search-template',

            data() {
                return {
                    activeTab: 'products',
                    isDropdownOpen: false,
                    isLoading: false,
                    searchTerm: '',
                    tabs: [
                        {
                            key: 'products',
                            title: "@lang('admin::app.components.layouts.header.mega-search.products')",
                            endpoint: "{{ route('admin.catalog.products.search') }}",
                        },
                        {
                            key: 'orders',
                            title: "@lang('admin::app.components.layouts.header.mega-search.orders')",
                            endpoint: "{{ route('admin.sales.orders.search') }}",
                        },
                        {
                            key: 'categories',
                            title: "@lang('admin::app.components.layouts.header.mega-search.categories')",
                            endpoint: "{{ route('admin.catalog.categories.search') }}",
                        },
                        {
                            key: 'customers',
                            title: "@lang('admin::app.components.layouts.header.mega-search.customers')",
                            endpoint: "{{ route('admin.customers.customers.search') }}",
                        },
                    ],
                    searchedResults: {
                        products: { data: [], meta: { total: 0 } },
                        orders: { data: [], total: 0 },
                        categories: { data: [], total: 0 },
                        customers: { data: [], total: 0 },
                    },
                };
            },

            watch: {
                searchTerm() {
                    this.search();
                },
            },

            created() {
                window.addEventListener('click', this.handleFocusOut);
            },

            beforeUnmount() {
                window.removeEventListener('click', this.handleFocusOut);
            },

            methods: {
                search() {
                    if (this.searchTerm.length <= 1) {
                        this.searchedResults[this.activeTab] = this.defaultResultFor(this.activeTab);
                        this.isDropdownOpen = false;

                        return;
                    }

                    this.isDropdownOpen = true;
                    this.isLoading = true;

                    this.$axios.get(this.tabs.find((tab) => tab.key === this.activeTab).endpoint, {
                        params: { query: this.searchTerm },
                    }).then((response) => {
                        this.searchedResults[this.activeTab] = response.data;
                        this.isLoading = false;
                    }).catch(() => {
                        this.isLoading = false;
                    });
                },

                defaultResultFor(tabKey) {
                    if (tabKey === 'products') {
                        return { data: [], meta: { total: 0 } };
                    }

                    return { data: [], total: 0 };
                },

                handleFocusOut(event) {
                    if (! this.$el.contains(event.target)) {
                        this.isDropdownOpen = false;
                    }
                },
            },
    });
</script>
