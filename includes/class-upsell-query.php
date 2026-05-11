<?php

if (!defined('ABSPATH')) {
    exit;
}

class ItchenKing_Upsell_Query {

    /**
     * Show 10 lowest priced products when any amount remains for free shipping.
     *
     * Example:
     * Free delivery threshold = £50
     * Cart total = £30
     * Remaining = £20
     *
     * Slider will show the 10 lowest priced in-stock visible products,
     * excluding products already in the cart.
     */
    public static function get_products($remaining, $exclude = []) {

        $remaining = (float) $remaining;
        $exclude   = array_values(array_unique(array_filter(array_map('absint', $exclude))));

        if ($remaining <= 0) {
            return [];
        }

        $products = wc_get_products([
            'status'             => 'publish',
            'limit'              => 20,
            'exclude'            => $exclude,
            'stock_status'       => 'instock',
            'type'               => ['simple', 'variable'],
            'catalog_visibility' => 'visible',
            'orderby'            => 'price',
            'order'              => 'ASC',
            'return'             => 'objects',
        ]);

        $filtered = [];

        foreach ($products as $product) {
            if (!$product || !$product->is_purchasable() || !$product->is_in_stock()) {
                continue;
            }

            $price = (float) $product->get_price();

            if ($price > 0) {
                $filtered[] = $product;
            }
        }

        usort($filtered, function ($a, $b) {
            return (float) $a->get_price() <=> (float) $b->get_price();
        });

        return array_slice($filtered, 0, 10);
    }
}