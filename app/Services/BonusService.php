<?php

namespace App\Services;

use App\Models\BonusChoice;
use App\Models\BonusRule;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Bonus pembelian: beli X (produk tertentu) dapat hadiah, berupa produk tetap atau pilihan bebas dari daftar.
 * Hadiah dimasukkan ke pesanan sebagai item harga Rp0 sehingga stok, batch, dan pengiriman ikut diproses
 * seperti item biasa. Tidak memengaruhi subtotal, diskon MOQ, maupun pajak.
 */
class BonusService
{
    /**
     * Aturan yang sedang berjalan dan berlaku untuk tipe customer ini.
     *
     * @return Collection<int, BonusRule>
     */
    public function rulesFor(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        return BonusRule::running()->with(['buyProducts:id', 'giftProducts'])->orderBy('name')->get()
            ->filter(fn (BonusRule $r) => in_array($user->role, (array) $r->customer_types, true))
            ->values();
    }

    /**
     * Jumlah (dalam satuan aturan) produk syarat yang ada di keranjang.
     */
    private function purchasedUnits(BonusRule $rule, Collection $carts): float
    {
        $buyIds = $rule->buyProducts->pluck('id')->all();
        $total = 0.0;
        foreach ($carts as $cart) {
            if (! in_array($cart->product_id, $buyIds, true) || ! $cart->product) {
                continue;
            }
            $per = $rule->unit === 'carton' ? max(1, $cart->product->unitsPerLargeEffective()) : 1;
            $total += ((int) $cart->quantity) / $per;
        }

        return $total;
    }

    /**
     * Evaluasi semua aturan terhadap keranjang. Satu entri per aturan yang relevan (ada syarat di keranjang
     * atau sudah tercapai), berisi status tercapai, jumlah hadiah, dan hadiah terpilih.
     *
     * @return array<int, array<string, mixed>>
     */
    public function evaluate(Collection $carts, ?User $user): array
    {
        $choices = $user ? BonusChoice::where('user_id', $user->id)->pluck('product_id', 'bonus_rule_id') : collect();
        $entries = [];

        foreach ($this->rulesFor($user) as $rule) {
            $purchased = $this->purchasedUnits($rule, $carts);
            if ($purchased <= 0) {
                continue;
            }

            $buy = max(1, (int) $rule->buy_qty);
            $qualified = $purchased >= $buy;
            $times = $qualified ? ($rule->is_multiple ? (int) floor($purchased / $buy) : 1) : 0;
            $giftUnits = $times * (int) $rule->gift_qty;
            if ($rule->max_gift_qty) {
                $giftUnits = min($giftUnits, (int) $rule->max_gift_qty);
            }

            $options = $rule->giftProducts;
            $selected = null;
            if ($rule->isFixed()) {
                $selected = $options->first();
            } elseif ($choices->has($rule->id)) {
                $selected = $options->firstWhere('id', $choices->get($rule->id));
            }

            $remaining = $qualified
                ? ($rule->is_multiple ? $buy - (int) floor(fmod($purchased, $buy)) : 0)
                : (int) ceil($buy - $purchased);
            if ($qualified && $rule->is_multiple && $remaining <= 0) {
                $remaining = $buy;
            }

            $entries[] = [
                'rule' => $rule,
                'purchased' => $purchased,
                'qualified' => $qualified,
                'gift_units' => $giftUnits,
                'gift_pcs' => $selected ? $giftUnits * ($rule->unit === 'carton' ? max(1, $selected->unitsPerLargeEffective()) : 1) : 0,
                'options' => $options,
                'selected' => $selected,
                'needs_choice' => $qualified && ! $rule->isFixed() && ! $selected,
                'remaining' => $remaining,
            ];
        }

        return $entries;
    }

    /**
     * Baris hadiah sebagai objek Cart sementara (tidak disimpan) agar bisa diproses sama seperti item keranjang.
     * Hanya aturan yang tercapai dan sudah punya hadiah terpilih.
     *
     * @return Collection<int, Cart>
     */
    public function giftLines(Collection $carts, ?User $user): Collection
    {
        $reference = $carts->first();
        $lines = collect();

        foreach ($this->evaluate($carts, $user) as $e) {
            if (! $e['qualified'] || ! $e['selected'] || $e['gift_pcs'] < 1) {
                continue;
            }

            $line = new Cart([
                'user_id' => $user->id,
                'cart_type' => 'regular',
                'product_id' => $e['selected']->id,
                'warehouse_id' => $reference?->warehouse_id,
                'quantity' => $e['gift_pcs'],
            ]);
            $line->id = 'bonus-' . $e['rule']->id;
            $line->setRelation('product', $e['selected']);
            $line->setRelation('warehouse', $reference?->warehouse);
            $line->setAttribute('is_bonus', true);
            $line->setAttribute('bonus_rule_id', $e['rule']->id);
            $line->setAttribute('bonus_rule_name', $e['rule']->name);
            $lines->push($line);
        }

        return $lines;
    }

    /** Aturan free_choice yang sudah tercapai tetapi hadiahnya belum dipilih. */
    public function pendingChoices(Collection $carts, ?User $user): array
    {
        return array_values(array_filter($this->evaluate($carts, $user), fn ($e) => $e['needs_choice']));
    }

    public function setChoice(User $user, BonusRule $rule, Product $product): bool
    {
        if ($rule->isFixed() || ! $rule->giftProducts->contains('id', $product->id)) {
            return false;
        }
        BonusChoice::updateOrCreate(['user_id' => $user->id, 'bonus_rule_id' => $rule->id], ['product_id' => $product->id]);

        return true;
    }

    public function clearChoices(User $user): void
    {
        BonusChoice::where('user_id', $user->id)->delete();
    }
}
