<?php

namespace App\Traits;

use App\Models\OperationalHour;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasOperationalHours
{
    /**
     * Get all of the model's operational hours.
     */
    public function operationalHours(): MorphMany
    {
        return $this->morphMany(OperationalHour::class, 'operatable')->orderBy('day');
    }

    /**
     * Generate default operational hours for the model.
     */
    public function generateDefaultOperationalHours(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            $this->operationalHours()->updateOrCreate(
                ['day' => $i],
                [
                    'is_open' => true,
                    'open_time' => '08:00',
                    'close_time' => '20:00',
                ]
            );
        }
    }

    /**
     * Boot the trait.
     */
    protected static function bootHasOperationalHours(): void
    {
        static::created(function ($model) {
            // Check if it's a warehouse hub or a distributor user
            $shouldGenerate = false;

            if ($model instanceof \App\Models\Warehouse) {
                $shouldGenerate = true;
            } elseif ($model instanceof \App\Models\User && $model->role === \App\Models\User::ROLE_DISTRIBUTOR) {
                $shouldGenerate = true;
            }

            if ($shouldGenerate) {
                $model->generateDefaultOperationalHours();
            }
        });
    }
}
