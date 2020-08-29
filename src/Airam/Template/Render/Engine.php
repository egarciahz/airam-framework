<?php

namespace Airam\Template\Render;

use Airam\Template\LayoutInterface;
use Airam\Template\TemplateInterface;
use LightnCandy\LightnCandy;

use function Airam\Template\Lib\{
    is_layout,
    makeTemplateFileName,
    matchFilesByExtension,
    closureCodeCompiler,
    loadResource
};
use function Airam\Utils\path_join;

use ErrorException;
use Closure;

class Engine
{
    private $context = [];
    private $config;

    private $partials = [];
    private $helpers = [];

    private $root;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->root = getenv("ROOT_DIR");
    }

    public function loadResources(bool $isDevMode = true)
    {
        $helpers = path_join(DIRECTORY_SEPARATOR, $this->root, ".cache", $this->config["helpers"]["buildDir"], "helpers_bundle.php");
        $partials = path_join(DIRECTORY_SEPARATOR, $this->root, ".cache", $this->config["partials"]["buildDir"], "partials_bundle.php");

        $helpers = loadResource($helpers);
        $partials = loadResource($partials);

        /** prepare context */
        return $this->prepare($isDevMode, [
            "helpers" =>  $helpers,
            "partials" => $partials
        ]);
    }

    /**
     * @param string[] $paths
     */
    protected function compileHelpers(array $paths, string $buildDir)
    {
        foreach ($paths as $path) {
            if (!file_exists($path)) {
                continue;
            }

            $helper = require $path;

            if ($helper instanceof Closure) {
                $name = preg_replace("/\..+$/", "", basename($path));
                $code = closureCodeCompiler($helper, $name);

                array_push($this->helpers, $code);
            }

            if (gettype($helper) === "array") {
                foreach ($helper as $name => $predicate) {
                    if ($predicate instanceof Closure) {
                        $code = closureCodeCompiler($predicate, $name);
                        array_push($this->helpers, $code);
                        continue;
                    }

                    array_push($this->helpers, "\"{$name}\" => " . var_export($predicate, true));
                }
            }
        }

        $code = join(PHP_EOL, [
            "<?php",
            "use Airam\Application;",
            "use function Airam\Utils\{path_join,randomId,class_use};",
            "use function Airam\Template\Lib\{is_layout,is_template};",
            "return [", join("," . PHP_EOL, $this->helpers), "];"
        ]);

        if (file_exists($buildDir)) {
            $file = path_join(DIRECTORY_SEPARATOR, $buildDir, "helper_bundle.php");
            $size = file_put_contents($file, $code);
            if ($size === 0) {
                throw new ErrorException("Could not create file during compilation of helpers");
            }

            return $file;
        }

        return null;
    }

    protected function compilePartials(array $paths, string $buildDir)
    {
        foreach ($paths as $path) {
            if (!file_exists($path)) {
                continue;
            }

            $name = preg_replace("/\..+$/", "", basename($path));

            $partial = file_get_contents($path);
            $this->partials[] = "\"{$name}\" => \"{$partial}\"";
        }

        $code = join(PHP_EOL, [
            "<?php",
            "return [", join("," . PHP_EOL, $this->partials), "];"
        ]);

        if (file_exists($buildDir)) {
            $file = path_join(DIRECTORY_SEPARATOR, $buildDir, "partials_bundle.php");
            $size = file_put_contents($file, $code);
            if ($size === 0) {
                throw new ErrorException("Could not create file during compilation of partials");
            }

            return $file;
        }

        return null;
    }

    protected function compileTemplate(string $path, string $buildDir)
    {
        if (!file_exists($path)) {
            return null;
        }

        $template = file_get_contents($path);
        $code = LightnCandy::compile($template, $this->context);
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
     */
    public function layout($layout, ...$templates)
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
     */
    public function render($object, bool $isDevMode = true)
    {

        $data = $object->__toRender($isDevMode);

        if ($isDevMode) {

            $context = $this->context;
            $template = file_get_contents($data->file);
            $compiled = LightnCandy::compile($template, $context);
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

        $toRender = array_merge_recursive($data->properties, $data->methods);
        return $renderer($toRender);
    }

    public function prepare(bool $isDevMode = true, array $overrides = [])
    {

        $context = [
            "flags" => ($isDevMode ?
                (LightnCandy::FLAG_ERROR_EXCEPTION | LightnCandy::FLAG_RENDER_DEBUG)
                : LightnCandy::FLAG_ERROR_LOG) | // options for error catching and debug
                ($isDevMode ? LightnCandy::FLAG_STANDALONEPHP : LightnCandy::FLAG_BESTPERFORMANCE) |
                LightnCandy::FLAG_HANDLEBARSJS_FULL |
                LightnCandy::FLAG_ADVARNAME |
                LightnCandy::FLAG_NAMEDARG |
                LightnCandy::FLAG_PARENT,
            "prepartial" => function ($context, $template, $name) {
                return "<!-- partial start: $name -->$template<!-- partial end: $name -->";
            }
        ];

        $this->context = array_merge($context, $overrides);

        return $this->context;
    }

    public function build(bool $isDevMode = true)
    {


        $this->compileHelpers(
            matchFilesByExtension(
                path_join(DIRECTORY_SEPARATOR, $this->root, $this->config["helpers"]["dir"]),
                $this->config["helpers"]["fileExtension"],
                $this->config["helpers"]["excludeDir"],
            ),
            path_join(DIRECTORY_SEPARATOR, $this->root, ".cache", $this->config["helpers"]["buildDir"])
        );

        $this->compilePartials(
            matchFilesByExtension(
                path_join(DIRECTORY_SEPARATOR, $this->root, $this->config["partials"]["dir"]),
                $this->config["partials"]["fileExtension"],
                $this->config["partials"]["excludeDir"],
            ),
            path_join(DIRECTORY_SEPARATOR, $this->root, ".cache", $this->config["partials"]["buildDir"])
        );

        $this->loadResources($isDevMode);

        if (!$isDevMode) {
            throw new \Error("Production mode not yet implemented");
            exit;
            # for Production
            /*
            $templates = matchFilesByExtension(
                path_join(DIRECTORY_SEPARATOR, $this->root, $this->config["templates"]["dir"]),
                $this->config["templates"]["fileExtension"],
                $this->config["templates"]["excludeDir"]
            );

            foreach ($templates as $path) {
                $this->compileTemplate(
                    $path,
                    path_join(DIRECTORY_SEPARATOR, $this->root, ".cache", $this->config["templates"]["buildDir"])
                );
            }
        */
        }
    }
}
