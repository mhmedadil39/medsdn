@php
    $quickActions = collect([
        [
            'permission' => 'catalog.products.create',
            'route'      => route('admin.catalog.products.index'),
            'icon'       => 'quick-product',
            'label'      => trans('admin::app.catalog.products.index.title'),
        ],
        [
            'permission' => 'catalog.categories.create',
            'route'      => route('admin.catalog.categories.create'),
            'icon'       => 'quick-category',
            'label'      => trans('admin::app.catalog.categories.index.title'),
        ],
        [
            'permission' => 'catalog.attributes.create',
            'route'      => route('admin.catalog.attributes.create'),
            'icon'       => 'quick-attribute',
            'label'      => trans('admin::app.catalog.attributes.index.title'),
        ],
        [
            'permission' => 'catalog.families.create',
            'route'      => route('admin.catalog.families.create'),
            'icon'       => 'quick-family',
            'label'      => trans('admin::app.catalog.families.index.title'),
        ],
        [
            'permission' => 'cms.create',
            'route'      => route('admin.cms.create'),
            'icon'       => 'quick-cms',
            'label'      => trans('admin::app.components.layouts.sidebar.cms'),
        ],
        [
            'permission' => 'marketing.promotions.cart_rules.create',
            'route'      => route('admin.marketing.promotions.cart_rules.create'),
            'icon'       => 'quick-cart-rule',
            'label'      => trans('admin::app.marketing.promotions.index.cart-rule-title'),
        ],
        [
            'permission' => 'settings.inventory_sources.create',
            'route'      => route('admin.settings.inventory_sources.create'),
            'icon'       => 'quick-inventory-source',
            'label'      => trans('admin::app.settings.inventory-sources.index.title'),
        ],
        [
            'permission' => 'settings.roles.create',
            'route'      => route('admin.settings.roles.create'),
            'icon'       => 'quick-role',
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
                <div
                    class="relative px-2 py-4"
                    data-role="header-quick-create-grid"
                >
                    <div class="grid grid-cols-3 gap-2 text-center max-sm:grid-cols-2">
                        @foreach ($quickActions as $quickAction)
                            <a
                                href="{{ $quickAction['route'] }}"
                                class="rounded-lg bg-white p-2 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-950"
                            >
                                <div class="flex flex-col gap-1">
                                    <x-admin::icon.shell
                                        :name="$quickAction['icon']"
                                        alt="{{ $quickAction['label'] }}"
                                        class="mx-auto h-6 w-6"
                                    />

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
