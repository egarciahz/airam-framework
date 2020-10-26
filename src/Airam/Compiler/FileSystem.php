<?php

namespace Airam\Compiler;

use function Airam\Commons\path_join;
use function Airam\Template\Lib\cleanFileName;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

class FileSystem
{

    /**
     * @param bool $isDevMode
     */
    static public $isDevMode = true;

    public static function makeDirectoryMap(string $base, array $subdirs)
    {
        static::makeDirectory($base);
        foreach ($subdirs as $sub) {
            $sub = path_join(DIRECTORY_SEPARATOR, $base, $sub);
            static::makeDirectory($sub);
        }
    }

    public static function makeDirectory(string $directory)
    {
        if (!is_dir($directory) && !@mkdir($directory, 0777, true)) {
            return static::error(sprintf('Compilation directory does not exist and can not be created: [%s]', $directory));
        }
        if (!is_writable($directory)) {
            return static::error(sprintf('Compilation directory is not writable: %s.', $directory));
        }

        return true;
    }

    public static function write(string $path, string $content)
    {
        $dir = dirname($path);
        $name = basename($path);
        $tempName = date("s-u-") . cleanFileName($name);

        if (!is_dir($dir) && !static::makeDirectory($dir)) {
            return static::error(sprintf('Error while writing %s under %s directory', $name, $dir));
        }

        $tmpFile = tempnam($dir, $tempName);
        @chmod($tmpFile, 0666);

        $written = file_put_contents($tmpFile, $content);
        if (!$written) {
            @unlink($tmpFile);
            return static::error(sprintf('Error ocurred while writing to %s', $tmpFile));
        }

        $renamed = @rename($tmpFile, $path);
        @unlink($tmpFile);
        if (!$renamed) {
            return static::error(sprintf('Error ocurred while renaming %s to %s', $tmpFile, $name));
        }

        return $written;
    }

    public static function remove(string $path)
    {
        $cursor = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $nodes = new RecursiveIteratorIterator($cursor, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($nodes as $inode) {
            if ($inode->isDir()) {
                rmdir($inode->getRealPath());
            } else {
                unlink($inode->getRealPath());
            }
        }

        rmdir($path);
    }

    private static function error(string $message)
    {
        if (!static::$isDevMode) {
            error_log($message);
            return false;
        }

        throw new RuntimeException($message);
    }
}
