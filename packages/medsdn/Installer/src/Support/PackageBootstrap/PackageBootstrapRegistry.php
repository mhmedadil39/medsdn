<?php

namespace Webkul\Installer\Support\PackageBootstrap;

class PackageBootstrapRegistry
{
    /**
     * @return array<string, PackageBootstrapStep>
     */
    public function all(): array
    {
        return [
            'Admin'         => PackageBootstrapStep::migrationOnly('Admin', 'Admin package is initialized through migrations and existing configuration.'),
            'Attribute'     => PackageBootstrapStep::seeded('Attribute', 'Installer seeds default attribute families, groups, and options.'),
            'BankTransfer'  => PackageBootstrapStep::migrationOnly('BankTransfer', 'Bank transfer package relies on migrations and configuration defaults.'),
            'BookingProduct'=> PackageBootstrapStep::migrationOnly('BookingProduct', 'Booking products rely on migrated schema without extra first-run data.'),
            'CMS'           => PackageBootstrapStep::seeded('CMS', 'Installer seeds default CMS pages for a fresh storefront.'),
            'CartRule'      => PackageBootstrapStep::migrationOnly('CartRule', 'Cart rules are available after migrations and base catalog/customer data.'),
            'CatalogRule'   => PackageBootstrapStep::command('CatalogRule', 'Catalog rules require an initial rule-price index bootstrap.', [
                ['name' => 'product:price-rule:index'],
            ]),
            'Category'      => PackageBootstrapStep::seeded('Category', 'Installer seeds the root category for the default channel.'),
            'Checkout'      => PackageBootstrapStep::migrationOnly('Checkout', 'Checkout package relies on migrated schema only.'),
            'Core'          => PackageBootstrapStep::seeded('Core', 'Installer seeds channels, locales, currencies, countries, and configuration.'),
            'Customer'      => PackageBootstrapStep::seeded('Customer', 'Installer seeds default customer groups.'),
            'DataGrid'      => PackageBootstrapStep::migrationOnly('DataGrid', 'DataGrid package has no first-run bootstrap requirements.'),
            'DataTransfer'  => PackageBootstrapStep::migrationOnly('DataTransfer', 'Data transfer package relies on migrated schema only.'),
            'DebugBar'      => PackageBootstrapStep::migrationOnly('DebugBar', 'DebugBar has no installer bootstrap requirements.'),
            'FPC'           => PackageBootstrapStep::migrationOnly('FPC', 'FPC package has no first-run bootstrap requirements.'),
            'GDPR'          => PackageBootstrapStep::migrationOnly('GDPR', 'GDPR package relies on migrated schema only.'),
            'GraphQLAPI'    => PackageBootstrapStep::command('GraphQLAPI', 'GraphQL API package requires its install command on a fresh MedSDN setup.', [
                ['name' => 'medsdn-graphql:install'],
            ]),
            'Installer'     => PackageBootstrapStep::migrationOnly('Installer', 'Installer package is the orchestration layer itself.'),
            'Inventory'     => PackageBootstrapStep::seeded('Inventory', 'Installer seeds the default inventory source.'),
            'MagicAI'       => PackageBootstrapStep::migrationOnly('MagicAI', 'MagicAI package has no installer bootstrap requirements.'),
            'Marketing'     => PackageBootstrapStep::migrationOnly('Marketing', 'Marketing package migrations include their own defaults where needed.'),
            'MedsdnApi'     => PackageBootstrapStep::command('MedsdnApi', 'Storefront API package requires API Platform setup and an initial storefront key.', [
                ['name' => 'medsdn-api-platform:install'],
                [
                    'name'       => 'medsdn-api:generate-key',
                    'parameters' => [
                        '--name'       => 'Default Storefront Key',
                        '--rate-limit' => 1000,
                    ],
                ],
            ]),
            'Notification'  => PackageBootstrapStep::migrationOnly('Notification', 'Notification package relies on migrated schema only.'),
            'Payment'       => PackageBootstrapStep::migrationOnly('Payment', 'Payment package is available after configuration merge and migrations.'),
            'Paypal'        => PackageBootstrapStep::migrationOnly('Paypal', 'Paypal package is available after configuration merge and migrations.'),
            'Product'       => PackageBootstrapStep::command('Product', 'Product package requires an initial full index build for fresh installs.', [
                [
                    'name'       => 'indexer:index',
                    'parameters' => [
                        '--mode' => ['full'],
                    ],
                ],
            ]),
            'Rule'          => PackageBootstrapStep::migrationOnly('Rule', 'Rule package has no extra installer bootstrap requirements.'),
            'Sales'         => PackageBootstrapStep::migrationOnly('Sales', 'Sales package relies on migrated schema only.'),
            'Shipping'      => PackageBootstrapStep::migrationOnly('Shipping', 'Shipping package has no extra installer bootstrap requirements.'),
            'Shop'          => PackageBootstrapStep::seeded('Shop', 'Installer seeds theme customizations and storefront defaults.'),
            'Sitemap'       => PackageBootstrapStep::migrationOnly('Sitemap', 'Sitemap package relies on migrated schema only.'),
            'SocialLogin'   => PackageBootstrapStep::seeded('SocialLogin', 'Installer prepares social login package defaults for fresh installs.'),
            'SocialShare'   => PackageBootstrapStep::migrationOnly('SocialShare', 'Social share package has no installer bootstrap requirements.'),
            'Tax'           => PackageBootstrapStep::migrationOnly('Tax', 'Tax package relies on migrated schema and admin-managed data.'),
            'Theme'         => PackageBootstrapStep::seeded('Theme', 'Installer seeds default theme customization records.'),
            'User'          => PackageBootstrapStep::seeded('User', 'Installer seeds the default role and admin account scaffolding.'),
        ];
    }

    /**
     * @return array<int, PackageBootstrapStep>
     */
    public function commandBootstrapped(): array
    {
        $definitions = $this->all();

        return array_values(array_filter([
            $definitions['GraphQLAPI'],
            $definitions['MedsdnApi'],
            $definitions['Product'],
            $definitions['CatalogRule'],
        ]));
    }
}
