@php
    $admin = auth()->guard('admin')->user();
@endphp

<header class="sticky top-0 z-[10001] flex items-center justify-between gap-1 border-b border-gray-200 bg-white px-4 py-2.5 transition-all dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-center gap-1.5">
        <x-admin::layouts.sidebar.mobile />

        <a href="{{ route('admin.dashboard.index') }}">
            @if ($logo = core()->getConfigData('general.design.admin_logo.logo_image'))
                <img
                    class="h-10"
                    src="{{ Storage::url($logo) }}"
                    alt="{{ config('app.name') }}"
                />
            @else
                <img
                    class="h-10"
                    src="{{ request()->cookie('dark_mode') ? bagisto_asset('images/dark-logo.svg') : bagisto_asset('images/logo.svg') }}"
                    data-role="brand-logo"
                    alt="{{ config('app.name') }}"
                />
            @endif
        </a>
    </div>

    <div class="flex items-center gap-1.5 max-md:hidden">
        @include('admin::components.layouts.header.desktop.mega-search')
        @include('admin::components.layouts.header.quick-creation')
    </div>

    <div class="flex items-center gap-2.5">
        <div class="md:hidden">
            @include('admin::components.layouts.header.mobile.mega-search')
        </div>

        <v-dark>
            <div class="flex">
                <span
                    class="{{ request()->cookie('dark_mode') ? 'icon-light' : 'icon-dark' }} cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
                ></span>
            </div>
        </v-dark>

        <div class="md:hidden">
            @include('admin::components.layouts.header.quick-creation')
        </div>

        <a
            href="{{ route('shop.home.index') }}"
            target="_blank"
            class="hidden md:flex"
        >
            <span
                class="icon-store cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
                title="@lang('admin::app.components.layouts.header.visit-shop')"
            ></span>
        </a>

        <v-notifications {{ $attributes }}>
            <span class="relative flex">
                <span
                    class="icon-notification cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
                    title="@lang('admin::app.components.layouts.header.notifications')"
                ></span>
            </span>
        </v-notifications>

        <x-admin::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'right' : 'left' }}">
            <x-slot:toggle>
                @if ($admin->image)
                    <button class="flex h-9 w-9 cursor-pointer overflow-hidden rounded-full hover:opacity-80 focus:opacity-80">
                        <img
                            src="{{ $admin->image_url }}"
                            class="h-full w-full object-cover"
                        />
                    </button>
                @else
                    <button class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-blue-400 font-semibold leading-6 text-white">
                        {{ substr($admin->name, 0, 1) }}
                    </button>
                @endif
            </x-slot>

            <x-slot:content class="mt-2 border-t-0 !p-0">
                <div class="flex items-center gap-1.5 border border-x-0 border-b-gray-300 px-5 py-2.5 dark:border-gray-800">
                    <img
                        src="{{ url('cache/logo/bagisto.png') }}"
                        width="24"
                        height="24"
                    />

                    <p class="text-gray-400">
                        @lang('admin::app.components.layouts.header.app-version', ['version' => 'v' . core()->version()])
                    </p>
                </div>

                <div class="grid gap-1 pb-2.5">
                    <a
                        class="cursor-pointer px-5 py-2 text-base text-gray-800 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-950"
                        href="{{ route('admin.account.edit') }}"
                    >
                        @lang('admin::app.components.layouts.header.my-account')
                    </a>

                    <x-admin::form
                        method="DELETE"
                        action="{{ route('admin.session.destroy') }}"
                        id="adminLogout"
                    >
                    </x-admin::form>

                    <a
                        class="cursor-pointer px-5 py-2 text-base text-gray-800 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-950"
                        href="{{ route('admin.session.destroy') }}"
                        onclick="event.preventDefault(); document.getElementById('adminLogout').submit();"
                    >
                        @lang('admin::app.components.layouts.header.logout')
                    </a>
                </div>
            </x-slot>
        </x-admin::dropdown>
    </div>
</header>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-notifications-template"
    >
        <x-admin::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'right' : 'left' }}">
            <x-slot:toggle>
                <span class="relative flex">
                    <span
                        class="icon-notification text-red cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
                        title="@lang('admin::app.components.layouts.header.notifications')"
                    ></span>

                    <span
                        class="absolute -top-2 flex h-5 min-w-5 cursor-pointer items-center justify-center rounded-full bg-blue-600 p-1.5 text-[10px] font-semibold leading-[9px] text-white ltr:left-5 rtl:right-5"
                        v-if="totalUnRead"
                    >
                        @{{ totalUnRead }}
                    </span>
                </span>
            </x-slot>

            <x-slot:content class="min-w-[250px] max-w-[250px] !p-0">
                <div class="border-b p-3 text-base font-semibold text-gray-600 dark:border-gray-800 dark:text-gray-300">
                    @lang('admin::app.notifications.title', ['read' => 0])
                </div>

                <div class="grid">
                    <a
                        class="flex items-start gap-1.5 border-b p-3 last:border-b-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                        v-for="notification in notifications"
                        :href="'{{ route('admin.notification.viewed_notification', ':orderId') }}'.replace(':orderId', notification.order_id)"
                    >
                        <span
                            v-if="notification.order.status in notificationStatusIcon"
                            class="h-fit"
                            :class="notificationStatusIcon[notification.order.status]"
                        ></span>

                        <div class="grid">
                            <p class="text-gray-800 dark:text-white">
                                #@{{ notification.order.id }}
                                @{{ orderTypeMessages[notification.order.status] }}
                            </p>

                            <p class="text-xs text-gray-600 dark:text-gray-300">
                                @{{ notification.order.datetime }}
                            </p>
                        </div>
                    </a>
                </div>

                <div class="flex h-[47px] justify-between gap-1.5 border-t px-6 py-4 dark:border-gray-800">
                    <a
                        href="{{ route('admin.notification.index') }}"
                        class="cursor-pointer text-xs font-semibold text-blue-600 transition-all hover:underline"
                    >
                        @lang('admin::app.notifications.view-all')
                    </a>

                    <a
                        class="cursor-pointer text-xs font-semibold text-blue-600 transition-all hover:underline"
                        v-if="notifications?.length"
                        @click="readAll()"
                    >
                        @lang('admin::app.notifications.read-all')
                    </a>
                </div>
            </x-slot>
        </x-admin::dropdown>
    </script>

    <script type="module">
        app.component('v-notifications', {
            template: '#v-notifications-template',

            data() {
                return {
                    notifications: [],
                    totalUnRead: 0,
                    orderTypeMessages: {
                        {{ \Webkul\Sales\Models\Order::STATUS_PENDING }}: "@lang('admin::app.notifications.order-status-messages.pending')",
                        {{ \Webkul\Sales\Models\Order::STATUS_CANCELED }}: "@lang('admin::app.notifications.order-status-messages.canceled')",
                        {{ \Webkul\Sales\Models\Order::STATUS_CLOSED }}: "@lang('admin::app.notifications.order-status-messages.closed')",
                        {{ \Webkul\Sales\Models\Order::STATUS_COMPLETED }}: "@lang('admin::app.notifications.order-status-messages.completed')",
                        {{ \Webkul\Sales\Models\Order::STATUS_PROCESSING }}: "@lang('admin::app.notifications.order-status-messages.processing')",
                        {{ \Webkul\Sales\Models\Order::STATUS_PENDING_PAYMENT }}: "@lang('admin::app.notifications.order-status-messages.pending-payment')",
                    },
                };
            },

            computed: {
                notificationStatusIcon() {
                    return {
                        pending: 'icon-information rounded-full bg-amber-100 text-2xl text-amber-600 dark:!text-amber-600',
                        closed: 'icon-repeat rounded-full bg-red-100 text-2xl text-red-600 dark:!text-red-600',
                        completed: 'icon-done rounded-full bg-blue-100 text-2xl text-blue-600 dark:!text-blue-600',
                        canceled: 'icon-cancel-1 rounded-full bg-red-100 text-2xl text-red-600 dark:!text-red-600',
                        processing: 'icon-sort-right rounded-full bg-green-100 text-2xl text-green-600 dark:!text-green-600',
                    };
                },
            },

            mounted() {
                this.getNotification();
            },

            methods: {
                getNotification() {
                    this.$axios.get('{{ route('admin.notification.get_notification') }}', {
                        params: {
                            limit: 5,
                            read: 0,
                        },
                    }).then((response) => {
                        this.notifications = response.data.search_results.data;
                        this.totalUnRead = response.data.total_unread;
                    }).catch(() => {});
                },

                readAll() {
                    this.$axios.post('{{ route('admin.notification.read_all') }}')
                        .then((response) => {
                            this.notifications = response.data.search_results.data;
                            this.totalUnRead = response.data.total_unread;
                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.success_message });
                        }).catch(() => {});
                },
            },
        });
    </script>

    <script
        type="text/x-template"
        id="v-dark-template"
    >
        <div class="flex">
            <span
                class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
                :class="[isDarkMode ? 'icon-light' : 'icon-dark']"
                @click="toggle"
            ></span>
        </div>
    </script>

    <script type="module">
        app.component('v-dark', {
            template: '#v-dark-template',

            data() {
                return {
                    isDarkMode: {{ request()->cookie('dark_mode') ?? 0 }},
                    logo: "{{ bagisto_asset('images/logo.svg') }}",
                    darkLogo: "{{ bagisto_asset('images/dark-logo.svg') }}",
                };
            },

            methods: {
                toggle() {
                    this.isDarkMode = parseInt(this.isDarkModeCookie()) ? 0 : 1;

                    const expiryDate = new Date();

                    expiryDate.setMonth(expiryDate.getMonth() + 1);

                    document.cookie = 'dark_mode=' + this.isDarkMode + '; path=/; expires=' + expiryDate.toGMTString();
                    document.documentElement.classList.toggle('dark', this.isDarkMode === 1);

                    this.$emitter.emit('change-theme', this.isDarkMode ? 'dark' : 'light');

                    document.querySelectorAll('[data-role=\"brand-logo\"]').forEach((image) => {
                        image.src = this.isDarkMode ? this.darkLogo : this.logo;
                    });
                },

                isDarkModeCookie() {
                    const cookies = document.cookie.split(';');

                    for (const cookie of cookies) {
                        const [name, value] = cookie.trim().split('=');

                        if (name === 'dark_mode') {
                            return value;
                        }
                    }

                    return 0;
                },
            },
        });
    </script>
@endPushOnce
