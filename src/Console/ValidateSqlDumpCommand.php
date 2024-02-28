<?php

namespace Zenoware\Laravel\SqlDumpValidator\Console;

use Illuminate\Console\Command;
use Zenoware\Laravel\SqlDumpValidator\Dto\SqlDumpFileMetadata;
use Zenoware\Laravel\SqlDumpValidator\Services\SqlDumpValidatorService;

class ValidateSqlDumpCommand extends Command
{
    protected $signature = 'zenoware:sqldump:validate {path} {--depth=2}';
    protected $description = 'Check .sql.gz files for corruption.';

    public function handle(SqlDumpValidatorService $validatorService)
    {
        $path = $this->argument('path');
        $depth = $this->option('depth');

        $validatorService->validateSqlDumps($path, $depth, function (SqlDumpFileMetadata $metadata, array $errors) {
            if (!empty($errors)) {
                $this->error("Validation failed for file {$metadata->path}: " . implode(', ', $errors));
            } else {
                $this->info("Validation successful for file {$metadata->path}");
            }
        });
    }
}
