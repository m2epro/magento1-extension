window.ListingOtherRemoving = Class.create(Action, {

    // ---------------------------------------

    run: function()
    {
        this.removingProducts(
            this.gridHandler.getSelectedProductsString()
        );
    },

    removingProducts: function(productsString)
    {
        new Ajax.Request(M2ePro.url.removingProducts, {
            method: 'post',
            parameters: {
                componentMode: M2ePro.customData.componentMode,
                product_ids: productsString
            },
            onSuccess: (function(transport) {

                MessageObj.clearAll();

                if (transport.responseText == '1') {
                    MessageObj.addSuccess(M2ePro.translator.translate('Product(s) was Removed.'));
                } else {
                    MessageObj.addError(M2ePro.translator.translate('Not enough data'));
                }

                this.gridHandler.unselectAllAndReload();
            }).bind(this)
        });
    }

    // ---------------------------------------
});