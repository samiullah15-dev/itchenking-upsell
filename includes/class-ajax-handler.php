<?php

class ItchenKing_Ajax_Handler {

    public function __construct() {
        add_action('wp_ajax_itchenking_refresh', [$this, 'refresh']);
        add_action('wp_ajax_nopriv_itchenking_refresh', [$this, 'refresh']);
    }

    public function refresh() {

        ob_start();
        include ITCHENKING_PATH . 'templates/widget.php';
        echo ob_get_clean();

        wp_die();
    }
}

new ItchenKing_Ajax_Handler();