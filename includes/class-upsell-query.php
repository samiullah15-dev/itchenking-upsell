<?php

if (!defined('ABSPATH')) {
    exit;
}

class ItchenKing_Upsell_Query {

    /**
     * Get products close to the remaining amount needed for free shipping.
     * Example:
     * Threshold £50, cart £40, remaining £10
     * Show products around £10 to £15.
     */
    public static function get_products($remaining, $exclude = []) {

        $remaining = (float) $remaining;
        $exclude   = array_values(array_unique(array_filter(array_map('absint', $exclude))));

        /*
         * Main target range:
         * If remaining is £10, show products from £10 to £15.
         */
        $min_price = max(1, $remaining);
        $max_price = max($remaining + 5, 5);

        $args = [
            'status'             => 'publish',
            'limit'              => 12,
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
                    'value'   => [$min_price, $max_price],
                    'compare' => 'BETWEEN',
                    'type'    => 'DECIMAL(10,2)',
                ],
            ],
        ];

        $products = wc_get_products($args);

        /*
         * Fallback 1:
         * If no product found between £10 and £15,
         * show products slightly higher, like £10 to £25.
         */
        if (empty($products)) {
            $products = wc_get_products([
                'status'             => 'publish',
                'limit'              => 12,
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
                        'value'   => [$remaining, $remaining + 15],
                        'compare' => 'BETWEEN',
                        'type'    => 'DECIMAL(10,2)',
                    ],
                ],
            ]);
        }

        /*
         * Fallback 2:
         * If still no product found, show nearest cheaper/low-price products.
         */
        if (empty($products)) {
            $products = wc_get_products([
                'status'             => 'publish',
                'limit'              => 12,
                'exclude'            => $exclude,
                'stock_status'       => 'instock',
                'type'               => ['simple', 'variable'],
                'catalog_visibility' => 'visible',
                'orderby'            => 'price',
                'order'              => 'ASC',
                'return'             => 'objects',
            ]);
        }

        return $products;
    }
}