<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonusChoice extends Model
{
    protected $fillable = ['user_id', 'bonus_rule_id', 'product_id'];
}
