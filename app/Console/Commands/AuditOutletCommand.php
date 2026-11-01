<?php

namespace App\Console\Commands;

use App\Models\Outlet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditOutletCommand extends Command
{
    protected $signature = 'outlet:audit {--strict : Exit with failure when an invariant is violated}';

    protected $description = 'Verify outlet, warehouse, assignment, and per-outlet stock invariants';

    public function handle(): int
    {
        $this->info('Outlet audit (read-only)');

        $outlets = DB::table('outlets')->count();
        $activeOutlets = DB::table('outlets')->where('is_active', true)->count();
        $salesOutlets = DB::table('outlets')->where('is_active', true)->where('is_sales_enabled', true)->count();
        $warehouses = DB::table('warehouses')->count();

        $this->line("Outlets: {$outlets} ({$activeOutlets} active, {$salesOutlets} sales-enabled)");
        $this->line("Warehouses: {$warehouses}");

        $issues = array_merge(
            $this->auditWarehouseOutlets(),
            $this->auditUserAssignments(),
            $this->auditSalesOutletWarehouses(),
            $this->auditSettings(),
            $this->auditPusat(),
        );

        if ($issues === []) {
            $this->info('All outlet invariants hold.');
        }

        if ($this->option('strict') && $issues !== []) {
            $this->error('Strict audit failed: '.implode('; ', $issues));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function auditWarehouseOutlets(): array
    {
        $orphans = DB::table('warehouses')->whereNull('outlet_id')->count();

        $this->line("Warehouses without outlet: {$orphans}");

        return $orphans > 0 ? ["{$orphans} warehouses without outlet"] : [];
    }

    private function auditUserAssignments(): array
    {
        $unassigned = DB::table('users')
            ->whereNotExists(fn ($query) => $query->selectRaw('1')
                ->from('user_outlets')
                ->whereColumn('user_outlets.user_id', 'users.id'))
            ->count();

        $this->line("Users without outlet assignment: {$unassigned}");

        return $unassigned > 0 ? ["{$unassigned} users without outlet assignment"] : [];
    }

    private function auditSalesOutletWarehouses(): array
    {
        $issues = [];

        foreach (Outlet::query()->where('is_sales_enabled', true)->get(['id', 'code']) as $outlet) {
            $count = DB::table('warehouses')
                ->where('outlet_id', $outlet->id)
                ->where('is_active', true)
                ->count();

            if ($count === 0) {
                $issues[] = "sales outlet {$outlet->code} has no active warehouse";
            }
        }

        return $issues;
    }

    private function auditSettings(): array
    {
        foreach (['settings', 'payment_settings', 'bank_accounts'] as $table) {
            $global = DB::table($table)->whereNull('outlet_id')->count();
            $scoped = DB::table($table)->whereNotNull('outlet_id')->count();
            $this->line("{$table}: {$global} global, {$scoped} outlet-scoped");
        }

        return [];
    }

    private function auditPusat(): array
    {
        $pusat = DB::table('outlets')->where('code', 'PUSAT')->first();

        if (! $pusat) {
            $this->warn('PUSAT outlet not found.');
        } else {
            $this->line('PUSAT sales enabled: '.((int) $pusat->is_sales_enabled ? 'yes' : 'no'));
            $this->line('PUSAT warehouses: '.DB::table('warehouses')->where('outlet_id', $pusat->id)->count());

            if ($pusat->is_sales_enabled) {
                return ['PUSAT is sales-enabled'];
            }
        }

        return [];
    }
}
