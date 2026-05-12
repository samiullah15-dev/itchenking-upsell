<?php

if (!defined('ABSPATH')) {
    exit;
}

class ItchenKing_Upsell_Query {

    /**
     * If manual products are selected in admin, show them.
     * Otherwise show 10 lowest priced WooCommerce products.
     */
    public static function get_products($remaining, $exclude = []) {

        $remaining = (float) $remaining;
        $exclude   = array_values(array_unique(array_filter(array_map('absint', $exclude))));

        if ($remaining <= 0) {
            return [];
        }

        $manual_products = self::get_manual_products($exclude);

        if (!empty($manual_products)) {
            return $manual_products;
        }

        return self::get_lowest_price_products($exclude);
    }

    private static function get_manual_products($exclude = []) {

        if (!class_exists('ItchenKing_Admin_Settings')) {
            return [];
        }

        $settings = ItchenKing_Admin_Settings::get_settings();

        if (empty($settings['manual_product_ids']) || !is_array($settings['manual_product_ids'])) {
            return [];
        }

        $products = [];

        foreach ($settings['manual_product_ids'] as $product_id) {
            $product_id = absint($product_id);

            if (!$product_id || in_array($product_id, $exclude, true)) {
                continue;
            }

            $product = wc_get_product($product_id);

            if (!$product || !$product->is_purchasable() || !$product->is_in_stock()) {
                continue;
            }

            if (!$product->is_type(['simple', 'variable'])) {
                continue;
            }

            $products[] = $product;
        }

        return array_slice($products, 0, 10);
    }

    private static function get_lowest_price_products($exclude = []) {

        $products = wc_get_products([
            'status'             => 'publish',
            'limit'              => 50,
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

            if ($price <= 0) {
                continue;
            }

            $filtered[] = $product;
        }

        usort($filtered, function ($a, $b) {
            return (float) $a->get_price() <=> (float) $b->get_price();
        });

        return array_slice($filtered, 0, 10);
    }
}