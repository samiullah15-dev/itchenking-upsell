<?php

class ItchenKing_Free_Shipping {

    public static function get_data() {

        $threshold = 50;

        $cart_total = WC()->cart->get_subtotal();
        $remaining  = $threshold - $cart_total;

        return [
            'threshold' => $threshold,
            'cart_total' => $cart_total,
            'remaining' => $remaining,
            'unlocked' => $remaining <= 0
        ];
    }
}