<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Admin\OutletController as AdminOutletController;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Menu Outlet dan Reseller di panel Admin Hub: sama seperti di admin pusat, tetapi tanpa tab ECOM
 * (user ecom tidak bisa dilihat/diubah) dan tanpa hapus user.
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

    protected function tabs(): array
    {
        return ['outlet', 'reseller'];
    }

    protected function actorId(): ?string
    {
        return auth()->id();
    }
}
