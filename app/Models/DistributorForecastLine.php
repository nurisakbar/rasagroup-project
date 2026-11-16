<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris forecast: outlet rekrutan × produk, jumlah per bulan (qty_1..qty_12, satuan botol/pcs).
 */
class DistributorForecastLine extends Model
{
    use HasUuids;

    protected $fillable = [
        'forecast_id', 'outlet_id', 'product_id',
        'qty_1', 'qty_2', 'qty_3', 'qty_4', 'qty_5', 'qty_6', 'qty_7', 'qty_8', 'qty_9', 'qty_10', 'qty_11', 'qty_12',
    ];

    public function forecast(): BelongsTo
    {
        return $this->belongsTo(DistributorForecast::class, 'forecast_id');
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(User::class, 'outlet_id')->withTrashed();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withoutGlobalScopes();
    }

    public function qty(int $month): int
    {
        return (int) $this->{'qty_' . $month};
    }

    public function total(): int
    {
        return array_sum(array_map(fn ($m) => $this->qty($m), range(1, 12)));
    }
}
