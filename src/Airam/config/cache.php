<?php

namespace Airam;

/**
 * @return array cache definition folders
 */
return [
    "dir.config" => [
        "basename" => ".airam",
        "folders" => [
            "render",
            "build",
            "temp",
        ]
    ],

    "app.config" => [
        "definitionCache" => "Airam\Cache",
        "buildDir" => "build",
        "proxyDir" => "temp"
    ],

    "router.config" => [
        "cacheFile" => "build/router.php"
    ],

    "template.config" => [
        "helpers" => [
            "fileExtension" => [".helper.php"],
            "buildDir" => "render/helpers",
            "dir" =>  "app/Client/helpers",
            "excludeDir" => ["example","builkd"],
        ],
        "templates" => [
            "fileExtension" => [".template.html"],
            "buildDir" => "render/templates",
            "dir" => "app/Client",
            "excludeDir" => ["helper", "helpers", "partial", "partials"]
        ],
        "partials" => [
            "fileExtension" => [".hbs", ".partial.html", ".partial.hbs"],
            "buildDir" => "render/partials",
            "dir" => "app/Client/",
            "excludeDir" => ["helper", "helpers", "partial", "partials"]
        ]
    ]
];
