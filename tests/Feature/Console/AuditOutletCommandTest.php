<?php

namespace Tests\Feature\Console;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditOutletCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_command_is_read_only_and_reports_system_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->artisan('outlet:audit')
            ->expectsOutputToContain('Outlet audit (read-only)')
            ->expectsOutputToContain('Outlets: 1 (1 active, 0 sales-enabled)')
            ->assertSuccessful();
    }

    public function test_strict_audit_passes_for_clean_system_seed(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->artisan('outlet:audit', ['--strict' => true])
            ->assertSuccessful();
    }
}
