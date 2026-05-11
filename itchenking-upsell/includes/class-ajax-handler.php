<?php

if (!defined('ABSPATH')) {
    exit;
}

class ItchenKing_Ajax_Handler {

    public function __construct() {
        add_action('wp_ajax_itchenking_refresh', [$this, 'refresh']);
        add_action('wp_ajax_nopriv_itchenking_refresh', [$this, 'refresh']);

        add_action('wp_ajax_itchenking_add_to_cart', [$this, 'add_to_cart']);
        add_action('wp_ajax_nopriv_itchenking_add_to_cart', [$this, 'add_to_cart']);
    }

    /**
     * Return refreshed widget HTML.
     */
    public function refresh() {
        ob_start();
        include ITCHENKING_PATH . 'templates/widget.php';
        echo ob_get_clean();
        wp_die();
    }

    /**
     * Custom AJAX add to cart for simple and variable products.
     */
    public function add_to_cart() {

        check_ajax_referer('itchenking_nonce', 'nonce');

        if (!WC()->cart) {
            wp_send_json_error([
                'message' => __('Cart is not available.', 'itchenking-upsell'),
            ]);
        }

        $product_id   = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
        $quantity     = isset($_POST['quantity']) ? max(1, absint($_POST['quantity'])) : 1;
        $variation    = [];

        if (!$product_id) {
            wp_send_json_error([
                'message' => __('Invalid product.', 'itchenking-upsell'),
            ]);
        }

        $product = wc_get_product($product_id);

        if (!$product || !$product->is_purchasable()) {
            wp_send_json_error([
                'message' => __('This product is not purchasable.', 'itchenking-upsell'),
            ]);
        }

        if (!$product->is_in_stock()) {
            wp_send_json_error([
                'message' => __('This product is out of stock.', 'itchenking-upsell'),
            ]);
        }

        if (!empty($_POST['attributes']) && is_array($_POST['attributes'])) {
            $raw_attributes = wp_unslash($_POST['attributes']);

            foreach ($raw_attributes as $key => $value) {
                $attribute_key   = sanitize_title(wp_unslash($key));
                $attribute_value = wc_clean(wp_unslash($value));

                if ($attribute_key && $attribute_value !== '') {
                    // WooCommerce expects keys like attribute_pa_size.
                    if (strpos($attribute_key, 'attribute_') !== 0) {
                        $attribute_key = 'attribute_' . $attribute_key;
                    }

                    $variation[$attribute_key] = $attribute_value;
                }
            }
        }

        if ($product->is_type('variable')) {

            if (empty($variation)) {
                wp_send_json_error([
                    'message' => __('Please select product options.', 'itchenking-upsell'),
                ]);
            }

            // If JS did not send a variation ID, find it from selected attributes.
            if (!$variation_id) {
                $data_store   = WC_Data_Store::load('product');
                $variation_id = $data_store->find_matching_product_variation($product, $variation);
            }

            if (!$variation_id) {
                wp_send_json_error([
                    'message' => __('Please choose a valid product variation.', 'itchenking-upsell'),
                ]);
            }

            $variation_product = wc_get_product($variation_id);

            if (!$variation_product || !$variation_product->is_purchasable() || !$variation_product->is_in_stock()) {
                wp_send_json_error([
                    'message' => __('Selected variation is not available.', 'itchenking-upsell'),
                ]);
            }
        }

        wc_clear_notices();

        $added = WC()->cart->add_to_cart(
            $product_id,
            $quantity,
            $variation_id,
            $variation
        );

        if (!$added) {
            $notices = wc_get_notices('error');
            $message = __('Product could not be added to cart.', 'itchenking-upsell');

            if (!empty($notices[0]['notice'])) {
                $message = wp_strip_all_tags($notices[0]['notice']);
            }

            wp_send_json_error([
                'message' => $message,
            ]);
        }

        WC()->cart->calculate_totals();

        ob_start();
        include ITCHENKING_PATH . 'templates/widget.php';
        $widget_html = ob_get_clean();

        wp_send_json_success([
            'message'    => __('Product added successfully.', 'itchenking-upsell'),
            'widget'     => $widget_html,
            'cart_count' => WC()->cart->get_cart_contents_count(),
        ]);
    }
}
