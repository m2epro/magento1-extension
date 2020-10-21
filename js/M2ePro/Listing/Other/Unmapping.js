window.ListingOtherUnmapping = Class.create(Action, {

    // ---------------------------------------

    run: function()
    {
        this.unmappingProducts(
            this.gridHandler.getSelectedProductsString()
        );
    },

    unmappingProducts: function(productsString)
    {
        new Ajax.Request(M2ePro.url.unmappingProducts, {
            method: 'post',
            parameters: {
                componentMode: M2ePro.customData.componentMode,
                product_ids: productsString
            },
            onSuccess: (function(transport) {

                MessageObj.clearAll();

                if (transport.responseText == '1') {
                    MessageObj.addSuccess(M2ePro.translator.translate('Product(s) was Unmapped.'));
                } else {
                    MessageObj.addError(M2ePro.translator.translate('Not enough data'));
                }

                this.gridHandler.unselectAllAndReload();
            }).bind(this)
        });
    }

    // ---------------------------------------
});