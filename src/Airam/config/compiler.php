<?php

namespace Airam;

use Airam\Compiler\Compiler;
use Psr\Container\ContainerInterface;

use function DI\factory;

/**
 * @return array definitions of compiler
 */
return [
    "compiler" => [
        "config" => [
            "root" => ".cache",
            "watch" => "app/",
            "subdirs" => [
                "render",
                "build",
                "temp",
            ]
        ],
        // configurations for Template
        "helpers" => [
            "target" => "{root}/render/helpers/helpers.bundle.php",
            "watch" => [
                "files" => [".helper.php"],
                "dirname" => "{watch}/Client/helpers",
                "exclude" => ["lib"]
            ],
        ],
        "templates" => [
            "target" => "{root}/render/templates/{filename}.php",
            "watch" => [
                "files" => [".template.html"],
                "dirname" => "{watch}/Client",
                "exclude" => ["helper", "helpers", "partial", "partials", "lib"]
            ],
        ],
        "partials" => [
            "target" => "{root}/render/partials/partials.bundle.php",
            "watch" => [
                "files" => [".hbs", ".partial.html", ".partial.hbs"],
                "dirname" => "{watch}/Client",
                "exclude" => ["helper", "helpers", "partial", "partials", "lib"]
            ],
        ],
        // configurations for Router
        "router" => [
            "target" => "{root}/build/{filename}.bundle.php",
            "watch" => null
        ],
        // doctrine
        "orm" => [
            "target" => "{root}/build/proxy",
            "watch" => [
                "files" => [".php"],
                "dirname" => "{watch}/Entities",
                "exclude" => ["lib"]
            ]
        ]
    ],
    "CompilerOptions" => factory(function (ContainerInterface $c) {
        $compiler = $c->get("compiler");
        return Compiler::buildMaps($compiler);
    }),
];
