<?php

namespace Zenoware\Laravel\SqlDumpValidator\Services;

use RuntimeException;
use Symfony\Component\Process\Process;
use Zenoware\Laravel\SqlDumpValidator\Adapters\IFileAdapter;
use Zenoware\Laravel\SqlDumpValidator\Dto\SqlDumpFileMetadata;
use Zenoware\Laravel\SqlDumpValidator\Events\SqlDumpFileOk;
use Zenoware\Laravel\SqlDumpValidator\Events\SqlDumpFileCorrupted;
use Illuminate\Support\Facades\Event;
use Zenoware\Laravel\SqlDumpValidator\Factories\ProcessFactory;

class SqlDumpValidatorService
{
    private const STATUS_CODE_CORRUPTED = 1;

    private IFileAdapter $adapter;

    private ProcessFactory $processFactory;

    public function __construct(IFileAdapter $adapter, ProcessFactory $processFactory = null)
    {
        $this->adapter = $adapter;
        $this->processFactory = $processFactory ?? new ProcessFactory();
    }

    public function validateSqlDumps(string $path, int $depth, callable $onFileProcessed): void
    {
        $files = $this->adapter->findFiles($path, $depth);

        foreach ($files as $file) {
            $process = $this->processFactory->create(['gunzip', '-t', $file]);
            $process->run();

            $errorOutput = $process->getErrorOutput();
            $returnCode = $process->getExitCode();

            if ($returnCode !== 0 && $returnCode !== 1) {
                throw new RuntimeException("Unexpected return code: $returnCode");
            }

            $errors = [];

            if ($returnCode === self::STATUS_CODE_CORRUPTED) {
                $errors = explode("\n", trim($errorOutput));
                Event::dispatch(new SqlDumpFileCorrupted($errors, new SqlDumpFileMetadata($file, time())));
            } else {
                Event::dispatch(new SqlDumpFileOk(new SqlDumpFileMetadata($file, time())));
            }

            $metadata = new SqlDumpFileMetadata($file, time());

            $onFileProcessed($metadata, $errors);
        }
    }
}