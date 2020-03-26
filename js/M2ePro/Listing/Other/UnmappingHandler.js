ListingOtherUnmappingHandler = Class.create(ActionHandler, {

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

                MagentoMessageObj.clearAll();

                if (transport.responseText == '1') {
                    MagentoMessageObj.addSuccess(M2ePro.text.successfully_unmapped);
                } else {
                    MagentoMessageObj.addError(M2ePro.text.not_enough_data);
                }

                this.gridHandler.unselectAllAndReload();
            }).bind(this)
        });
    }

    // ---------------------------------------
});