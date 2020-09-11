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

    "router.config" => [
        "buildDir" => "build",
        "filename" => "{name}.php"
    ],

    "template.config" => [
        "helpers" => [
            "mapFiles" => [".helper.php"],
            "buildDir" => "render/helpers",
            "excludeDir" => ["lib"],
            "basename" => "app/Client/helpers",
            "filename" => "helpers.bundle.php"
        ],
        "templates" => [
            "mapFiles" => [".template.html"],
            "buildDir" => "render/templates",
            "excludeDir" => ["helper", "helpers", "partial", "partials", "lib"],
            "basename" => "app/Client",
            "filename" => "{name}.php"
        ],
        "partials" => [
            "mapFiles" => [".hbs", ".partial.html", ".partial.hbs"],
            "buildDir" => "render/partials",
            "excludeDir" => ["helper", "helpers", "partial", "partials", "lib"],
            "basename" => "app/Client/",
            "filename" => "partials.bundle.php"
        ]
    ]
];
