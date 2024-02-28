<?php

namespace Zenoware\Laravel\SqlDumpValidator\Events;

use Zenoware\Laravel\SqlDumpValidator\Dto\SqlDumpFileMetadata;

class SqlDumpFileCorrupted
{
    public SqlDumpFileMetadata $metadata;

    /** @var string[] */
    public array $errors = [];

    public function __construct($errors, SqlDumpFileMetadata $metadata)
    {
        $this->errors = $errors;
        $this->metadata = $metadata;
    }
}
