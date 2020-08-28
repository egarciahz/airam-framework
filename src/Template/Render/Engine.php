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

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function loadResources()
    {
        // load partials and helpers from build
    }

    /**
     * @param string[] $paths
     */
    protected function compileHelpers(array $paths)
    {
        foreach ($paths as $path) {
            if (!file_exists($path)) {
                continue;
            }

            // ...
        }
    }

    protected function compilePartial(string $path)
    {
        if (!file_exists($path)) {
            return null;
        }

        // ...
    }

    protected function compileTemplate(string $path)
    {
        if (!file_exists($path)) {
            return null;
        }

        // ...
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
    public function render($object)
    {
        $isDevMode = !$this->app->isProdMode();
        $data = $object->__toRender($isDevMode);

        if ($isDevMode) {

            $config = $this->prepare($isDevMode);
            $compiled = LightnCandy::compile(file_get_contents($data->file), $config);
            $renderer = LightnCandy::prepare($compiled);

            $data->properties["Template"]["file"] = $data->file;
        } else {

            $file = makeTemplateFileName($data->file);
            $file = path_join(DIRECTORY_SEPARATOR, getenv("ROOT_DIR"), ".cache", "render", $file);
            $data->properties["Template"]["file"] = $file;

            if (!file_exists($file)) {

                $config = $this->prepare($isDevMode);
                $phpStr = LightnCandy::compile(file_get_contents($data->file), $config);
                $size = file_put_contents($file, $phpStr, LOCK_EX);
                if ($size === 0) {
                    throw new ErrorException("Dont created file during compilation.", 500);
                }
            }

            $renderer = require $file;
        }

        $context = array_merge_recursive($data->properties, $data->methods);
        return $renderer($context);
    }

    public function prepare(bool $isDevMode = true, array $overrides = [])
    {
        $config = [
            "flags" => ($isDevMode ?
                (LightnCandy::FLAG_ERROR_EXCEPTION | LightnCandy::FLAG_RENDER_DEBUG)
                : LightnCandy::FLAG_ERROR_LOG) | // options for error catching and debug
                ($isDevMode ? LightnCandy::FLAG_STANDALONEPHP : LightnCandy::FLAG_BESTPERFORMANCE) |
                LightnCandy::FLAG_HANDLEBARSJS_FULL |
                LightnCandy::FLAG_ADVARNAME |
                LightnCandy::FLAG_NAMEDARG |
                LightnCandy::FLAG_PARENT,
            "helpers" => $this->helpers,
            "partials" => $this->partials,
            'prepartial' => function ($context, $template, $name) {
                return "<!-- partial start: $name -->$template<!-- partial end: $name -->";
            }
        ];


        return $config;
    }

    public function build(bool $isDevMode = true)
    {
        $this->compileHelpers(matchFilesByExtension("", [".helper.php"]));

        $partials = matchFilesByExtension("", [".partial.hbs", ".partial.html"]);
        foreach ($partials as $partial) {
            $this->compilePartial($partial);
        }

        if (!$isDevMode) {
            $templates = matchFilesByExtension("", [".template.html"]);
            foreach ($templates as $template) {
                $this->compileTemplate($template);
            }
        }
    }
}
