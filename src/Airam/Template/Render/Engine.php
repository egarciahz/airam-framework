<?php

namespace Airam\Template\Render;

use Airam\Compiler\Compiler;
use Airam\Template\LayoutInterface;
use Airam\Template\TemplateInterface;
use LightnCandy\{LightnCandy, SafeString};

use function Airam\Template\Lib\{
    is_layout,
    makeTemplateFileName,
    cleanFileName
};
use function Airam\Commons\{
    path_join,
    loadResource,
    matchFilesByExtension
};

use Psr\Container\ContainerInterface;
use ErrorException;

class Engine
{
    private $isDevMode = true;

    private static $context = [];
    private $config;

    private static $partials = [];
    private $root;
    private $app;

    public function __construct(array $config, ContainerInterface $app)
    {
        $this->app = $app;
        $this->config = $config;
        $this->root = getenv("ROOT_DIR");
    }

    /**
     * load helpers and partial bundlers
     * @param bool $isDevMode
     */
    private function loadResources()
    {
        $helpers = path_join(DIRECTORY_SEPARATOR, $this->root, ".cache", $this->config["helpers"]["buildDir"], "helpers.bundle.php");
        $partials = path_join(DIRECTORY_SEPARATOR, $this->root, ".cache", $this->config["partials"]["buildDir"], "partials.bundle.php");

        $helpers = loadResource($helpers);
        self::$partials = loadResource($partials);

        /** prepare context */
        return $this->prepare([
            "helpers" =>  $helpers
        ]);
    }

    /**
     * @param string[] $paths array of available file paths
     * @param string $buildDir path folder for make file
     */
    protected function compileHelpers(array $paths, string $buildDir)
    {
        $helpers = [];
        foreach ($paths as $path) {
            if (!file_exists($path)) {
                error_log("Can not find helper file [{$path}]!\n.");
                continue;
            }

            if (!is_readable($path)) {
                error_log("Can not read helper file [{$path}]!\n.");
                continue;
            }

            $helper = loadResource($path);
            $name = cleanFileName($path);

            if (gettype($helper) === "array") {
                array_merge($helpers, $helper);
            } else {
                $helpers[$name] = $helper;
            }
        }

        return Compiler::bundle($helpers, path_join(DIRECTORY_SEPARATOR, $buildDir, "/helpers.bundle.php"), "Airam\Cache");
    }

    /**
     * @param string[] $paths array of available file paths
     * @param string $buildDir path folder for make file
     */
    protected function compilePartials(array $paths, string $buildDir)
    {
        $partials = [];
        foreach ($paths as $path) {
            if (!file_exists($path)) {
                error_log("Can not find partial file [{$path}]!\n.");
                continue;
            }

            if (!is_readable($path)) {
                error_log("Could not read partial file [{$path}]!\n.");
                continue;
            }

            $name = cleanFileName($path);
            $template = new SafeString(file_get_contents($path));
            $template = "<!-- $name -->$template<!-- /$name -->";

            $code = LightnCandy::compilePartial($template);
            $partials[$name] = $code;
        }

        return Compiler::bundle($partials, path_join(DIRECTORY_SEPARATOR, $buildDir, "/partials.bundle.php"), "Airam\Cache");
    }

    /**
     * @param string $path template file path
     * @param string $buildDir path folder for make file
     */
    protected function compileTemplate(string $path, string $buildDir)
    {
        if (!file_exists($path)) {
            return null;
        }

        $template = file_get_contents($path);
        $code = LightnCandy::compile($template, self::$context);
        $code = join(PHP_EOL, [
            "<?php ",
            $code
        ]);

        if (file_exists($buildDir)) {
            $file = makeTemplateFileName($path);
            $file = path_join(DIRECTORY_SEPARATOR, $buildDir, $file);

            $size = file_put_contents($file, $code);
            if ($size === 0) {
                throw new ErrorException("Could not create file during compilation of template: [$path]");
            }
        }

        return $file;
    }

    /**
     * @param LayoutInterface $layout
     * @param TemplateInterface $templates
     * 
     * @return string html code
     */
    public function layout($layout, ...$templates): string
    {
        if (!is_layout($layout)) {
            return null;
        }

        foreach ($templates as $template) {
            $layout->setYield($template->yield, $this->render($template));
        }

        return  $this->render($layout);
    }

    /**
     * @param TemplateInterface $object
     * @param bool $runtime enable runtime rendering
     * 
     * @return string html code
     */
    public function render($object)
    {
        $data = $object->__toRender($this->isDevMode);

        if ($this->isDevMode) {

            $template = file_get_contents($data->file);
            $compiled = LightnCandy::compile($template, self::$context);
            $renderer = LightnCandy::prepare($compiled);

            $data->properties["Template"]["file"] = $data->file;
        } else {

            $buildDir = path_join(DIRECTORY_SEPARATOR, $this->root, ".cache", $this->config["templates"]["buildDir"]);
            $file = makeTemplateFileName($data->file);
            $file = path_join(DIRECTORY_SEPARATOR, $buildDir, $file);

            if (!file_exists($file)) {
                $file = $this->compileTemplate($data->file, $buildDir);
            }

            $data->properties["Template"]["file"] = $file;
            $renderer = require $file;
        }

        /**
         * rendering strategy depending of LightnCandy::FLAG_ERROR_SKIPPARTIAL | LightnCandy::FLAG_RUNTIMEPARTIAL flags
         */
        return $renderer(array_merge_recursive($data->properties, $data->methods), [
            "partials" => self::$partials
        ]);
    }

    public function prepare(array $overrides = [])
    {
        $context = [
            "flags" => (!$this->isDevMode ? LightnCandy::FLAG_ERROR_LOG : LightnCandy::FLAG_ERROR_EXCEPTION) | // options for error catching and debug
                LightnCandy::FLAG_HANDLEBARSJS_FULL |
                LightnCandy::FLAG_ERROR_SKIPPARTIAL |
                LightnCandy::FLAG_RUNTIMEPARTIAL |
                LightnCandy::FLAG_BESTPERFORMANCE |
                LightnCandy::FLAG_NAMEDARG |
                LightnCandy::FLAG_PARENT
        ];

        self::$context = array_merge($context, $overrides);
        return self::$context;
    }

    public function enableCompilation()
    {
        $this->isDevMode = false;
        return $this;
    }

    public function build()
    {
        $maps = Compiler::buildMaps($this->config);
        echo var_dump($maps); exit;
        $helpersdir = path_join(DIRECTORY_SEPARATOR, $this->root, $this->config["helpers"]["basename"]);
        if ($this->isDevMode && !file_exists($helpersdir . "/helpers.bundle.php")) {
            $this->compileHelpers(
                matchFilesByExtension(
                    $helpersdir,
                    $this->config["helpers"]["mapFiles"],
                    $this->config["helpers"]["excludeDir"],
                ),
                path_join(DIRECTORY_SEPARATOR, $this->root, ".cache", $this->config["helpers"]["buildDir"])
            );
        }

        $partialsdir = path_join(DIRECTORY_SEPARATOR, $this->root, $this->config["partials"]["basename"]);
        if ($this->isDevMode && !file_exists($partialsdir . "/partials.bundle.php")) {
            $this->compilePartials(
                matchFilesByExtension(
                    $partialsdir,
                    $this->config["partials"]["mapFiles"],
                    $this->config["partials"]["excludeDir"],
                ),
                path_join(DIRECTORY_SEPARATOR, $this->root, ".cache", $this->config["partials"]["buildDir"])
            );
        }

        $this->loadResources();

        if (!$this->isDevMode) {
            $templates = matchFilesByExtension(
                path_join(DIRECTORY_SEPARATOR, $this->root, $this->config["templates"]["basename"]),
                $this->config["templates"]["mapFiles"],
                $this->config["templates"]["excludeDir"]
            );

            foreach ($templates as $path) {
                $this->compileTemplate(
                    $path,
                    path_join(DIRECTORY_SEPARATOR, $this->root, ".cache", $this->config["templates"]["buildDir"])
                );
            }
        }
    }
}
