<?php

if (!defined('ABSPATH')) {
    exit;
}

class ItchenKing_Admin_Settings {

    const OPTION_NAME = 'itchenking_upsell_settings';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        add_action('wp_ajax_itchenking_product_search', [$this, 'ajax_product_search']);
    }

    public static function defaults() {
        return [
            'manual_product_ids' => [],

            'button_bg'          => '#242e42',
            'button_text'        => '#ffffff',
            'button_hover_bg'    => '#193366',

            'arrow_color'        => '#242e42',
            'progress_color'     => '#193366',

            'message_color'      => '#242e42',
            'highlight_color'    => '#193366',

            'title_color'        => '#242e42',
            'price_color'        => '#242e42',

            'font_family'        => 'inherit',
            'title_font_size'    => '15',
            'price_font_size'    => '15',
            'button_font_size'   => '14',
        ];
    }

    public static function get_settings() {
        $saved = get_option(self::OPTION_NAME, []);
        return wp_parse_args($saved, self::defaults());
    }

    public function add_menu_page() {
        add_submenu_page(
            'woocommerce',
            __('ItchenKing Upsell', 'itchenking-upsell'),
            __('ItchenKing Upsell', 'itchenking-upsell'),
            'manage_woocommerce',
            'itchenking-upsell',
            [$this, 'render_settings_page']
        );
    }

    public function enqueue_admin_assets($hook) {
    if ($hook !== 'woocommerce_page_itchenking-upsell') {
        return;
    }

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');

    /*
     * Load WooCommerce SelectWoo properly.
     * This fixes broken product search UI.
     */
    if (defined('WC_VERSION') && function_exists('WC')) {
        wp_enqueue_script(
            'selectWoo',
            WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full.min.js',
            ['jquery'],
            WC_VERSION,
            true
        );

        wp_enqueue_style(
            'select2',
            WC()->plugin_url() . '/assets/css/select2.css',
            [],
            WC_VERSION
        );
    }

    wp_enqueue_style(
        'itchenking-admin-settings',
        ITCHENKING_URL . 'assets/css/admin-settings.css',
        ['wp-color-picker'],
        ITCHENKING_VERSION
    );

    wp_enqueue_script(
        'itchenking-admin-settings',
        ITCHENKING_URL . 'assets/js/admin-settings.js',
        ['jquery', 'wp-color-picker', 'selectWoo'],
        ITCHENKING_VERSION,
        true
    );

    wp_localize_script('itchenking-admin-settings', 'itchenking_admin', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('itchenking_admin_nonce'),
    ]);
}

    public function render_settings_page() {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        if (
            isset($_POST['itchenking_settings_nonce']) &&
            wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['itchenking_settings_nonce'])), 'itchenking_save_settings')
        ) {
            $this->save_settings();
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully.</p></div>';
        }

        $settings = self::get_settings();
        $manual_product_ids = !empty($settings['manual_product_ids']) ? array_map('absint', $settings['manual_product_ids']) : [];
        ?>

        <div class="wrap">
            <h1><?php esc_html_e('ItchenKing Upsell Settings', 'itchenking-upsell'); ?></h1>

            <form method="post">
                <?php wp_nonce_field('itchenking_save_settings', 'itchenking_settings_nonce'); ?>

                <h2><?php esc_html_e('Manual Slider Products', 'itchenking-upsell'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <?php esc_html_e('Select Products', 'itchenking-upsell'); ?>
                        </th>
                        <td>
                            <select
                                id="itchenking_manual_products"
                                name="itchenking_settings[manual_product_ids][]"
                                multiple="multiple"
                                style="width: 600px; max-width: 100%;"
                            >
                                <?php foreach ($manual_product_ids as $product_id) : ?>
                                    <?php
                                    $product = wc_get_product($product_id);
                                    if (!$product) {
                                        continue;
                                    }
                                    ?>
                                    <option value="<?php echo esc_attr($product_id); ?>" selected>
                                        <?php echo esc_html($product->get_name() . ' — ' . wp_strip_all_tags($product->get_price_html())); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <p class="description">
                                Select products manually. These products will be shown in the slider first, in the same selected order.
                            </p>
                        </td>
                    </tr>
                </table>

                <hr>

                <h2><?php esc_html_e('Colors', 'itchenking-upsell'); ?></h2>

                <table class="form-table">

                    <?php $this->color_field('button_bg', 'Button Background', $settings); ?>
                    <?php $this->color_field('button_text', 'Button Text Color', $settings); ?>
                    <?php $this->color_field('button_hover_bg', 'Button Hover Background', $settings); ?>
                    <?php $this->color_field('arrow_color', 'Slider Arrow Color', $settings); ?>
                    <?php $this->color_field('progress_color', 'Progress Bar Color', $settings); ?>
                    <?php $this->color_field('message_color', 'Main Text Color', $settings); ?>
                    <?php $this->color_field('highlight_color', 'Highlighted Amount Color', $settings); ?>
                    <?php $this->color_field('title_color', 'Product Title Color', $settings); ?>
                    <?php $this->color_field('price_color', 'Product Price Color', $settings); ?>

                </table>

                <hr>

                <h2><?php esc_html_e('Typography', 'itchenking-upsell'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">Font Family</th>
                        <td>
                            <select name="itchenking_settings[font_family]">
                                <?php
                                $fonts = [
                                    'inherit' => 'Theme Default',
                                    'Arial, sans-serif' => 'Arial',
                                    'Helvetica, Arial, sans-serif' => 'Helvetica',
                                    'Georgia, serif' => 'Georgia',
                                    'Tahoma, sans-serif' => 'Tahoma',
                                    'Verdana, sans-serif' => 'Verdana',
                                    '"Times New Roman", serif' => 'Times New Roman',
                                ];

                                foreach ($fonts as $value => $label) :
                                    ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['font_family'], $value); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <?php $this->number_field('title_font_size', 'Product Title Font Size', $settings, 'px'); ?>
                    <?php $this->number_field('price_font_size', 'Product Price Font Size', $settings, 'px'); ?>
                    <?php $this->number_field('button_font_size', 'Button Font Size', $settings, 'px'); ?>
                </table>

                <?php submit_button(__('Save Settings', 'itchenking-upsell')); ?>
            </form>
        </div>

        <?php
    }

    private function color_field($key, $label, $settings) {
        ?>
        <tr>
            <th scope="row"><?php echo esc_html($label); ?></th>
            <td>
                <input
                    type="text"
                    class="itchenking-color-field"
                    name="itchenking_settings[<?php echo esc_attr($key); ?>]"
                    value="<?php echo esc_attr($settings[$key]); ?>"
                >
            </td>
        </tr>
        <?php
    }

    private function number_field($key, $label, $settings, $suffix = '') {
        ?>
        <tr>
            <th scope="row"><?php echo esc_html($label); ?></th>
            <td>
                <input
                    type="number"
                    min="10"
                    max="40"
                    name="itchenking_settings[<?php echo esc_attr($key); ?>]"
                    value="<?php echo esc_attr($settings[$key]); ?>"
                    style="width: 90px;"
                >
                <?php echo esc_html($suffix); ?>
            </td>
        </tr>
        <?php
    }

    private function save_settings() {
        $defaults = self::defaults();
        $input = isset($_POST['itchenking_settings']) && is_array($_POST['itchenking_settings'])
            ? wp_unslash($_POST['itchenking_settings'])
            : [];

        $settings = [];

        $settings['manual_product_ids'] = [];

        if (!empty($input['manual_product_ids']) && is_array($input['manual_product_ids'])) {
            $settings['manual_product_ids'] = array_values(array_unique(array_map('absint', $input['manual_product_ids'])));
        }

        $color_keys = [
            'button_bg',
            'button_text',
            'button_hover_bg',
            'arrow_color',
            'progress_color',
            'message_color',
            'highlight_color',
            'title_color',
            'price_color',
        ];

        foreach ($color_keys as $key) {
            $settings[$key] = isset($input[$key]) ? sanitize_hex_color($input[$key]) : $defaults[$key];

            if (!$settings[$key]) {
                $settings[$key] = $defaults[$key];
            }
        }

        $allowed_fonts = [
            'inherit',
            'Arial, sans-serif',
            'Helvetica, Arial, sans-serif',
            'Georgia, serif',
            'Tahoma, sans-serif',
            'Verdana, sans-serif',
            '"Times New Roman", serif',
        ];

        $settings['font_family'] = isset($input['font_family']) && in_array($input['font_family'], $allowed_fonts, true)
            ? sanitize_text_field($input['font_family'])
            : 'inherit';

        $settings['title_font_size']  = isset($input['title_font_size']) ? absint($input['title_font_size']) : $defaults['title_font_size'];
        $settings['price_font_size']  = isset($input['price_font_size']) ? absint($input['price_font_size']) : $defaults['price_font_size'];
        $settings['button_font_size'] = isset($input['button_font_size']) ? absint($input['button_font_size']) : $defaults['button_font_size'];

        update_option(self::OPTION_NAME, $settings);
    }

    public function ajax_product_search() {
        check_ajax_referer('itchenking_admin_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error();
        }

        $term = isset($_GET['term']) ? sanitize_text_field(wp_unslash($_GET['term'])) : '';

        $products = wc_get_products([
            'status' => 'publish',
            'limit'  => 20,
            's'      => $term,
            'type'   => ['simple', 'variable'],
            'return' => 'objects',
        ]);

        $results = [];

        foreach ($products as $product) {
            if (!$product || !$product->is_purchasable()) {
                continue;
            }

            $image_id = $product->get_image_id();
            $image = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : wc_placeholder_img_src();

            $results[] = [
                'id'    => $product->get_id(),
                'text'  => $product->get_name() . ' — ' . wp_strip_all_tags($product->get_price_html()),
                'image' => $image,
                'price' => wp_strip_all_tags($product->get_price_html()),
            ];
        }

        wp_send_json([
            'results' => $results,
        ]);
    }
}