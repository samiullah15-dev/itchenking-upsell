(function ($) {
    'use strict';

    function initSwiper() {
        if (window.kitchenKingSwiper && typeof window.kitchenKingSwiper.destroy === 'function') {
            window.kitchenKingSwiper.destroy(true, true);
        }

        if ($('.itchenking-swiper').length && typeof Swiper !== 'undefined') {
            window.kitchenKingSwiper = new Swiper('.itchenking-swiper', {
                slidesPerView: 2,
                spaceBetween: 15,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                },
                breakpoints: {
                    768: {
                        slidesPerView: 3
                    },
                    1200: {
                        slidesPerView: 4
                    }
                }
            });
        }
    }

    function initVariations() {
        $('.itchenking-variation-form').each(function () {
            var form = $(this);

            if ($.fn.wc_variation_form) {
                form.wc_variation_form();
            }

            form.off('.itchenking');

            form.on('found_variation.itchenking', function (event, variation) {
                form.find('.variation_id').val(variation.variation_id);
                form.find('.itchenking-add-variable-product')
                    .removeClass('disabled')
                    .prop('disabled', false)
                    .text('Add to Cart');
            });

            form.on('reset_data.itchenking hide_variation.itchenking', function () {
                form.find('.variation_id').val(0);
                form.find('.itchenking-add-variable-product')
                    .addClass('disabled')
                    .prop('disabled', true)
                    .text('Choose Option');
            });

            form.find('select').off('change.itchenking').on('change.itchenking', function () {
                form.trigger('check_variations');
            });
        });
    }

    function initUpsell() {
        initSwiper();
        initVariations();
    }

    function collectAttributes(form) {
        var attributes = {};

        form.find('select[name^="attribute_"]').each(function () {
            var select = $(this);
            attributes[select.attr('name')] = select.val();
        });

        return attributes;
    }

    function refreshWooCommerceAreas() {
        $(document.body).trigger('wc_fragment_refresh');
        $(document.body).trigger('wc_update_cart');
        $(document.body).trigger('update_checkout');
        $(document.body).trigger('added_to_cart');
    }

    function replaceWidget(html) {
        if (!html) {
            return;
        }

        $('.itchenking-upsell-wrapper').first().replaceWith(html);
        initUpsell();
    }

    function ajaxAddToCart(payload, button, card) {
        button.addClass('loading').prop('disabled', true);
        card.find('.itchenking-card-message').text('');

        $.ajax({
            url: itchenking_ajax.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: $.extend({
                action: 'itchenking_add_to_cart',
                nonce: itchenking_ajax.nonce
            }, payload),
            success: function (response) {
                if (response && response.success) {
                    replaceWidget(response.data.widget);
                    refreshWooCommerceAreas();
                } else {
                    var message = response && response.data && response.data.message
                        ? response.data.message
                        : 'Product could not be added.';

                    card.find('.itchenking-card-message').text(message);
                    button.removeClass('loading').prop('disabled', false);
                }
            },
            error: function () {
                card.find('.itchenking-card-message').text('Something went wrong. Please try again.');
                button.removeClass('loading').prop('disabled', false);
            }
        });
    }

    $(document).on('click', '.itchenking-add-simple-product', function (event) {
        event.preventDefault();

        var button = $(this);
        var card = button.closest('.itchenking-product-card');

        ajaxAddToCart({
            product_id: button.data('product_id'),
            quantity: button.data('quantity') || 1,
            variation_id: 0,
            attributes: {}
        }, button, card);
    });

    $(document).on('submit', '.itchenking-variation-form', function (event) {
        event.preventDefault();

        var form = $(this);
        var button = form.find('.itchenking-add-variable-product');
        var card = form.closest('.itchenking-product-card');
        var variationId = parseInt(form.find('.variation_id').val(), 10) || 0;

        if (!variationId) {
            card.find('.itchenking-card-message').text('Please select product options.');
            return;
        }

        ajaxAddToCart({
            product_id: form.data('product_id'),
            quantity: form.find('input[name="quantity"]').val() || 1,
            variation_id: variationId,
            attributes: collectAttributes(form)
        }, button, card);
    });

    $(document).ready(function () {
        initUpsell();
    });

    $(document.body).on('updated_checkout updated_wc_div', function () {
        initUpsell();
    });

})(jQuery);
