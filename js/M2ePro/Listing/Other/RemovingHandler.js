ListingOtherRemovingHandler = Class.create(ActionHandler, {

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
        this.removingProducts(
            this.gridHandler.getSelectedProductsString()
        );
    },

    removingProducts: function(productsString)
    {
        new Ajax.Request(this.options.url.removingProducts, {
            method: 'post',
            parameters: {
                componentMode: this.options.customData.componentMode,
                product_ids: productsString
            },
            onSuccess: (function(transport) {

                MagentoMessageObj.clearAll();

                if (transport.responseText == '1') {
                    MagentoMessageObj.addSuccess(this.options.text.successfully_removed);
                } else {
                    MagentoMessageObj.addError(this.options.text.not_enough_data);
                }

                this.gridHandler.unselectAllAndReload();
            }).bind(this)
        });
    }

    //----------------------------------
});