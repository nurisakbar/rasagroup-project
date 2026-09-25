<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Admin\ManualOrderController as AdminManualOrderController;
use App\Models\Warehouse;
use App\Services\WmsService;
use Illuminate\Http\Request;

class ManualOrderController extends AdminManualOrderController
{
    protected function currentWarehouse(): Warehouse
    {
        $warehouse = auth()->user()?->warehouse;
        if (! $warehouse || ! $warehouse->is_active) {
            abort(403, 'Hub tidak ditemukan atau tidak aktif.');
        }

        return $warehouse;
    }

    protected function constrainManualOrderWarehouse(Request $request): void
    {
        $request->merge(['source_warehouse_id' => $this->currentWarehouse()->id]);
    }

    public function productBatches(Request $request, WmsService $wms)
    {
        $request->merge(['warehouse_id' => $this->currentWarehouse()->id]);

        return parent::productBatches($request, $wms);
    }

    protected function redirectAfterManualStore($order)
    {
        $order->refresh();
        $ownHub = $order->source_warehouse_id === $this->currentWarehouse()->id;

        if ($ownHub && $order->isReleasedToHub()) {
            return redirect()
                ->route('warehouse.orders.show', $order)
                ->with('success', 'Transaksi '.$order->order_number.' berhasil dibuat.');
        }

        $message = 'Transaksi '.$order->order_number.' berhasil dibuat.';
        if ($ownHub && ! $order->isReleasedToHub()) {
            $message .= ' Pesanan menunggu approval finance sebelum tampil di daftar hub.';
        } elseif (! $ownHub) {
            $message .= ' Pesanan tercatat di hub pengirim yang dipilih.';
        }

        return redirect()
            ->route('warehouse.orders.index')
            ->with('success', $message);
    }

    protected function manualOrderViewData(): array
    {
        $data = parent::manualOrderViewData();
        $warehouse = $this->currentWarehouse();
        $data['manualLayout'] = 'layouts.warehouse';
        $data['lockWarehouse'] = true;
        $data['warehouses'] = collect([$warehouse]);
        $data['defaultWarehouseId'] = $warehouse->id;
        $data['manualUrls'] = [
            'store' => route('warehouse.orders.store'),
            'index' => route('warehouse.orders.index'),
            'searchSales' => route('warehouse.orders.search-sales'),
            'searchCustomers' => route('warehouse.orders.search-customers'),
            'searchProducts' => route('warehouse.orders.search-products'),
            'productBatches' => route('warehouse.orders.product-batches'),
            'previewPricing' => route('warehouse.orders.preview-pricing'),
            'customerAddresses' => route('warehouse.orders.customer-addresses', ['user' => '00000000-0000-0000-0000-000000000000']),
        ];

        return $data;
    }
}
