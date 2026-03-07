<v-mobile-mega-search>
    <i class="icon-search flex items-center text-2xl"></i>
</v-mobile-mega-search>

<script
    type="text/x-template"
    id="v-mobile-mega-search-template"
>
        <div>
            <i
                class="icon-search flex items-center text-2xl"
                @click="toggleSearchInput"
                v-show="!isSearchVisible"
            ></i>

            <div
                v-show="isSearchVisible"
                class="absolute left-1/2 top-3 z-[10002] flex w-full max-w-full -translate-x-1/2 items-center px-2"
            >
                <i class="icon-search absolute top-2 flex items-center text-2xl ltr:left-4 rtl:right-4"></i>

                <input
                    type="text"
                    class="peer block w-full rounded-3xl border bg-white px-10 py-1.5 leading-6 text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                    :class="{'border-gray-400': isDropdownOpen}"
                    placeholder="@lang('admin::app.components.layouts.header.mega-search.title')"
                    v-model.lazy="searchTerm"
                    @click="searchTerm.length >= 2 ? isDropdownOpen = true : {}"
                    v-debounce="500"
                    ref="searchInput"
                >

                <i
                    class="icon-cross absolute top-2 flex items-center text-2xl ltr:right-4 rtl:left-4"
                    @click="toggleSearchInput"
                ></i>

                <div
                    class="absolute left-[6px] right-[6px] top-10 z-10 max-h-[80vh] overflow-y-auto rounded-lg border bg-white shadow-[0px_0px_0px_0px_rgba(0,0,0,0.10),0px_1px_3px_0px_rgba(0,0,0,0.10),0px_5px_5px_0px_rgba(0,0,0,0.09),0px_12px_7px_0px_rgba(0,0,0,0.05),0px_22px_9px_0px_rgba(0,0,0,0.01),0px_34px_9px_0px_rgba(0,0,0,0.00)] dark:border-gray-800 dark:bg-gray-900"
                    v-if="isDropdownOpen"
                >
                    <div class="flex overflow-x-auto border-b text-sm text-gray-600 dark:border-gray-800 dark:text-gray-300">
                        <div
                            class="flex-shrink-0 cursor-pointer whitespace-nowrap px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-950"
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
                            <div class="grid max-h-[400px] divide-y divide-slate-200 overflow-y-auto dark:divide-gray-800">
                                <a
                                    :href="'{{ route('admin.catalog.products.edit', ':id') }}'.replace(':id', product.id)"
                                    class="flex flex-col gap-2.5 p-4 hover:bg-gray-100 dark:hover:bg-gray-950"
                                    v-for="product in searchedResults.products.data"
                                >
                                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">
                                        @{{ product.name }}
                                    </p>

                                    <p class="text-sm text-gray-500">
                                        @{{ "@lang('admin::app.components.layouts.header.mega-search.sku')".replace(':sku', product.sku) }}
                                    </p>

                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        @{{ product.formatted_price }}
                                    </p>
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
                                    class="grid gap-1.5 border-b border-slate-300 p-4 last:border-b-0 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-gray-950"
                                    v-for="order in searchedResults.orders.data"
                                >
                                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">
                                        #@{{ order.increment_id }}
                                    </p>

                                    <p class="text-sm text-gray-500 dark:text-gray-300">
                                        @{{ order.formatted_created_at + ', ' + order.status_label }}
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
                                    class="border-b border-slate-300 p-4 text-sm font-semibold text-gray-600 last:border-b-0 hover:bg-gray-100 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950"
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
                                    class="grid gap-1.5 border-b border-slate-300 p-4 last:border-b-0 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-gray-950"
                                    v-for="customer in searchedResults.customers.data"
                                >
                                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">
                                        @{{ customer.first_name + ' ' + customer.last_name }}
                                    </p>

                                    <p class="text-sm text-gray-500">
                                        @{{ customer.email }}
                                    </p>
                                </a>
                            </div>
                        </template>
                    </template>
                </div>
            </div>
        </div>
    </script>

<script type="module">
    app.component('v-mobile-mega-search', {
        template: '#v-mobile-mega-search-template',

            data() {
                return {
                    isSearchVisible: false,
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

            methods: {
                toggleSearchInput() {
                    this.isSearchVisible = ! this.isSearchVisible;
                    this.isDropdownOpen = this.isSearchVisible && this.searchTerm.length > 1;

                    if (this.isSearchVisible) {
                        this.$nextTick(() => this.$refs.searchInput?.focus());
                    }
                },

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
            },
    });
</script>
