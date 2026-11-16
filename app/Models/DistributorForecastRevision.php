<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Catatan audit perubahan angka forecast (per bulan): siapa, kapan, dari web atau Excel.
 */
class DistributorForecastRevision extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['forecast_id', 'outlet_id', 'product_id', 'month', 'old_qty', 'new_qty', 'changed_by', 'source'];

    protected $casts = ['created_at' => 'datetime'];
}
