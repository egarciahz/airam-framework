<?php

namespace Core\Template\Render;

use Core\Application;
use Core\Template\TemplateInterface;

use LightnCandy\LightnCandy;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class Engine
{
    private $rootDir;
    private $cache;
    private $app;

    private $blockhelpers = [];
    private $hbhelpers = [];
    private $partials = [];
    private $helpers = [];

    public function __construct(Application $app)
    {
        $this->rootDir = getenv("ROOT_DIR");
        $this->app = $app;
    }

    public function helperLoader(bool $isDevMode = true)
    {
    }

    public function compilePartial(string $path)
    {
    }

    public function compileComponent(Data $input)
    {
    }

    public function renderComponent(Data $input)
    {
    }

    private function matchFilesByExtension($folder, $extension)
    {
    }
    
    // prepara los parciales, ayudantes, configuracion de plantillas y el compilador
    public function prepare(bool $isDevMode = true)
    {

    }

    public function build(bool $isDevMode = true)
    {
    }
}
