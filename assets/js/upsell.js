jQuery(function ($) {

    function normalizeValue(value) {
        return String(value || "").trim();
    }

    function getProductVariations(form) {
        let variations = form.attr("data-product_variations");

        if (!variations) {
            return [];
        }

        try {
            return JSON.parse(variations);
        } catch (e) {
            return [];
        }
    }

    function getChosenAttributes(form) {
        let attributes = {};
        let count = 0;
        let chosen = 0;

        form.find("select[name^='attribute_']").each(function () {
            let name = $(this).attr("name");
            let value = normalizeValue($(this).val());

            count++;

            if (value) {
                chosen++;
                attributes[name] = value;
            }
        });

        return {
            count: count,
            chosen: chosen,
            attributes: attributes
        };
    }

    function findMatchingVariation(variations, selectedAttributes) {
        for (let i = 0; i < variations.length; i++) {
            let variation = variations[i];
            let matched = true;

            for (let attrName in variation.attributes) {
                let variationValue = normalizeValue(variation.attributes[attrName]);
                let selectedValue = normalizeValue(selectedAttributes[attrName]);

                if (variationValue !== "" && variationValue !== selectedValue) {
                    matched = false;
                    break;
                }
            }

            if (matched) {
                return variation;
            }
        }

        return false;
    }

    function enableVariableButton(form, variationId) {
        form.find(".variation_id").val(variationId);

        form.find(".itchenking-add-variable-product")
            .prop("disabled", false)
            .removeClass("disabled")
            .text("Add to Cart");
    }

    function disableVariableButton(form) {
        form.find(".variation_id").val(0);

        form.find(".itchenking-add-variable-product")
            .prop("disabled", true)
            .addClass("disabled")
            .text("Choose Option");
    }

    function checkVariableSelection(form) {
        let selected = getChosenAttributes(form);

        if (selected.count === 0 || selected.count !== selected.chosen) {
            disableVariableButton(form);
            return;
        }

        let variations = getProductVariations(form);
        let matchedVariation = findMatchingVariation(variations, selected.attributes);

        if (
            matchedVariation &&
            matchedVariation.variation_id &&
            matchedVariation.is_purchasable !== false &&
            matchedVariation.is_in_stock !== false
        ) {
            enableVariableButton(form, matchedVariation.variation_id);
        } else {
            disableVariableButton(form);
        }
    }

    function initUpsellSlider() {
        if (typeof Swiper !== "undefined" && $(".itchenking-swiper").length) {
            if (window.itchenkingSwiper) {
                window.itchenkingSwiper.destroy(true, true);
            }

            window.itchenkingSwiper = new Swiper(".itchenking-swiper", {
                slidesPerView: 4,
                slidesPerGroup: 1,
                spaceBetween: 15,
                watchOverflow: true,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev"
                },
                breakpoints: {
                    0: {
                        slidesPerView: 1
                    },
                    768: {
                        slidesPerView: 2
                    },
                    1024: {
                        slidesPerView: 4
                    }
                }
            });
        }
    }

    function initVariableForms() {
        $(".itchenking-variable-form").each(function () {
            let form = $(this);

            disableVariableButton(form);

            if (typeof form.wc_variation_form === "function") {
                form.wc_variation_form();
            }

            form.off(".itchenkingVariation");

            form.on("found_variation.itchenkingVariation", function (event, variation) {
                if (
                    variation &&
                    variation.variation_id &&
                    variation.is_purchasable !== false &&
                    variation.is_in_stock !== false
                ) {
                    enableVariableButton(form, variation.variation_id);
                } else {
                    checkVariableSelection(form);
                }
            });

            form.on("reset_data.itchenkingVariation hide_variation.itchenkingVariation", function () {
                checkVariableSelection(form);
            });

            form.find("select[name^='attribute_']")
                .off("change.itchenkingVariation")
                .on("change.itchenkingVariation", function () {
                    setTimeout(function () {
                        checkVariableSelection(form);
                    }, 150);
                });

            checkVariableSelection(form);
        });
    }

    function refreshAfterAdd(response) {
        if (response.success && response.data && response.data.widget) {
            $(".itchenking-upsell-wrapper").replaceWith(response.data.widget);
        }

        $(document.body).trigger("added_to_cart");
        $(document.body).trigger("wc_fragment_refresh");
        $(document.body).trigger("update_checkout");

        initUpsellSlider();
        initVariableForms();
    }

    function addProductToCart(data, button) {
        button.addClass("loading").prop("disabled", true);

        $.ajax({
            url: itchenking_ajax.ajaxurl,
            type: "POST",
            data: data,
            success: function (response) {
                if (response.success) {
                    refreshAfterAdd(response);
                } else {
                    alert(response.data && response.data.message ? response.data.message : "Could not add product.");
                    button.removeClass("loading").prop("disabled", false);
                }
            },
            error: function () {
                alert("AJAX error. Please try again.");
                button.removeClass("loading").prop("disabled", false);
            }
        });
    }

    $(document).on("click", ".itchenking-add-simple-product", function (e) {
        e.preventDefault();

        let button = $(this);

        addProductToCart({
            action: "itchenking_add_to_cart",
            nonce: itchenking_ajax.nonce,
            product_id: button.data("product_id"),
            quantity: button.data("quantity") || 1,
            variation_id: 0,
            attributes: {}
        }, button);
    });

    $(document).on("click", ".itchenking-add-variable-product", function (e) {
        e.preventDefault();

        let button = $(this);
        let form = button.closest(".itchenking-variable-form");

        checkVariableSelection(form);

        let selected = getChosenAttributes(form);
        let variationId = parseInt(form.find(".variation_id").val(), 10) || 0;

        if (selected.count !== selected.chosen || !variationId) {
            alert("Please select product options.");
            return;
        }

        addProductToCart({
            action: "itchenking_add_to_cart",
            nonce: itchenking_ajax.nonce,
            product_id: button.data("product_id"),
            quantity: 1,
            variation_id: variationId,
            attributes: selected.attributes
        }, button);
    });

    initUpsellSlider();
    initVariableForms();

});