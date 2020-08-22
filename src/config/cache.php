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
        "name" => "Simplext PHP",
        "definitionCache" => "App\Cache",
        "buildDir" => "build",
        "proxyDir" => "temp"
    ],

    "router.config" => [
        "cacheFile" => "build/router.php"
    ],

    "template.config" => [
        "buildDir" => "render"
    ]
];
