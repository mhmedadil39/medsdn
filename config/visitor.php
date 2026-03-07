<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | This value determines which of the following driver to use.
    | You can switch to a different driver at runtime.
    |
    */
    'default' => 'jenssegers',

    // except save request or route names
    'except' => ['login', 'register'],

    // name of the table which visit records should save in
    'table_name' => 'visits',

    /*
    |--------------------------------------------------------------------------
    | List of Drivers
    |--------------------------------------------------------------------------
    |
    | This is the array of Classes that maps to Drivers above.
    | You can create your own driver if you like and add the
    | config in the drivers array and the class to use for
    | here with the same name. You will have to implement
    | Shetabit\Visitor\Contracts\UserAgentParser in your driver.
    |
    */
    'drivers' => [
        'jenssegers' => \Shetabit\Visitor\Drivers\JenssegersAgent::class,
        'UAParser' => \Shetabit\Visitor\Drivers\UAParser::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | GeoIP Enrichment
    |--------------------------------------------------------------------------
    |
    | Visits can optionally be enriched with geolocation data stored in
    | the `geo_raw` column. The null resolver keeps visitor logging safe
    | even when GeoIP enrichment is disabled.
    |
    */
    'geoip' => false,

    /*
    |--------------------------------------------------------------------------
    | GeoIP Resolver
    |--------------------------------------------------------------------------
    |
    | The installed visitor package still validates the resolver during
    | bootstrap even when geoip enrichment is disabled, so default to a
    | resolver class that is present in this installation.
    |
    */
    'resolver' => 'stevebauman',

    /*
    |--------------------------------------------------------------------------
    | GeoIP Resolvers
    |--------------------------------------------------------------------------
    |
    | Available geolocation resolvers for the visitor package.
    |
    */
    'resolvers' => [
        'stevebauman' => \Shetabit\Visitor\Resolvers\GeoIp\SteveBaumanResolver::class,
        'null' => \Shetabit\Visitor\Resolvers\GeoIp\NullResolver::class,
    ],
];
