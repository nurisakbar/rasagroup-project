<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'cart_type',
        'session_id',
        'product_id',
        'warehouse_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Merge session-based cart items to the authenticated user's cart.
     */
    public static function mergeSessionCartToUser($userId, $sessionId): void
    {
        $sessionCarts = self::where('session_id', $sessionId)
            ->where('cart_type', 'regular')
            ->get();

        if ($sessionCarts->isEmpty()) {
            return;
        }

        foreach ($sessionCarts as $sessionCart) {
            $userCart = self::where('user_id', $userId)
                ->where('product_id', $sessionCart->product_id)
                ->where('warehouse_id', $sessionCart->warehouse_id)
                ->where('cart_type', 'regular')
                ->first();

            if ($userCart) {
                // Merge quantity
                $userCart->quantity += $sessionCart->quantity;
                $userCart->save();
                $sessionCart->delete();
            } else {
                // Transfer cart to user
                $sessionCart->user_id = $userId;
                $sessionCart->session_id = null;
                $sessionCart->save();
            }
        }
    }
}
