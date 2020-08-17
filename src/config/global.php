<?php

namespace Core;

/**
 * @return array cache definition folders
 */
return [
    "dir.config" => [
        "basename" => ".simplext",
        "folders" => [
            "render",
            "build",
            "temp",
        ]
    ],

    "app.config" => [
        "definitionCache" => "App\Cache",
        "buildDir" => "build",
        "proxyDir" => "temp"
    ],

    "router.config" => [
        "cacheFile" => "build/router.php",
        'routeParser' => 'FastRoute\\RouteParser\\Std',
        'dataGenerator' => 'FastRoute\\DataGenerator\\GroupCountBased',
        'dispatcher' => 'FastRoute\\Dispatcher\\GroupCountBased',
    ],

    "template.config" => [
        "buildDir" => "render",
    ]
];
