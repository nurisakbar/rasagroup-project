<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorCategoryDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'category_id',
        'discount_percentage',
    ];

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
