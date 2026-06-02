<?php

if (!defined('ABSPATH')) {
    exit;
}

class ItchenKing_Free_Shipping {

    /**
     * Return free shipping threshold, cart subtotal, remaining amount, and progress status.
     */
    public static function get_data() {

        $threshold  = self::get_threshold();
        $cart_total = self::get_cart_total_for_threshold();
        $remaining  = max(0, $threshold - $cart_total);
        $progress   = $threshold > 0 ? min(100, ($cart_total / $threshold) * 100) : 100;

        return [
            'threshold'  => $threshold,
            'cart_total' => $cart_total,
            'remaining'  => $remaining,
            'progress'   => $progress,
            'unlocked'   => $threshold > 0 && $remaining <= 0,
        ];
    }

    /**
     * Try to read the minimum amount from enabled WooCommerce Free Shipping methods.
     * Falls back to 50 if no threshold is configured.
     */
    public static function get_threshold() {

        $fallback = 2000;
        $amounts  = [];

        if (class_exists('WC_Shipping_Zones')) {
            $zones = WC_Shipping_Zones::get_zones();

            // Also include the default/rest-of-world zone.
            $zones[] = [
                'zone_id' => 0,
            ];

            foreach ($zones as $zone_data) {
                $zone_id = isset($zone_data['zone_id']) ? absint($zone_data['zone_id']) : 0;
                $zone    = new WC_Shipping_Zone($zone_id);
                $methods = $zone->get_shipping_methods(true);

                foreach ($methods as $method) {
                    if (!isset($method->id) || $method->id !== 'free_shipping') {
                        continue;
                    }

                    if (isset($method->enabled) && $method->enabled !== 'yes') {
                        continue;
                    }

                    $min_amount = isset($method->min_amount) ? (float) $method->min_amount : 0;

                    if ($min_amount > 0) {
                        $amounts[] = $min_amount;
                    }
                }
            }
        }

        $threshold = !empty($amounts) ? min($amounts) : $fallback;

        return (float) apply_filters('itchenking_free_shipping_threshold', $threshold);
    }

    /**
     * Cart value used for progress calculation.
     */
    private static function get_cart_total_for_threshold() {

        if (!function_exists('WC') || !WC()->cart || !WC()->cart->get_cart()) {
    return 0;
}

        if (method_exists(WC()->cart, 'get_displayed_subtotal')) {
            $subtotal = (float) WC()->cart->get_cart_contents_total();
        } else {
            $subtotal = (float) WC()->cart->get_subtotal();
        }

        // Subtract discounts so the progress matches what customer is actually paying for products.
        $discount = (float) WC()->cart->get_discount_total();

        return max(0, (float) WC()->cart->get_cart_contents_total());
    }
}
