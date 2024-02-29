<?php


namespace Tests\Unit\Services;

use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\TestCase;
use Zenoware\Laravel\SqlDumpValidator\Services\SqlDumpValidatorService;
use Zenoware\Laravel\SqlDumpValidator\Adapters\IFileAdapter;
use Zenoware\Laravel\SqlDumpValidator\Factories\ProcessFactory;
use Symfony\Component\Process\Process;

/**
 * @coversDefaultClass \Zenoware\Laravel\SqlDumpValidator\Services\SqlDumpValidatorService
 */
class SqlDumpValidatorServiceTest extends TestCase
{
    /**
     * @covers ::validateSqlDumps
     *
     * @dataProvider provideCorruptFiles
     */
    public function testValidateCorruptSqlDumps(string $filePath, int $exitCode): void
    {
        $this->validateSqlDumps($filePath, $exitCode, true);
    }

    /**
     * @covers ::validateSqlDumps
     *
     * @dataProvider provideNonCorruptFiles
     */
    public function testValidateNonCorruptSqlDumps(string $filePath, int $exitCode): void
    {
        $this->validateSqlDumps($filePath, $exitCode, false);
    }

    public static function provideCorruptFiles(): array
    {
        return [
            ['/path/to/corrupt/file1.sql.gz', 1],
            ['/path/to/corrupt/file2.sql.gz', 1],
        ];
    }

    public static function provideNonCorruptFiles(): array
    {
        return [
            ['/path/to/non-corrupt/file1.sql.gz', 0],
            ['/path/to/non-corrupt/file2.sql.gz', 0],
        ];
    }

    private function validateSqlDumps(string $filePath, int $exitCode, bool $isCorrupt): void
    {
        // Arrange
        $adapterMock = $this->createMock(IFileAdapter::class);
        $processFactoryMock = $this->createMock(ProcessFactory::class);
        $processStub = $this->createStub(Process::class);

        $adapterMock->expects($this->once())->method('findFiles')->willReturn([$filePath]);
        $processFactoryMock->expects($this->once())->method('create')->willReturn($processStub);
        $processStub->method('getExitCode')->willReturn($exitCode);

        Event::shouldReceive('dispatch')->andReturnTrue();

        $service = new SqlDumpValidatorService($adapterMock, $processFactoryMock);

        // Act
        $service->validateSqlDumps('/path/to', 2, function ($metadata, $errors) use ($isCorrupt) {
            // Assert
            if ($isCorrupt) {
                $this->assertNotEmpty($errors);
            } else {
                $this->assertEmpty($errors);
            }
        });
    }

    /**
     * @covers ::validateSqlDumps
     */
    public function testValidateSqlDumpsThrowsExceptionForUnexpectedExitCode(): void
    {
        // Arrange
        $adapterMock = $this->createMock(IFileAdapter::class);
        $processFactoryMock = $this->createMock(ProcessFactory::class);
        $processStub = $this->createStub(Process::class);

        $adapterMock->expects($this->once())->method('findFiles')->willReturn(['/path/to/file.sql.gz']);
        $processFactoryMock->expects($this->once())->method('create')->willReturn($processStub);
        $processStub->method('getExitCode')->willReturn(2); // Unexpected exit code

        Event::shouldReceive('dispatch')->andReturnTrue();

        $service = new SqlDumpValidatorService($adapterMock, $processFactoryMock);

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unexpected return code: 2');

        // Act
        $service->validateSqlDumps('/path/to', 2, function ($metadata, $errors) {
            // This code will not be executed
        });
    }
}