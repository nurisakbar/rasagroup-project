<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Admin\OutletController as AdminOutletController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

/**
 * Menu Outlet dan Reseller di panel Admin Hub: hanya outlet & reseller yang tercatat di hub ini,
 * tanpa tab ECOM dan tanpa hapus user. Outlet baru otomatis tercatat di hub ini.
 */
class OutletController extends AdminOutletController implements HasMiddleware
{
    protected string $routePrefix = 'warehouse.outlets';

    protected string $layout = 'layouts.warehouse';

    protected bool $canDelete = false;

    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                abort_unless(auth()->user()?->sub_role === 'admin', 403, 'Akses khusus Admin Hub.');

                return $next($request);
            }),
        ];
    }

    private function hubId(): string
    {
        return (string) auth()->user()->warehouse_id;
    }

    protected function tabs(): array
    {
        return ['outlet', 'reseller'];
    }

    protected function actorId(): ?string
    {
        return auth()->id();
    }

    protected function scopeToPanel($query)
    {
        return $query->where('hub_id', $this->hubId());
    }

    protected function canChooseHub(): bool
    {
        return false;
    }

    /** Hanya sales dari hub ini (ditambah sales yang sudah terpasang agar tidak hilang). */
    protected function salesOptions(?User $user = null): \Illuminate\Support\Collection
    {
        $options = User::salesCodeOptionsForHub($this->hubId());
        if ($user?->sales_code && ! $options->contains('sales_code', $user->sales_code)) {
            $current = User::salesCodeOptions()->firstWhere('sales_code', $user->sales_code);
            if ($current) {
                $options->push($current);
            }
        }

        return $options->values();
    }

    protected function salesCodeRules(?User $user = null): array
    {
        return ['nullable', 'string', Rule::in($this->salesOptions($user)->pluck('sales_code')->all())];
    }

    protected function resolveHubId(Request $request, ?User $user, ?string $salesCode): ?string
    {
        return $this->hubId();
    }
}
