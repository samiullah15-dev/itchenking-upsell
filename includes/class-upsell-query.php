<?php

if (!defined('ABSPATH')) {
    exit;
}

class ItchenKing_Upsell_Query {

    /**
     * Get products near the remaining amount needed for free shipping.
     */
    public static function get_products($remaining, $exclude = []) {

        $remaining = (float) $remaining;
        $exclude   = array_values(array_unique(array_filter(array_map('absint', $exclude))));

        $min_price = max(0, $remaining - 5);
        $max_price = max(10, $remaining + 20);

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

        // Fallback: if no products match the price range, show recent in-stock products.
        if (empty($products)) {
            $products = wc_get_products([
                'status'             => 'publish',
                'limit'              => 12,
                'exclude'            => $exclude,
                'stock_status'       => 'instock',
                'type'               => ['simple', 'variable'],
                'catalog_visibility' => 'visible',
                'orderby'            => 'date',
                'order'              => 'DESC',
                'return'             => 'objects',
            ]);
        }

        return $products;
    }
}
