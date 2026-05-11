<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!WC()->cart) {
    return;
}

$data = ItchenKing_Free_Shipping::get_data();

$exclude = [];
foreach (WC()->cart->get_cart() as $item) {
    if (!empty($item['product_id'])) {
        $exclude[] = absint($item['product_id']);
    }
}

$products = [];
if (!$data['unlocked']) {
    $products = ItchenKing_Upsell_Query::get_products($data['remaining'], $exclude);
}
?>

<div class="itchenking-upsell-wrapper">

    <?php if ($data['unlocked']) : ?>

        <div class="itchenking-success-box">
            🎉 <strong><?php esc_html_e('Free Delivery Unlocked!', 'itchenking-upsell'); ?></strong>
            <span><?php esc_html_e('Your order now qualifies for free delivery.', 'itchenking-upsell'); ?></span>
        </div>

    <?php else : ?>

        <div class="itchenking-header">
            <div class="itchenking-message">
                <?php esc_html_e("You're only", 'itchenking-upsell'); ?>
                <strong><?php echo wp_kses_post(wc_price($data['remaining'])); ?></strong>
                <?php esc_html_e('away from', 'itchenking-upsell'); ?>
                <strong><?php esc_html_e('FREE Delivery!', 'itchenking-upsell'); ?></strong>
            </div>

            <div class="itchenking-sub-message">
                <?php esc_html_e('Add one of these items to your cart and move closer to free delivery.', 'itchenking-upsell'); ?>
            </div>

            <div class="itchenking-progress" aria-label="<?php esc_attr_e('Free delivery progress', 'itchenking-upsell'); ?>">
                <div class="itchenking-progress-fill" style="width: <?php echo esc_attr($data['progress']); ?>%"></div>
            </div>
        </div>

        <?php if (!empty($products)) : ?>

            <div class="swiper itchenking-swiper">
                <div class="swiper-wrapper">

                    <?php foreach ($products as $product) : ?>

                        <?php
                        if (!$product || !$product->is_purchasable() || !$product->is_in_stock()) {
                            continue;
                        }

                        $product_id = $product->get_id();
                        $image_id   = $product->get_image_id();
                        $image_html = $image_id
                            ? wp_get_attachment_image($image_id, 'woocommerce_thumbnail', false, ['class' => 'itchenking-product-image'])
                            : wc_placeholder_img('woocommerce_thumbnail');
                        ?>

                        <div class="swiper-slide">
                            <div class="itchenking-product-card" data-product_id="<?php echo esc_attr($product_id); ?>">

                                <div class="itchenking-product-image-wrap">
                                    <?php echo wp_kses_post($image_html); ?>
                                </div>

                                <h4 class="itchenking-product-title">
                                    <?php echo esc_html($product->get_name()); ?>
                                </h4>

                                <div class="itchenking-price">
                                    <?php echo wp_kses_post($product->get_price_html()); ?>
                                </div>

                                <?php if ($product->is_type('simple')) : ?>

                                    <button type="button"
                                            class="button itchenking-add-simple-product"
                                            data-product_id="<?php echo esc_attr($product_id); ?>"
                                            data-quantity="1">
                                        <?php esc_html_e('Add to Cart', 'itchenking-upsell'); ?>
                                    </button>

<?php elseif ($product->is_type('variable')) : ?>

    <?php
    $available_variations = $product->get_available_variations();
    $variation_attributes = $product->get_variation_attributes();
    ?>

    <?php if (!empty($available_variations)) : ?>

        <form class="variations_form cart itchenking-variable-form"
              data-product_id="<?php echo esc_attr($product_id); ?>"
              data-product_variations="<?php echo esc_attr(wp_json_encode($available_variations)); ?>">

            <div class="itchenking-variation-fields">

                <?php foreach ($variation_attributes as $attribute_name => $options) : ?>
                    <div class="itchenking-variation-field">
                        <label>
                            <?php echo esc_html(wc_attribute_label($attribute_name)); ?>
                        </label>

                        <?php
                        wc_dropdown_variation_attribute_options([
                            'options'          => $options,
                            'attribute'        => $attribute_name,
                            'product'          => $product,
                            'class'            => 'itchenking-variation-select',
                            'show_option_none' => 'Choose ' . wc_attribute_label($attribute_name),
                        ]);
                        ?>
                    </div>
                <?php endforeach; ?>

            </div>

            <input type="hidden" name="variation_id" class="variation_id" value="0">

            <button type="button"
                    class="button itchenking-add-variable-product disabled"
                    data-product_id="<?php echo esc_attr($product_id); ?>"
                    disabled>
                <?php esc_html_e('Choose Option', 'itchenking-upsell'); ?>
            </button>

        </form>

    <?php endif; ?>

<?php endif; ?>

                                <div class="itchenking-card-message" aria-live="polite"></div>

                            </div>
                        </div>

                    <?php endforeach; ?>

                </div>

                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>

        <?php else : ?>

            <div class="itchenking-empty-box">
                <?php esc_html_e('No recommended products are available right now.', 'itchenking-upsell'); ?>
            </div>

        <?php endif; ?>

    <?php endif; ?>

</div>
