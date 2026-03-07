@php
    $quickActions = collect([
        [
            'permission' => 'catalog.products.create',
            'route'      => route('admin.catalog.products.index'),
            'image'      => bagisto_asset('images/settings/product.svg', 'admin'),
            'label'      => trans('admin::app.catalog.products.index.title'),
        ],
        [
            'permission' => 'catalog.categories.create',
            'route'      => route('admin.catalog.categories.create'),
            'image'      => bagisto_asset('images/settings/files.svg', 'admin'),
            'label'      => trans('admin::app.catalog.categories.index.title'),
        ],
        [
            'permission' => 'catalog.attributes.create',
            'route'      => route('admin.catalog.attributes.create'),
            'image'      => bagisto_asset('images/settings/description.svg', 'admin'),
            'label'      => trans('admin::app.catalog.attributes.index.title'),
        ],
        [
            'permission' => 'catalog.families.create',
            'route'      => route('admin.catalog.families.create'),
            'image'      => bagisto_asset('images/settings/products.svg', 'admin'),
            'label'      => trans('admin::app.catalog.families.index.title'),
        ],
        [
            'permission' => 'cms.create',
            'route'      => route('admin.cms.create'),
            'image'      => bagisto_asset('images/settings/notes.svg', 'admin'),
            'label'      => trans('admin::app.components.layouts.sidebar.cms'),
        ],
        [
            'permission' => 'marketing.promotions.cart_rules.create',
            'route'      => route('admin.marketing.promotions.cart_rules.create'),
            'image'      => bagisto_asset('images/settings/quotes.svg', 'admin'),
            'label'      => trans('admin::app.marketing.promotions.index.cart-rule-title'),
        ],
        [
            'permission' => 'settings.inventory_sources.create',
            'route'      => route('admin.settings.inventory_sources.create'),
            'image'      => bagisto_asset('images/settings/inventory.svg', 'admin'),
            'label'      => trans('admin::app.settings.inventory-sources.index.title'),
        ],
        [
            'permission' => 'settings.roles.create',
            'route'      => route('admin.settings.roles.create'),
            'image'      => bagisto_asset('images/settings/users.svg', 'admin'),
            'label'      => trans('admin::app.settings.roles.index.title'),
        ],
    ])->filter(fn (array $action) => bouncer()->hasPermission($action['permission']));
@endphp

<div data-role="header-quick-create">
    @if ($quickActions->isNotEmpty())
        <x-admin::dropdown position="bottom-right">
            <x-slot:toggle>
                <button class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-brandColor text-white">
                    <i class="icon-add text-2xl"></i>
                </button>
            </x-slot>

            <x-slot:content class="mt-2 !p-0">
                <div class="relative px-2 py-4">
                    <div class="grid grid-cols-3 gap-2 text-center max-sm:grid-cols-2">
                        @foreach ($quickActions as $quickAction)
                            <a
                                href="{{ $quickAction['route'] }}"
                                class="rounded-lg bg-white p-2 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-950"
                            >
                                <div class="flex flex-col gap-1">
                                    <img
                                        src="{{ $quickAction['image'] }}"
                                        alt="{{ $quickAction['label'] }}"
                                        class="mx-auto h-6 w-6 dark:mix-blend-exclusion dark:invert"
                                    >

                                    <span class="font-medium dark:text-gray-300">{{ $quickAction['label'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </x-slot>
        </x-admin::dropdown>
    @endif
</div>
