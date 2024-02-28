<?php

namespace Zenoware\Laravel\SqlDumpValidator\Dto;

class SqlDumpFileMetadata
{
    public string $path;
    public string $timestamp;

    public function __construct(string $path, string $timestamp)
    {
        $this->path = $path;
    }
}
