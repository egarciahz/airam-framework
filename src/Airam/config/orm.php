<?php

namespace Airam;

use Airam\Compiler\DirMap;
use Doctrine\ORM\{EntityManager, Configuration};

use Psr\Container\ContainerInterface;
use InvalidArgumentException;
use RuntimeException;

use function Airam\Commons\path_join;
use function DI\factory;

/**
 * @return array definitions of orm
 */
return [
    "cache" => factory(function (ContainerInterface $c) {
        if (Application::isDevMode()) {
            return new \Doctrine\Common\Cache\ArrayCache;
        }
    
        return new \Doctrine\Common\Cache\ApcuCache;
    }),
    "directory" => factory(function (ContainerInterface $c) {
        /** @var array<string,DirMap> $maps*/
        $maps = $c->get("CompilerOptions");
        $orm = $maps['orm'];

        return $orm->getDirname();
    }),
    "options" => factory(function () {

        $allowedConfigSchema = [
            "pdo_sqlite" => [
                "driver" => 1,
                "user" => 1,
                "password" => 1,
                "path" => 1,
                "memory" => 0
            ],
            "pdo_mysql" =>  [
                "driver" => 1,
                "user" => 1,
                "password" => 1,
                "host" => 0,
                "port" => 0,
                "dbname" => 1,
                "unix_socket" => 0,
                "charset" => 0
            ],
            "mysqli" => [
                "driver" => 1,
                "user" => 1,
                "password" => 1,
                "host" => 1,
                "port" => 0,
                "dbname" => 1,
                "unix_socket" => 0,
                "charset" => 0,
                "ssl_key" => 0,
                "ssl_ca" => 0,
                "ssl_cert" => 0,
                "ssl_capath" => 0,
                "ssl_cipher" => 0,
                "driverOptions" => 0
            ],
            "pdo_pgsql" => [
                "driver" => 1,
                "user" => 1,
                "password" => 1,
                "host" => 1,
                "port" => 0,
                "dbname" => 1,
                "charset" => 0,
                "default_dbname" => 0,
                "sslrootcert" => 0,
                "sslcert" => 0,
                "sslkey" => 0,
                "sslcrl" => 0,
                "application_name" => 1
            ],
            "pdo_oci" => [
                "driver" => 1,
                "user" => 1,
                "password" => 1,
                "host" => 0,
                "port" => 0,
                "dbname" => 1,
                "servicename" => 0,
                "service" => 0,
                "pooled" => 0,
                "charset" => 0,
                "instancename" => 0,
                "connectstring" => 0,
                "persistent" => 0
            ],
            "pdo_sqlsrv" => [
                "driver" => 1,
                "user" => 1,
                "password" => 1,
                "host" => 1,
                "port" => 0,
                "dbname" => 1
            ]
        ];

        $keys = array_filter(array_keys($_ENV), function ($key) {
            return preg_match("/^(DB\_)/", $key);
        });

        $data = array_reduce($keys, function ($carry, $item) {
            $key = strtolower(str_replace("DB_", "", $item));
            return array_merge($carry, ["$key" => $_ENV[$item]]);
        }, []);

        if (!isset($data['driver'])) {
            throw new RuntimeException("Invalid ORM config definition");
        }

        if (!isset($allowedConfigSchema[$data['driver']])) {
            throw new RuntimeException("Invalid driver definition");
        }

        $safe_scheme = function (array $scheme, array $model) {
            $model_filter = array();
            foreach ($scheme as $attr => $required) {
                if ((bool) $required) {
                    if (!in_array($attr, array_keys($model))) {
                        throw new InvalidArgumentException("Required parameter DB_" . strtoupper($attr) . " was not found.");
                    }
                }

                $model_filter[$attr] = $model[$model];
            }

            return $model_filter;
        };

        $config = $safe_scheme($allowedConfigSchema[$data['driver']], $data);

        return $config;
    }),
    Configuration::class => factory(function (ContainerInterface $c) {
        return null;
    }),
    EntityManager::class => factory(function (ContainerInterface $c) {
        return null;
    })
];
