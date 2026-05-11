<?php
/**
 * Plugin Name: ItchenKing Upsell Free Shipping
 * Plugin URI: https://github.com/your-username/itchenking-upsell
 * Description: WooCommerce free delivery upsell widget with progress bar, product slider, and AJAX add to cart for simple and variable products on cart and checkout pages.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: itchenking-upsell
 * Requires Plugins: woocommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ITCHENKING_VERSION', '1.0.1');
define('ITCHENKING_PATH', plugin_dir_path(__FILE__));
define('ITCHENKING_URL', plugin_dir_url(__FILE__));

/**
 * Stop plugin from loading if WooCommerce is not active.
 */
add_action('plugins_loaded', function () {

    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>ItchenKing Upsell Free Shipping</strong> requires WooCommerce to be active.</p></div>';
        });
        return;
    }

    require_once ITCHENKING_PATH . 'includes/class-free-shipping.php';
    require_once ITCHENKING_PATH . 'includes/class-upsell-query.php';
    require_once ITCHENKING_PATH . 'includes/class-ajax-handler.php';

    new ItchenKing_Ajax_Handler();
});

/**
 * Enqueue assets only on cart and checkout pages.
 */
add_action('wp_enqueue_scripts', function () {

    if (!class_exists('WooCommerce') || (!is_cart() && !is_checkout())) {
        return;
    }

    wp_enqueue_style(
        'swiper-css',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
        [],
        '11'
    );

    wp_enqueue_style(
        'itchenking-css',
        ITCHENKING_URL . 'assets/css/upsell.css',
        [],
        ITCHENKING_VERSION
    );

    wp_enqueue_script(
        'swiper-js',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
        ['jquery'],
        '11',
        true
    );

    // WooCommerce scripts needed for AJAX cart and variation matching.
    wp_enqueue_script('wc-add-to-cart');
    wp_enqueue_script('wc-add-to-cart-variation');
    wp_enqueue_script('wc-cart-fragments');

    wp_enqueue_script(
        'itchenking-js',
        ITCHENKING_URL . 'assets/js/upsell.js',
        ['jquery', 'swiper-js', 'wc-add-to-cart', 'wc-add-to-cart-variation'],
        '1.0.0',
        true
    );

    wp_localize_script('itchenking-js', 'itchenking_ajax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('itchenking_nonce'),
    ]);
});

/**
 * Render the widget on cart and checkout.
 */
function itchenking_render_widget() {
    if (!class_exists('WooCommerce') || !WC()->cart) {
        return;
    }

    include ITCHENKING_PATH . 'templates/widget.php';
}

add_action('woocommerce_before_cart', 'itchenking_render_widget', 15);
add_action('woocommerce_before_checkout_form', 'itchenking_render_widget', 15);
