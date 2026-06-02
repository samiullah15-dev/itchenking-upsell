jQuery(function ($) {

    $(".itchenking-color-field").wpColorPicker();

    function formatProduct(product) {
        if (product.loading) {
            return product.text;
        }

        let image = product.image ? product.image : "";
        let title = product.text ? product.text : "";
        let price = product.price ? product.price : "";

        return $(
            '<div class="itchenking-product-result">' +
                '<img src="' + image + '" alt="">' +
                '<div>' +
                    '<div class="itchenking-product-result-title">' + title + '</div>' +
                    '<div class="itchenking-product-result-price">' + price + '</div>' +
                '</div>' +
            '</div>'
        );
    }

    function formatSelection(product) {
        return product.text || product.id;
    }

    let productSelect = $("#itchenking_manual_products");

    if (productSelect.length && $.fn.selectWoo) {
        productSelect.selectWoo({
            ajax: {
                url: itchenking_admin.ajaxurl,
                dataType: "json",
                delay: 300,
                data: function (params) {
                    return {
                        action: "itchenking_product_search",
                        nonce: itchenking_admin.nonce,
                        term: params.term || ""
                    };
                },
                processResults: function (data) {
                    return data;
                },
                cache: true
            },
            templateResult: formatProduct,
            templateSelection: formatSelection,
            escapeMarkup: function (markup) {
                return markup;
            },
            minimumInputLength: 1,
            width: "100%",
            placeholder: "Search products...",
            closeOnSelect: true
        });
    }

});