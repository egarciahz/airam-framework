<?php

namespace Core\Template;

use Core\Services\FilesystemCache;

class TemplateProcessor implements TemplateProcessorInterface
{
    function __construct(TemplateProcessorRuntime $runtimeProcessor, FilesystemCache $filesystem)
    {
    }

    function compile(string $template)
    {
    }

    function build()
    {
    }
}
