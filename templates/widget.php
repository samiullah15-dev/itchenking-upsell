<?php

$data = ItchenKing_Free_Shipping::get_data();

if ($data['unlocked']) {
    echo '<div class="itchenking-success-box">🎉 Free Delivery Unlocked!</div>';
    return;
}

/**
 * EXCLUDE CART ITEMS
 */
$exclude = [];

if (WC()->cart) {
    foreach (WC()->cart->get_cart() as $item) {
        $exclude[] = $item['product_id'];

        if (!empty($item['variation_id'])) {
            $exclude[] = $item['variation_id'];
        }
    }
}

/**
 * GET PRODUCTS
 */
$products = ItchenKing_Upsell_Query::get_products(
    $data['remaining'],
    $exclude
);

/**
 * FALLBACK PRODUCTS
 */
if (empty($products)) {
    $products = wc_get_products([
        'limit'        => 8,
        'status'       => 'publish',
        'stock_status' => 'instock',
        'orderby'      => 'rand',
    ]);
}

/**
 * PROGRESS BAR
 */
$progress = ($data['threshold'] > 0)
    ? min(100, ($data['cart_total'] / $data['threshold']) * 100)
    : 0;

?>

<div class="itchenking-upsell-wrapper">

    <!-- HEADER -->
    <div class="itchenking-header">

        <div class="itchenking-message">
            You're only
            <strong>Rs <?php echo number_format($data['remaining'], 0); ?></strong>
            away from <strong>FREE Delivery!</strong>
        </div>

        <div class="itchenking-progress">
            <div class="itchenking-progress-fill"
                 style="width: <?php echo esc_attr($progress); ?>%">
            </div>
        </div>

    </div>

    <!-- SWIPER -->
    <div class="swiper itchenking-swiper">
        <div class="swiper-wrapper">

            <?php foreach ($products as $product): ?>

                <?php
                if (!$product || !$product->is_purchasable() || !$product->is_in_stock()) {
                    continue;
                }

                $product_id = $product->get_id();
                $img = get_the_post_thumbnail_url($product_id, 'woocommerce_thumbnail');

                if (!$img) {
                    $img = wc_placeholder_img_src();
                }
                ?>

                <div class="swiper-slide">

                    <div class="itchenking-product-card">

                        <!-- IMAGE -->
                        <img src="<?php echo esc_url($img); ?>">

                        <!-- TITLE -->
                        <h4><?php echo esc_html($product->get_name()); ?></h4>

                        <!-- PRICE -->
                        <div class="itchenking-price">
                            <?php echo $product->get_price_html(); ?>
                        </div>

                        <!-- SIMPLE PRODUCT -->
                        <?php if ($product->is_type('simple')) : ?>

                            <a href="<?php echo esc_url($product->add_to_cart_url()); ?>"
                               data-product_id="<?php echo esc_attr($product_id); ?>"
                               data-quantity="1"
                               class="button add_to_cart_button ajax_add_to_cart">

                                Add to Cart

                            </a>

                        <!-- VARIABLE PRODUCT -->
                        <?php elseif ($product->is_type('variable')) : ?>

                            <form class="variations_form cart itchenking-variation-form"
                                  data-product_id="<?php echo esc_attr($product_id); ?>"
                                  data-product_variations='<?php echo wp_json_encode($product->get_available_variations()); ?>'>

                                <?php foreach ($product->get_variation_attributes() as $attribute_name => $options) : ?>

                                    <select name="attribute_<?php echo esc_attr(sanitize_title($attribute_name)); ?>">

                                        <option value="">
                                            Choose <?php echo wc_attribute_label($attribute_name); ?>
                                        </option>

                                        <?php foreach ($options as $option) : ?>
                                            <option value="<?php echo esc_attr($option); ?>">
                                                <?php echo esc_html($option); ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>

                                <?php endforeach; ?>

                                <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>">
                                <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
                                <input type="hidden" name="variation_id" class="variation_id" value="0">

                                <button type="submit"
                                        class="button single_add_to_cart_button disabled"
                                        disabled>

                                    Add to Cart

                                </button>

                            </form>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

        <!-- NAVIGATION -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>

    </div>

</div>