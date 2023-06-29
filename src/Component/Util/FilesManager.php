<?php

namespace LiteApi\Component\Util;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;

class FilesManager
{

    public function removeDirRecursive(string $dirPath): void
    {
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            if ($file->isDir()) {
                $this->removeDirRecursive($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dirPath);
    }

    public function getClassesNamesFromPath(string $path, string $baseNamespace): array
    {
        $path = realpath($path);
        $allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $phpFiles = new RegexIterator($allFiles, '/\.php$/');
        $classes = [];
        foreach ($phpFiles as $phpFile) {
            $classes[] = substr(str_replace($path, $baseNamespace, $phpFile->getRealpath()), 0, -4);
        }
        return $classes;
    }

}