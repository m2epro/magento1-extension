EbayListingActionHandler = Class.create(ListingActionHandler, {

    // ---------------------------------------

    startActions: function($super,title,url,selectedProductsParts,requestParams)
    {
        if (typeof requestParams == 'undefined') {
            requestParams = {};
        }

        if (typeof requestParams['is_realtime'] == 'undefined') {
            requestParams['is_realtime'] = (this.gridHandler.getSelectedProductsArray().length <= 10);
        }

        $super(title,url,selectedProductsParts,requestParams);
    },

    stopAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts(100);
        if (selectedProductsParts.length == 0) {
            return;
        }

        var requestParams = {'is_realtime': (this.gridHandler.getSelectedProductsArray().length <= 10)};

        this.startActions(
            this.options.text.stopping_selected_items_message,
            this.options.url.runStopProducts,
            selectedProductsParts,
            requestParams
        );
    },

    stopAndRemoveAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts(100);
        if (selectedProductsParts.length == 0) {
            return;
        }

        var requestParams = {'is_realtime': (this.gridHandler.getSelectedProductsArray().length <= 10)};

        this.startActions(
            this.options.text.stopping_and_removing_selected_items_message,
            this.options.url.runStopAndRemoveProducts,
            selectedProductsParts,
            requestParams
        );
    }

    // ---------------------------------------
});