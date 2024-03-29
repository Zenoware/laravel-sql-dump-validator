# Laravel SQL Dump Validator

![PHPUnit](https://github.com/Zenoware/laravel-sql-dump-validator/actions/workflows/phpunit.yml/badge.svg)

This package provides a Laravel command and a service to validate SQL dump files for corruption. It uses the `gunzip -t` command to test the integrity of `.sql.gz` files.

## Installation

Use composer to install the package:

```bash
composer require zenoware/laravel-sql-dump-validator
```

## Requirements

The package requires the `gunzip` command to be installed on your system. The command is used to test the integrity of `.sql.gz` files.

## Usage

### Command

You can use the provided command to validate SQL dump files. The command accepts a path and an optional depth parameter.

```bash
php artisan zenoware:sqldump:validate {path} {--depth=2}
```

Validating SQL dump files in the `storage/app/sqldumps` directory:

```bash
php artisan zenoware:sqldump:validate storage/app/sqldumps
```

Validating SQL dump files in the `storage/app/sqldumps` directory with a maximum depth of 3:

```bash
php artisan zenoware:sqldump:validate storage/app/sqldumps --depth=3
```

The `path` argument is the directory where your SQL dump files are located. The `--depth` option is the maximum directory depth for the file search.

The command will print the validation results to the console. If a file is corrupted, it will print an error message with the file path and the error details.

### On Routes and Jobs

Since the command uses `SqlDumpValidatorService` under the hood, you can also listen to events to handle the validation results by just running the command from within a job or a route.

```bash
# Be wary of timeouts when calling the command from a route or a job
Artisan::call('zenoware:sqldump:validate', ['path' => storage_path('app/sqldumps')]);
```

### Service

You can also use the `SqlDumpValidatorService` directly in your code. The service accepts a path, a depth, and a callback function.

```php
$validatorService->validateSqlDumps($path, $depth, function (SqlDumpFileMetadata $metadata, array $errors) {
    // Handle the validation results
});
```

The callback function is called for each file processed. It receives a `SqlDumpFileMetadata` object and an array of errors. If the file is OK, the errors array is empty.

## Service Provider

The package includes a service provider that registers the `SqlDumpValidatorService` and the `IFileAdapter` interface with the Laravel service container.

If you want to use a different file adapter, you can bind your own implementation to the `IFileAdapter` interface in your own service provider.

## Listening to Events

The service used by the command fires an event for each file processed. You can listen/subscribe to events to handle the validation results.

```php
class SqlDumpEventSubscriber
{
    public function subscribe($events)
    {
        $events->listen(
            SqlDumpFileOk::class,
            function ($event) {
                Log::info('SqlDumpFileOk event fired', ['metadata' => $event->metadata]);
            }
        );

        $events->listen(
            SqlDumpFileCorrupted::class,
            function ($event) {
                Log::info('SqlDumpFileCorrupted event fired', ['metadata' => $event->metadata, 'errors' => $event->errors]);
            }
        );
    }
}
```

```php
class EventServiceProvider extends ServiceProvider
{
    // [...]

    protected $subscribe = [
        SqlDumpEventSubscriber::class,
    ];

    // [...]
}
```

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License

[MIT](https://choosealicense.com/licenses/mit/)