<?php

namespace Airam\Template\Render;

use Airam\Compiler\Compiler;
use Airam\Compiler\DirMap;
use Airam\Compiler\FileSystem;
use Airam\Template\LayoutInterface;
use Airam\Template\TemplateInterface;

use function Airam\Template\Lib\{
    makeTemplateFileName,
    cleanFileName,
    is_layout
};
use function Airam\Commons\{
    matchFilesByExtension,
    loadResource
};

use LightnCandy\{LightnCandy, SafeString};
use Psr\Container\ContainerInterface;
use ErrorException;
use Closure;

class Engine
{
    private $isDevMode = true;

    private static $context = [];
    private $config;

    private static $partials = [];
    public static $helpers = [];

    public function __construct(array $config, ContainerInterface $app)
    {
        $this->app = $app;
        $this->config = $config;
    }

    /**
     * @param string[] $paths array of available file paths
     * @param DirMap $map
     */
    protected function compileHelpers(array $paths, DirMap $map)
    {
        foreach ($paths as $path) {
            $helper = loadResource($path);
            $name = cleanFileName($path);

            !$this->isDevMode ?: error_log(sprintf("compiling helper: %s", $path));
            if ($helper === null) {
                error_log(sprintf("Can not find helper file [%s]!\n.", $path));
                continue;
            }

            if ($helper === false) {
                error_log(sprintf("Can not read helper file [%s]!\n.", $path));
                continue;
            }

            if (gettype($helper) === "array") {
                static::$helpers = array_merge(static::$helpers, $helper);
            } else if ($helper instanceof Closure) {
                static::$helpers[$name] = $helper;
            }
        }

        !$this->isDevMode ?: error_log(sprintf("bundle helpers: %s", $map->getPath()));
        return Compiler::bundle(static::$helpers, $map->getPath(), "Airam\Cache");
    }

    /**
     * @param string[] $paths array of available file paths
     * @param DirMap $map
     */
    protected function compilePartials(array $paths, DirMap $map)
    {
        $partials = [];
        foreach ($paths as $path) {
            $partial = file_get_contents($path);
            $name = cleanFileName($path);

            !$this->isDevMode ?: error_log(sprintf("compiling partial: %s", $path));
            if ($partial === null) {
                error_log(sprintf("Can not find helper file [%s]!\n.", $path));
                continue;
            }

            if ($partial === false) {
                error_log(sprintf("Can not read helper file [%s]!\n.", $path));
                continue;
            }

            $template = new SafeString($partial);
            $template = $this->isDevMode ? "<!-- $name -->$template<!-- /$name -->" : $template;


            $code = LightnCandy::compilePartial($template);
            $partials[] = " \"{$name}\" => {$code},";
        }

        !$this->isDevMode ?: error_log(sprintf("bundle partials: %s", $map->getPath()));
        $partials = join(PHP_EOL, ["array(", join(PHP_EOL, $partials), ")"]);

        return Compiler::bundle($partials, $map->getPath(), "Airam\Cache", [], true);
    }

    /**
     * @param string $path template file path
     * @param DirMap $map
     */
    protected function compileTemplate(string $path, DirMap $map)
    {
        if (!file_exists($path)) {
            $template = "<b>Template Not Found: </b> <small>{$path}</small>";
        }

        $template = file_get_contents($path);
        $code = LightnCandy::compile($template, self::$context);
        $code = Compiler::compile($code, false);

        $name = makeTemplateFileName($path);
        $cpath = $map->getPath($name);

        $this->isDevMode ?: error_log(sprintf("compile template: %s", $cpath));
        if (!FileSystem::write($cpath, $code)) {
            throw new ErrorException("Could not create file during compilation of template: [$cpath]");
        }

        return $cpath;
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

            $dirMap = DirMap::fromSchema($this->config, "templates");
            $name = makeTemplateFileName($data->file);

            if (!$dirMap->isFileExist($name)) {
                $file = $this->compileTemplate($data->file, $dirMap);
            }

            $data->properties["Template"]["file"] = $file;
            $renderer = loadResource($file);
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
        $partials = $maps["partials"];
        $helpers = $maps["helpers"];


        if ($this->isDevMode ?: !$helpers->isFileExist()) {
            error_log("compileHelpers");
            $this->compileHelpers(
                matchFilesByExtension(
                    $helpers->getDirname(),
                    $helpers->files,
                    $helpers->exclude,
                ),
                $helpers
            );
        }

        if ($this->isDevMode ?: !$partials->isFileExist()) {
            $this->compilePartials(
                matchFilesByExtension(
                    $partials->getDirname(),
                    $partials->files,
                    $partials->exclude,
                ),
                $partials
            );
        }

        /** load compiled helpers and partial files */
        $helpers = loadResource($helpers->getPath());
        self::$partials = loadResource($partials->getPath());
        /** prepare context */
        return $this->prepare([
            "helpers" =>  $helpers
        ]);

        if (!$this->isDevMode) {
            $templates = $maps["templates"];
            $paths = matchFilesByExtension(
                $templates->getDirname(),
                $templates->files,
                $templates->exclude
            );

            foreach ($paths as $path) {
                $this->compileTemplate(
                    $path,
                    $templates
                );
            }
        }
    }
}
