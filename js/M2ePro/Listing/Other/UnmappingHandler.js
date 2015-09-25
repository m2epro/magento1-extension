ListingOtherUnmappingHandler = Class.create(ActionHandler, {

    //----------------------------------

    options: {},

    setOptions: function(options)
    {
        this.options = Object.extend(this.options, options);
        return this;
    },

    //----------------------------------

    run: function()
    {
        this.unmappingProducts(
            this.gridHandler.getSelectedProductsString()
        );
    },

    unmappingProducts: function(productsString)
    {
        new Ajax.Request(this.options.url.unmappingProducts, {
            method: 'post',
            parameters: {
                componentMode: this.options.customData.componentMode,
                product_ids: productsString
            },
            onSuccess: (function(transport) {

                MagentoMessageObj.clearAll();

                if (transport.responseText == '1') {
                    MagentoMessageObj.addSuccess(this.options.text.successfully_unmapped);
                } else {
                    MagentoMessageObj.addError(this.options.text.not_enough_data);
                }

                this.gridHandler.unselectAllAndReload();
            }).bind(this)
        });
    }

    //----------------------------------
});