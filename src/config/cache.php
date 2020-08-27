<?php

namespace Airam;

/**
 * @return array cache definition folders
 */
return [
    "dir.config" => [
        "basename" => ".cache",
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
        "cacheFile" => "build/router.php"
    ],

    "template.config" => [
        "helpers" => [
            "fileExtension" => ".helper.php",
            "buildDir" => "render/helpers",
            "dir" =>  "app/Client/helpers",
            "exclude" => [],
        ],
        "components" => [
            "fileExtension" => ".template.html",
            "buildDir" => "render/client",
            "dir" => "app/client",
            "exclude" => ["helper", "helpers", "partial", "partials"]
        ],
        "partials" => [
            "fileExtension" => ".hbs",
            "buildDir" => "render/partials",
            "dir" => "app/Client/",
            "exclude" => []
        ]
    ]
];
