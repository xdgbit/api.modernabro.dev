<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MigrateAllDatabases extends Command
{
    protected $signature = 'migrate:all-databases';

    protected $description = 'Run migrations on all configured database connections';

    public function handle(): int
    {
        $connections = ['mysql'];

        foreach ($connections as $connection)
        {

            $this->info("Migrating database: {$connection}");

            try
            {
                DB::purge($connection);

                Artisan::call('migrate', [
                    '--database' => $connection,
                    '--force'    => true,
                ]);

                $this->line(Artisan::output());

                $this->info("Successfully migrated: {$connection}");

            } catch (\Throwable $e)
            {

                $this->error("Failed: {$connection}");
                $this->error($e->getMessage());
            }
        }

        $this->info('All database migrations completed.');

        return self::SUCCESS;
    }
}
