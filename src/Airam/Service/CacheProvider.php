<?php

namespace Airam;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use function Airam\Commons\path_join;

/**
 * @Injectable()
 */
class CacheProvider extends Filesystem
{
    /** @var string $rootPath */
    private $rootPath;
    private $app;

    public function __construct(Application $app)
    {
        $this->rootPath = getenv('ROOT_DIR');
        $this->app = $app;
    }

    public function getFile(string $folder, string $name)
    {
    }

    public function makeFile(string $folder, string $name, $content)
    {
    }

    public function makeFolder(string $folder)
    {
        
    }

    public function check()
    {
    }

    public function build()
    {
    }
}
