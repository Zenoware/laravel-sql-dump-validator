<?php

namespace Zenoware\Laravel\SqlDumpValidator\Events;

use Zenoware\Laravel\SqlDumpValidator\Dto\SqlDumpFileMetadata;

class SqlDumpFileOk
{
    public SqlDumpFileMetadata $metadata;

    public function __construct(SqlDumpFileMetadata $metadata)
    {
        $this->metadata = $metadata;
    }
}
