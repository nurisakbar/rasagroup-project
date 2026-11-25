<?php

namespace App\Console\Commands;

use App\Support\AdminAccess\AdminAccess;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class AdminAccessCheckRoutes extends Command
{
    protected $signature = 'admin-access:check-routes';

    protected $description = 'Daftar rute admin yang belum terpetakan ke modul hak akses (rute ini akan ditolak untuk non-super_admin)';

    public function handle(): int
    {
        $unmapped = [];
        $total = 0;
        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if (! $name || ! str_starts_with($name, 'admin.') || AdminAccess::isWhitelisted($name)) {
                continue;
            }
            $total++;
            if (AdminAccess::moduleKeyForRoute($name) === null) {
                $unmapped[] = [$name, implode('|', $route->methods())];
            }
        }

        $this->info("Rute admin diperiksa: {$total}");
        if ($unmapped) {
            $this->warn(count($unmapped) . ' rute belum terpetakan:');
            $this->table(['Nama rute', 'Metode'], $unmapped);

            return self::FAILURE;
        }
        $this->info('Semua rute admin sudah terpetakan ke modul.');

        return self::SUCCESS;
    }
}
