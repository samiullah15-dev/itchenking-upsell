jQuery(function ($) {

    $(".itchenking-color-field").wpColorPicker();

    function formatProduct(product) {
        if (product.loading) {
            return product.text;
        }

        let image = product.image ? product.image : "";
        let price = product.price ? product.price : "";

        return $(
            '<div style="display:flex;align-items:center;gap:10px;">' +
                '<img src="' + image + '" style="width:38px;height:38px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">' +
                '<div>' +
                    '<div style="font-weight:600;">' + product.text + '</div>' +
                    '<div style="font-size:12px;color:#666;">' + price + '</div>' +
                '</div>' +
            '</div>'
        );
    }

    if ($("#itchenking_manual_products").length && $.fn.selectWoo) {
        $("#itchenking_manual_products").selectWoo({
            ajax: {
                url: itchenking_admin.ajaxurl,
                dataType: "json",
                delay: 250,
                data: function (params) {
                    return {
                        action: "itchenking_product_search",
                        nonce: itchenking_admin.nonce,
                        term: params.term || ""
                    };
                },
                processResults: function (data) {
                    return data;
                }
            },
            templateResult: formatProduct,
            templateSelection: function (product) {
                return product.text || product.id;
            },
            escapeMarkup: function (markup) {
                return markup;
            },
            minimumInputLength: 1,
            width: "100%",
            placeholder: "Search and select products"
        });
    }

});