ListingOtherRemovingHandler = Class.create(ActionHandler, {

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

                MagentoMessageObj.clearAll();

                if (transport.responseText == '1') {
                    MagentoMessageObj.addSuccess(M2ePro.text.successfully_removed);
                } else {
                    MagentoMessageObj.addError(M2ePro.text.not_enough_data);
                }

                this.gridHandler.unselectAllAndReload();
            }).bind(this)
        });
    }

    // ---------------------------------------
});