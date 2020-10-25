<?php

namespace Airam\Compiler;

interface Compilable
{
    /**
     * @param string $path folder path for compiling 
     * @param bool $at true if compilation at time is enabled, otherwise false
     * 
     * @return this
     */
    public function enableCompilation(?string $path, bool $at);

    /**
     * @return void
     */
    public function build(): void;
}
