EbayListingOtherGridHandler = Class.create(ListingOtherGridHandler, {

    //----------------------------------

    getComponent: function()
    {
        return 'ebay';
    },

    //----------------------------------

    getLogViewUrl: function(rowId)
    {
        return M2ePro.url.get('adminhtml_ebay_log/listingOther', {
            id: rowId
        });
    },

    //----------------------------------

    getSelectedItemsParts: function()
    {
        var selectedProductsArray = this.getSelectedProductsArray();

        if (this.getSelectedProductsString() == '' || selectedProductsArray.length == 0) {
            return [];
        }

        var maxProductsInPart = this.getMaxProductsInPart();

        var result = [];
        for (var i=0;i<selectedProductsArray.length;i++) {
            if (result.length == 0 || result[result.length-1].length == maxProductsInPart) {
                result[result.length] = [];
            }
            result[result.length-1][result[result.length-1].length] = selectedProductsArray[i];
        }

        return result;
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

    prepareActions: function($super)
    {
        $super();

        this.actionHandler = new EbayListingOtherActionHandler(this);

        this.actions = Object.extend(this.actions, {
            relistAction: this.actionHandler.relistAction.bind(this.actionHandler),
            reviseAction: this.actionHandler.reviseAction.bind(this.actionHandler),
            stopAction: this.actionHandler.stopAction.bind(this.actionHandler)
        });
    }

    //----------------------------------
});