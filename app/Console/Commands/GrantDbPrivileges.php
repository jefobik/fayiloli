<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * GrantDbPrivileges — one-time setup command
 *
 * Grants the necessary PostgreSQL privileges to the EDMS application user
 * (edms_user) so that Artisan migrations and stancl/tenancy's CreateDatabase
 * job can both execute without "permission denied" errors.
 *
 * Run once after creating the fayiloli_central database as postgres superuser:
 *
 *   # 1. Add superuser credentials to .env (remove after the command succeeds)
 *   DB_ADMIN_USERNAME=postgres
 *   DB_ADMIN_PASSWORD=<postgres-password>
 *
 *   # 2. Run the command
 *   php artisan db:grant-privileges
 *
 *   # 3. Then run migrations normally
 *   php artisan migrate --seed
 *
 * What the command grants:
 *   - CREATE on schema public  → allows migrations to create tables
 *   - USAGE on schema public   → allows reading schema metadata
 *   - Default privileges on tables/sequences → all future tables are accessible
 *   - CREATEDB on the role     → allows stancl/tenancy to provision tenant DBs
 */
class GrantDbPrivileges extends Command
{
    protected $signature = 'db:grant-privileges
                            {--app-user= : The app DB user to grant privileges to (default: edms_user from .env)}
                            {--connection=pgsql_admin : The superuser connection to use}
                            {--dry-run : Print the SQL statements without executing them}';

    protected $description = 'Grant CREATE/USAGE schema privileges and CREATEDB to the EDMS app user (run once as superuser)';

    public function handle(): int
    {
        $connection = $this->option('connection');
        $appUser    = $this->option('app-user')
            ?: config('database.connections.pgsql.username', 'edms_user');
        $database   = config("database.connections.{$connection}.database", 'fayiloli_central');
        $dryRun     = (bool) $this->option('dry-run');

        // ── Validate the admin connection is configured ───────────────────────
        $adminUser = config("database.connections.{$connection}.username");
        if (empty($adminUser)) {
            $this->error("Connection [{$connection}] is not configured.");
            $this->line('Add DB_ADMIN_USERNAME and DB_ADMIN_PASSWORD to your .env file.');
            return self::FAILURE;
        }

        $this->info("Granting PostgreSQL privileges on database [{$database}]");
        $this->info("  App user  : {$appUser}");
        $this->info("  Admin via : {$connection} (user: {$adminUser})");
        $this->newLine();

        // ── SQL statements to execute ─────────────────────────────────────────
        $statements = [
            // Schema-level privileges
            "GRANT USAGE  ON SCHEMA public TO \"{$appUser}\"",
            "GRANT CREATE ON SCHEMA public TO \"{$appUser}\"",

            // All existing tables and sequences (idempotent)
            "GRANT ALL PRIVILEGES ON ALL TABLES    IN SCHEMA public TO \"{$appUser}\"",
            "GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO \"{$appUser}\"",

            // Default privileges: all FUTURE objects created by any role
            "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES    TO \"{$appUser}\"",
            "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO \"{$appUser}\"",
        ];

        // ── CREATEDB must be run outside a transaction against any database ───
        $createdbStatement = "ALTER ROLE \"{$appUser}\" CREATEDB";

        if ($dryRun) {
            $this->warn('[DRY RUN] The following SQL would be executed:');
            $this->newLine();
            foreach ($statements as $sql) {
                $this->line("  {$sql};");
            }
            $this->line("  {$createdbStatement};");
            $this->newLine();
            $this->comment('Re-run without --dry-run to apply.');
            return self::SUCCESS;
        }

        // ── Execute privilege grants ──────────────────────────────────────────
        try {
            DB::connection($connection)->transaction(function () use ($connection, $statements) {
                foreach ($statements as $sql) {
                    $this->line("  <fg=cyan>→</> {$sql}");
                    DB::connection($connection)->statement($sql);
                }
            });
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('Failed to grant schema privileges: ' . $e->getMessage());
            $this->hint($e);
            return self::FAILURE;
        }

        // ── ALTER ROLE (DDL on roles — cannot be in a transaction block in all PG versions) ──
        try {
            $this->line("  <fg=cyan>→</> {$createdbStatement}");
            DB::connection($connection)->statement($createdbStatement);
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('Failed to grant CREATEDB: ' . $e->getMessage());
            $this->hint($e);
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('All privileges granted successfully.');
        $this->newLine();
        $this->comment('Next step:');
        $this->line('  php artisan migrate --seed');

        return self::SUCCESS;
    }

    private function hint(\Throwable $e): void
    {
        if (str_contains($e->getMessage(), 'password authentication failed')) {
            $this->line('');
            $this->line('Hint: Set <fg=yellow>DB_ADMIN_USERNAME</> and <fg=yellow>DB_ADMIN_PASSWORD</> in your .env,');
            $this->line('      then run: <fg=yellow>php artisan config:clear && php artisan db:grant-privileges</>');
        } elseif (str_contains($e->getMessage(), 'must be superuser')) {
            $this->line('');
            $this->line('Hint: The DB_ADMIN_USERNAME must be a PostgreSQL superuser (e.g. <fg=yellow>postgres</>).');
        }
    }
}
