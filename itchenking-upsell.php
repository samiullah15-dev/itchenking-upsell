<?php
/**
 * Plugin Name: ItchenKing Upsell Free Shipping
 * Description: Cart upsell + free shipping + variable products + swiper
 * Version: 1.0
 */

if (!defined('ABSPATH')) exit;

define('ITCHENKING_PATH', plugin_dir_path(__FILE__));
define('ITCHENKING_URL', plugin_dir_url(__FILE__));

require_once ITCHENKING_PATH . 'includes/class-free-shipping.php';
require_once ITCHENKING_PATH . 'includes/class-upsell-query.php';
require_once ITCHENKING_PATH . 'includes/class-ajax-handler.php';

/* ===================== ASSETS ===================== */
add_action('wp_enqueue_scripts', function () {

    if (!is_cart() && !is_checkout()) return;

    wp_enqueue_style(
        'swiper-css',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css'
    );

    wp_enqueue_style(
        'itchenking-css',
        ITCHENKING_URL . 'assets/css/upsell.css'
    );

    wp_enqueue_script(
        'swiper-js',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
        ['jquery'],
        null,
        true
    );

    // ✅ REQUIRED FOR VARIABLE PRODUCTS
    wp_enqueue_script('wc-add-to-cart-variation');

    // optional but good for AJAX cart updates
    wp_enqueue_script('wc-cart-fragments');

    wp_enqueue_script(
        'itchenking-js',
        ITCHENKING_URL . 'assets/js/upsell.js',
        ['jquery', 'swiper-js', 'wc-add-to-cart-variation'],
        null,
        true
    );

    wp_localize_script('itchenking-js', 'itchenking_ajax', [
        'ajaxurl' => admin_url('admin-ajax.php')
    ]);
});


/* ===================== WIDGET ===================== */
function itchenking_render_widget() {
    include ITCHENKING_PATH . 'templates/widget.php';
}

add_action('woocommerce_before_cart', 'itchenking_render_widget');
add_action('woocommerce_before_checkout_form', 'itchenking_render_widget');


/* ===================== AJAX REFRESH ===================== */
add_action('wp_ajax_itchenking_refresh', 'itchenking_refresh');
add_action('wp_ajax_nopriv_itchenking_refresh', 'itchenking_refresh');

function itchenking_refresh() {
    include ITCHENKING_PATH . 'templates/widget.php';
    wp_die();
}