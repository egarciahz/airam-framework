<?php

namespace Airam;

/**
 * @return array definitions of compiler
 */
return [
    "compiler" => [
        "config" => [
            "root" => ".cache",
            "watch" => "app/Client",
            "subdirs" => [
                "render",
                "build",
                "temp",
            ]
        ],
        // configurations for Template
        "helpers" => [
            "target" => "{root}/render/helpers/{filename}.bundle.php",
            "watch" => [
                "files" => [".helper.php"],
                "dirname" => "{watch}/helpers",
                "exclude" => ["lib"]
            ],
        ],
        "templates" => [
            "target" => "{root}/render/templates/{filename}.php",
            "watch" => [
                "files" => [".template.html"],
                "dirname" => "{watch}/",
                "exclude" => ["helper", "helpers", "partial", "partials", "lib"]
            ],
        ],
        "partials" => [
            "target" => "{root}/render/partials/{filename}.bundle.php",
            "watch" => [
                "files" => [".hbs", ".partial.html", ".partial.hbs"],
                "dirname" => "{watch}/",
                "exclude" => ["helper", "helpers", "partial", "partials", "lib"]
            ],
        ],
        // configurations for Router
        "router" => [
            "target" => "{root}/build/{filename}.bundle.php",
            "watch" => null
        ]
    ]
];
