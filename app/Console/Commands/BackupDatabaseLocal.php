<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\DbDumper\Databases\Sqlite;
use Throwable;

class BackupDatabaseLocal extends Command
{
    protected $signature = 'backup:database-local';

    protected $description = 'Genera un respaldo local comprimido solo de la base de datos y conserva los ultimos 7 archivos.';

    public function handle(): int
    {
        $connectionName = config('database.default');
        $connection = config("database.connections.{$connectionName}");

        if (! is_array($connection)) {
            $this->error("La conexion de base de datos [{$connectionName}] no existe.");

            return self::FAILURE;
        }

        $directory = config('backup.local_database.directory');
        $keepFiles = (int) config('backup.local_database.keep_files', 7);
        $dumpFile = $directory.'/database-'.now()->format('Y-m-d').'.sql.gz';

        File::ensureDirectoryExists($directory);

        try {
            $this->buildDumper($connection)->dumpToFile($dumpFile);
            $this->deleteOldBackups($directory, $keepFiles);
        } catch (Throwable $exception) {
            $this->error('No se pudo generar el backup local de la base de datos.');
            $this->line($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Backup local generado: {$dumpFile}");

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $connection
     */
    private function buildDumper(array $connection): MySql|PostgreSql|Sqlite
    {
        $driver = $connection['driver'] ?? '';

        return match ($driver) {
            'mysql', 'mariadb' => $this->buildMySqlDumper($connection),
            'pgsql' => $this->buildPostgreSqlDumper($connection),
            'sqlite' => $this->buildSqliteDumper($connection),
            default => throw new \RuntimeException("El driver [{$driver}] no esta soportado para backup local."),
        };
    }

    /**
     * @param  array<string, mixed>  $connection
     */
    private function buildMySqlDumper(array $connection): MySql
    {
        $dumper = MySql::create()
            ->setDbName((string) $connection['database'])
            ->setUserName((string) $connection['username'])
            ->setPassword((string) $connection['password'])
            ->setHost((string) $connection['host'])
            ->setPort((int) $connection['port'])
            ->useSingleTransaction()
            ->includeRoutines()
            ->useCompressor(new GzipCompressor());

        if (! empty($connection['unix_socket'])) {
            $dumper->setSocket((string) $connection['unix_socket']);
        }

        if (! empty($connection['charset'])) {
            $dumper->setDefaultCharacterSet((string) $connection['charset']);
        }

        return $dumper;
    }

    /**
     * @param  array<string, mixed>  $connection
     */
    private function buildPostgreSqlDumper(array $connection): PostgreSql
    {
        return PostgreSql::create()
            ->setDbName((string) $connection['database'])
            ->setUserName((string) $connection['username'])
            ->setPassword((string) $connection['password'])
            ->setHost((string) $connection['host'])
            ->setPort((int) $connection['port'])
            ->useCompressor(new GzipCompressor());
    }

    /**
     * @param  array<string, mixed>  $connection
     */
    private function buildSqliteDumper(array $connection): Sqlite
    {
        return Sqlite::create()
            ->setDbName((string) $connection['database'])
            ->useCompressor(new GzipCompressor());
    }

    private function deleteOldBackups(string $directory, int $keepFiles): void
    {
        collect(File::glob($directory.'/database-*.sql.gz') ?: [])
            ->sortByDesc(fn (string $path): int => File::lastModified($path))
            ->values()
            ->slice(max(0, $keepFiles))
            ->each(fn (string $path): bool => File::delete($path));
    }
}
