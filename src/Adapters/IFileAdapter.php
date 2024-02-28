<?php

namespace Zenoware\Laravel\SqlDumpValidator\Adapters;

interface IFileAdapter
{
    public function findFiles(string $path, int $depth): array;
}
