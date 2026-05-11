<?php

if (!defined('ABSPATH')) {
    exit;
}

class ItchenKing_Upsell_Query {

    /**
     * Get products close to the remaining amount needed for free shipping.
     *
     * Example:
     * Free delivery threshold = £50
     * Cart total = £30
     * Remaining = £20
     *
     * Product priority:
     * 1. £20 to £30
     * 2. £15 to £30
     * 3. £10 to £30
     *
     * It will NOT show expensive products like £50, £70, £120 unless your remaining amount is close to that.
     */
    public static function get_products($remaining, $exclude = []) {

        $remaining = (float) $remaining;
        $exclude   = array_values(array_unique(array_filter(array_map('absint', $exclude))));

        if ($remaining <= 0) {
            return [];
        }

        /*
         * Main max range.
         * If remaining is £20, never show products above £30.
         */
        $max_price = $remaining + 10;

        /*
         * First try exact unlock range:
         * Remaining £20 = show £20 to £30.
         */
        $products = self::query_products($remaining, $max_price, $exclude);

        /*
         * Fallback 1:
         * If no product found, include slightly cheaper products:
         * Remaining £20 = show £15 to £30.
         */
        if (empty($products)) {
            $products = self::query_products(max(1, $remaining - 5), $max_price, $exclude);
        }

        /*
         * Fallback 2:
         * If still empty, include more cheaper products:
         * Remaining £20 = show £10 to £30.
         */
        if (empty($products)) {
            $products = self::query_products(max(1, $remaining - 10), $max_price, $exclude);
        }

        return $products;
    }

    private static function query_products($min_price, $max_price, $exclude = []) {

        $products = wc_get_products([
            'status'             => 'publish',
            'limit'              => 24,
            'exclude'            => $exclude,
            'stock_status'       => 'instock',
            'type'               => ['simple', 'variable'],
            'catalog_visibility' => 'visible',
            'orderby'            => 'price',
            'order'              => 'ASC',
            'return'             => 'objects',
            'meta_query'         => [
                [
                    'key'     => '_price',
                    'value'   => [(float) $min_price, (float) $max_price],
                    'compare' => 'BETWEEN',
                    'type'    => 'DECIMAL(10,2)',
                ],
            ],
        ]);

        /*
         * Extra safety filter.
         * This prevents WooCommerce/theme/query conflicts from showing expensive products.
         */
        $filtered = [];

        foreach ($products as $product) {
            if (!$product || !$product->is_purchasable() || !$product->is_in_stock()) {
                continue;
            }

            $price = (float) $product->get_price();

            if ($price >= $min_price && $price <= $max_price) {
                $filtered[] = $product;
            }
        }

        /*
         * Sort by lowest price first.
         */
        usort($filtered, function ($a, $b) {
            return (float) $a->get_price() <=> (float) $b->get_price();
        });

        return array_slice($filtered, 0, 12);
    }
}