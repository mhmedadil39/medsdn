<?php

namespace Webkul\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Shetabit\Visitor\Visitor as BaseVisitor;
use Webkul\Core\Jobs\UpdateCreateVisitIndex;

class Visitor extends BaseVisitor
{
    /**
     * Create a new visitor instance while normalizing legacy visitor config.
     */
    public function __construct(Request $request, $config)
    {
        parent::__construct($request, $this->normalizeConfig($config));
    }

    /**
     * Create a visit log.
     *
     * @return void
     */
    public function visit(?Model $model = null)
    {
        if (! core()->getConfigData('general.general.visitor_options.enabled')) {
            return;
        }

        foreach ($this->except as $path) {
            if ($this->request->is($path)) {
                return;
            }
        }

        UpdateCreateVisitIndex::dispatch($model, $this->prepareLog());
    }

    /**
     * Retrieve request's url.
     */
    public function url(): string
    {
        return $this->request->url();
    }

    /**
     * Prepare log's data.
     *
     *
     * @throws \Exception
     */
    protected function prepareLog(): array
    {
        return array_merge(parent::prepareLog(), [
            'channel_id' => core()->getCurrentChannel()->id,
        ]);
    }

    /**
     * Returns logs.
     *
     * @return array
     */
    public function getLog()
    {
        return $this->prepareLog();
    }

    /**
     * Normalize legacy project visitor config to the current package contract.
     */
    protected function normalizeConfig(array $config): array
    {
        $config['geoip'] ??= false;

        $config['resolvers'] = array_merge([
            'stevebauman' => \Shetabit\Visitor\Resolvers\GeoIp\SteveBaumanResolver::class,
            'null' => \Shetabit\Visitor\Resolvers\GeoIp\NullResolver::class,
        ], $config['resolvers'] ?? []);

        $config['resolvers'] = array_filter(
            $config['resolvers'],
            fn (string $resolverClass) => class_exists($resolverClass)
        );

        if (empty($config['resolver']) || empty($config['resolvers'][$config['resolver']])) {
            $config['resolver'] = array_key_first($config['resolvers']) ?: 'stevebauman';
        }

        return $config;
    }
}
