<?php

namespace Airam\ORM;

use Airam\Compiler\DirMap;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\Common\Annotations\{
    AnnotationReader,
    CachedReader
};
use Doctrine\Common\Cache\{
    ArrayCache,
    ApcuCache,
    Cache,
    CacheProvider
};

use function Airam\Commons\checkEnvScheme;
use RuntimeException;

class Adapter
{
    /** driver options */
    private const OPTIONS = [
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
            "port" => 1,
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

    /** @var bool $debug */
    public $debug = false;

    /** @var DirMap $dirmap */
    private $dirmap;

    /** @var Cache $cache */
    private $cache;

    /** @var int $autoGenerateProxies */
    private $autoGenerateProxies = AbstractProxyFactory::AUTOGENERATE_NEVER;

    /** @var CachedReader $reader */
    private $reader;

    /** @var AnnotationDriver $annotationDriver */
    private $annotationDriver;

    public function __construct(ContainerInterface $app)
    {
        /** @var array<string,DirMap> $maps*/
        $maps = $app->get("CompilerOptions");

        /** @var bool $prodMode */
        $prodMode = (bool) $app->get("ProductionMode");

        /** @var CacheProvider $productionCache */
        $this->cache = $prodMode ?
            (($app->has(CacheProduction::class) && $app->get(CacheProduction::class) instanceof Cache) ?
                $app->get(CacheProduction::class) :
                new ApcuCache) :
            new ArrayCache;

        $this->autogenerateProxies = $prodMode ? AbstractProxyFactory::AUTOGENERATE_NEVER : AbstractProxyFactory::AUTOGENERATE_ALWAYS;
        $this->dirmap = $maps['doctrine'];

        $this->annotationDriver = $this->makeAnnotationMetadata();
    }

    private function makeAnnotationMetadata(): AnnotationDriver
    {
        $this->reader = new CachedReader(new AnnotationReader, $this->cache, $this->debug);
        return  new AnnotationDriver($this->reader, $this->getAnnotationDir());
    }

    private function parseEnvironment(): array
    {
        // filter db variable definition prefix
        $keys = array_filter(array_keys($_ENV), function ($key) {
            return preg_match("/^(DB\_)/", $key);
        });

        // reduce and filter variables
        $data = array_reduce($keys, function ($carry, $item) {
            $key = strtolower(str_replace("DB_", "", $item));
            return array_merge($carry, ["$key" => $_ENV[$item]]);
        }, []);

        return $data;
    }

    public function getConfig(): array
    {
        $data = $this->parseEnvironment();
        $data['driver'] = isset($data['driver']) ? $data['driver'] : "pdo_mysql";

        if (!isset(self::OPTIONS[$data['driver']])) {
            throw new RuntimeException("Unrecognized Driver name for Doctrine configuration");
        }

        $config = checkEnvScheme(self::OPTIONS[$data['driver']], $data, function ($missings) {
            return array_map(function ($param) {
                return "DB_" . strtoupper($param);
            }, $missings);
        });

        !$this->debug ?: error_log(sprintf("Doctrine configuration: %s", var_export($config, true)));

        return $config;
    }

    public function getCache(): Cache
    {
        return $this->cache;
    }

    public function getProxyDir(): string
    {
        !$this->debug ?: error_log(sprintf("Doctrine proxy dir: %s", $this->dirmap->getPath()));
        return $this->dirmap->getPath();
    }

    private function getAnnotationDir(): string
    {
        !$this->debug ?: error_log(sprintf("Doctrine annotation driver: %s", $this->dirmap->getDirname()));
        return $this->dirmap->getDirname();
    }

    public function getAnnotationReader(): CachedReader
    {
        return $this->reader;
    }

    public function getAnnotationDriver(): AnnotationDriver
    {
        return $this->annotationDriver;
    }

    public function getAutoGeneratedProxies(): bool
    {
        !$this->debug ?: error_log(sprintf("Auto Generate Proxies? %s", $this->autoGenerateProxies ? "yes" : "no"));
        return $this->autoGenerateProxies;
    }
}
