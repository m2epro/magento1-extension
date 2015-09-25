EbayListingTranslationGridHandler = Class.create(EbayListingViewGridHandler, {

    //----------------------------------

    afterInitPage: function($super)
    {
        $super();

        $(this.gridId+'_massaction-select').observe('change', function() {
            if (!$('get-estimated-fee')) {
                return;
            }

            if (this.value == 'list') {
                $('get-estimated-fee').show();
            } else {
                $('get-estimated-fee').hide();
            }
        });
    },

    //----------------------------------

    getMaxProductsInPart: function()
    {
        var maxProductsInPart = 10;
        var selectedProductsArray = this.getSelectedProductsArray();

        if (selectedProductsArray.length <= 25) {
            maxProductsInPart = 5;
        }
        if (selectedProductsArray.length <= 15) {
            maxProductsInPart = 3;
        }
        if (selectedProductsArray.length <= 8) {
            maxProductsInPart = 2;
        }
        if (selectedProductsArray.length <= 4) {
            maxProductsInPart = 1;
        }

        return maxProductsInPart;
    },

    //----------------------------------

    getLogViewUrl: function(rowId)
    {
        var temp = this.getProductIdByRowId(rowId);

        var regExpImg= new RegExp('<img[^><]*>','gi');
        var regExpHr= new RegExp('<hr>','gi');

        temp = temp.replace(regExpImg,'');
        temp = temp.replace(regExpHr,'');

        var productId = strip_tags(temp).trim();

        return M2ePro.url.get('adminhtml_ebay_log/listing', {
            filter: base64_encode('product_id[from]='+productId+'&product_id[to]='+productId)
        });
    },

    //----------------------------------

    confirm: function()
    {
        return true;
    }

    //----------------------------------
});