<?php

namespace Zenoware\Laravel\SqlDumpValidator\Adapters;

use Symfony\Component\Finder\Finder;

class LocalFileAdapter implements IFileAdapter
{
    /**
     * Find and return an array of .sql.gz file paths within the specified path and up to the specified depth.
     *
     * @param string $path The base path to start searching from.
     * @param int $depth The maximum depth to search for files.
     * @return array An array of .sql.gz file paths.
     */
    public function findFiles(string $path, int $depth = 2): array
    {
        $finder = new Finder();
        $finder->files()->in($path)->name('*.sql.gz')->depth('<=' . $depth);

        $files = [];
        foreach ($finder as $file) {
            // Store the absolute path of the file
            $files[] = $file->getRealPath();
        }

        return $files;
    }
}
