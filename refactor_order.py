import re

with open('app/Http/Controllers/Distributor/OrderController.php', 'r') as f:
    content = f.read()

# 1. cart() & checkout() subtotal calc
content = content.replace(
    'return $user->getProductPrice($cart->product) * $cart->quantity;',
    'return $cart->product->price * $cart->quantity;'
)

# And in cart()/checkout(), update `$cart->display_price`:
content = content.replace(
    '$cart->display_price = $user->getProductPrice($cart->product);',
    '$cart->display_price = $cart->product->price;'
)

# In cart(), calculate discount
cart_discount_logic = """
        $discountPercent = $user->priceLevel ? $user->priceLevel->discount_percentage : 0;
        $discountAmount = ($subtotal * $discountPercent) / 100;

        // Add price info to each cart item for display
"""
content = content.replace('// Add price info to each cart item for display', cart_discount_logic, 1)

# In cart(), compact includes discount
content = content.replace(
    "compact('carts', 'subtotal', 'totalItems', 'potentialPoints')",
    "compact('carts', 'subtotal', 'totalItems', 'potentialPoints', 'discountPercent', 'discountAmount')"
)

# 2. checkout() method
checkout_discount_logic = """
        $discountPercent = $user->priceLevel ? $user->priceLevel->discount_percentage : 0;
        $discountAmount = ($subtotal * $discountPercent) / 100;
        $totalAfterDiscount = $subtotal - $discountAmount;

        $totalWeight = $carts->sum(function ($cart) {
"""
content = content.replace('$totalWeight = $carts->sum(function ($cart) {', checkout_discount_logic, 1)

# $total calculation in checkout
content = content.replace(
    '$total = $subtotal + $shippingCost;',
    '$total = $totalAfterDiscount + $shippingCost;',
    1
)

# compact in checkout
content = content.replace(
    "'subtotal',\n            'shippingCost',",
    "'subtotal',\n            'discountPercent',\n            'discountAmount',\n            'totalAfterDiscount',\n            'shippingCost',"
)

# 3. updateCart() method
update_cart_logic = """            $subtotal = $carts->sum(function ($c) use ($user) {
                return $c->product->price * $c->quantity;
            });
            $discountPercent = $user->priceLevel ? $user->priceLevel->discount_percentage : 0;
            $discountAmount = ($subtotal * $discountPercent) / 100;
            $totalAfterDiscount = $subtotal - $discountAmount;
            $totalItems = $carts->sum('quantity');
            
            $itemDisplayPrice = $cart->product->price;"""

content = content.replace(
"""            $subtotal = $carts->sum(function ($c) use ($user) {
                return $user->getProductPrice($c->product) * $c->quantity;
            });
            $totalItems = $carts->sum('quantity');
            
            $itemDisplayPrice = $user->getProductPrice($cart->product);""", update_cart_logic)

# In updateCart response:
content = content.replace(
    "'cart_subtotal_formatted' => 'Rp ' . number_format($subtotal, 0, ',', '.'),",
    "'cart_subtotal_formatted' => 'Rp ' . number_format($subtotal, 0, ',', '.'),\n                'cart_discount_formatted' => '- Rp ' . number_format($discountAmount, 0, ',', '.'),\n                'cart_total_after_discount_formatted' => 'Rp ' . number_format($totalAfterDiscount, 0, ',', '.'),",
    1
)

# 4. removeFromCart() method
remove_cart_logic = """            $subtotal = $carts->sum(function ($c) use ($user) {
                return $c->product->price * $c->quantity;
            });
            $discountPercent = $user->priceLevel ? $user->priceLevel->discount_percentage : 0;
            $discountAmount = ($subtotal * $discountPercent) / 100;
            $totalAfterDiscount = $subtotal - $discountAmount;
            $totalItems = $carts->sum('quantity');"""

content = content.replace(
"""            $subtotal = $carts->sum(function ($c) use ($user) {
                return $user->getProductPrice($c->product) * $c->quantity;
            });
            $totalItems = $carts->sum('quantity');""", remove_cart_logic)

content = content.replace(
    "'cart_subtotal_formatted' => 'Rp ' . number_format($subtotal, 0, ',', '.'),",
    "'cart_subtotal_formatted' => 'Rp ' . number_format($subtotal, 0, ',', '.'),\n                'cart_discount_formatted' => '- Rp ' . number_format($discountAmount, 0, ',', '.'),\n                'cart_total_after_discount_formatted' => 'Rp ' . number_format($totalAfterDiscount, 0, ',', '.'),",
    1
)

# 5. calculateShipping() method
calc_shipping_logic = """        $discountPercent = $user->priceLevel ? $user->priceLevel->discount_percentage : 0;
        $discountAmount = ($subtotal * $discountPercent) / 100;
        $totalAfterDiscount = $subtotal - $discountAmount;

        $expedition = Expedition::find($request->expedition_id);"""

content = content.replace(
"""        $expedition = Expedition::find($request->expedition_id);""", calc_shipping_logic)

# response in calculateShipping
content = content.replace(
"""            'total' => $subtotal + $shippingCost,
            'total_formatted' => 'Rp ' . number_format($subtotal + $shippingCost, 0, ',', '.'),""",
"""            'discount_amount' => $discountAmount,
            'discount_formatted' => '- Rp ' . number_format($discountAmount, 0, ',', '.'),
            'total' => $totalAfterDiscount + $shippingCost,
            'total_formatted' => 'Rp ' . number_format($totalAfterDiscount + $shippingCost, 0, ',', '.'),""")

# 6. store() method
store_logic = """            $discountPercent = $user->priceLevel ? $user->priceLevel->discount_percentage : 0;
            $discountAmount = ($subtotal * $discountPercent) / 100;
            $totalAfterDiscount = $subtotal - $discountAmount;

            $totalWeight = $carts->sum(function ($cart) {"""

content = content.replace(
"""            $totalWeight = $carts->sum(function ($cart) {""", store_logic, 1)

# Replace $total = $subtotal + $shippingCost;
content = content.replace(
"""            $total = $subtotal + $shippingCost;""",
"""            $total = $totalAfterDiscount + $shippingCost;""",
    1
)

# Add to Order::create array
content = re.sub(
    r"('subtotal' => \$subtotal,)",
    r"\1\n                'discount_percent' => $discountPercent,\n                'discount_amount' => $discountAmount,",
    content
)

# In foreach ($carts as $cart)
content = content.replace(
"""                $productPrice = $user->getProductPrice($cart->product);""",
"""                $productPrice = $cart->product->price;""")

with open('app/Http/Controllers/Distributor/OrderController.php', 'w') as f:
    f.write(content)

print("done")
