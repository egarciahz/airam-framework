<?php

namespace Core\Template;

use Core\Services\FilesystemCache;

interface TemplateProcessorInterface
{
    public function __construct(TemplateProcessorRuntime $runtimeProcessor, FilesystemCache $filesystem);
    public function compile(string $template); // by single file
    public function build(); // build all directory
}
