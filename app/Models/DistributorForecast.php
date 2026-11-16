<?php

namespace App\Models;

use App\Support\Wib;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Forecast kebutuhan satu distributor untuk satu tahun (Januari–Desember).
 * Bulan yang sudah berjalan (bulan ini dan sebelumnya) dikunci; bulan berikutnya masih bisa direvisi.
 */
class DistributorForecast extends Model
{
    use HasUuids;

    public const MONTHS = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
        7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];

    public const MONTH_LABELS = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    protected $fillable = ['distributor_id', 'year'];

    protected $casts = ['year' => 'integer'];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DistributorForecastLine::class, 'forecast_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(DistributorForecastRevision::class, 'forecast_id');
    }

    /** Tahun yang boleh diisi: tahun ini dan tahun depan. */
    public static function selectableYears(): array
    {
        $year = (int) now(Wib::TZ)->year;

        return [$year, $year + 1];
    }

    public static function isMonthLocked(int $year, int $month): bool
    {
        $now = now(Wib::TZ);

        return $year < $now->year || ($year === (int) $now->year && $month <= $now->month);
    }

    public function monthLocked(int $month): bool
    {
        return self::isMonthLocked($this->year, $month);
    }

    /** @return list<int> */
    public function openMonths(): array
    {
        return array_values(array_filter(array_keys(self::MONTHS), fn ($m) => ! $this->monthLocked($m)));
    }
}
