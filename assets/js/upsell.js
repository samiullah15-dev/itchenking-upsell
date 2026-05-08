function initUpsell() {

    if (window.kitchenKingSwiper) {
        window.kitchenKingSwiper.destroy(true, true);
    }

    if (document.querySelector(".itchenking-swiper")) {

        window.kitchenKingSwiper = new Swiper(".itchenking-swiper", {

            slidesPerView: 2,
            spaceBetween: 15,

            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev"
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

    // VARIABLE PRODUCTS
// VARIABLE PRODUCTS FIX
jQuery(function ($) {

    function initVariations() {

        $(".itchenking-variation-form").each(function () {

            let form = $(this);

            form.wc_variation_form();

            form.on("found_variation", function (e, variation) {

                form.find(".variation_id").val(variation.variation_id);

                form.find(".single_add_to_cart_button")
                    .removeClass("disabled")
                    .prop("disabled", false);

            });

            form.on("reset_data", function () {

                form.find(".variation_id").val(0);

                form.find(".single_add_to_cart_button")
                    .addClass("disabled")
                    .prop("disabled", true);

            });

            // trigger change detection
            form.find("select").on("change", function () {
                form.trigger("check_variations");
            });

        });
    }

    initVariations();

});
}

jQuery(function ($) {

    initUpsell();

    // AJAX REFRESH
    $(document.body).on("added_to_cart", function () {

        $.post(itchenking_ajax.ajaxurl, {

            action: "itchenking_refresh"

        }, function (html) {

            $(".itchenking-upsell-wrapper")
                .replaceWith(html);

            initUpsell();
        });

    });

});