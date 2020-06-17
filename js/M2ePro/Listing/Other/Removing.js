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
                    MessageObj.addSuccess(M2ePro.text.successfully_removed);
                } else {
                    MessageObj.addError(M2ePro.text.not_enough_data);
                }

                this.gridHandler.unselectAllAndReload();
            }).bind(this)
        });
    }

    // ---------------------------------------
});