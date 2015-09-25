ListingGridHandler = Class.create(GridHandler, {

    //----------------------------------

    productIdCellIndex: 1,
    productTitleCellIndex: 2,

    //----------------------------------

    initialize: function($super,gridId,listingId)
    {
        this.listingId = listingId;

        $super(gridId);
    },

    //----------------------------------

    getProductIdByRowId: function(rowId)
    {
        return this.getCellContent(rowId,this.productIdCellIndex);
    },

    //----------------------------------

    getSelectedItemsParts: function(maxProductsInPart)
    {
        var selectedProductsArray = this.getSelectedProductsArray();

        if (this.getSelectedProductsString() == '' || selectedProductsArray.length == 0) {
            return [];
        }

        var maxProductsInPart = maxProductsInPart || this.getMaxProductsInPart();

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
        alert('abstract getMaxProductsInPart');
    },

    //###############################################

    prepareActions: function()
    {
        this.actionHandler = new ListingActionHandler(this);

        this.actions = {
            listAction: this.actionHandler.listAction.bind(this.actionHandler),
            relistAction: this.actionHandler.relistAction.bind(this.actionHandler),
            reviseAction: this.actionHandler.reviseAction.bind(this.actionHandler),
            stopAction: this.actionHandler.stopAction.bind(this.actionHandler),
            stopAndRemoveAction: this.actionHandler.stopAndRemoveAction.bind(this.actionHandler),
            startTranslateAction: this.actionHandler.startTranslateAction.bind(this.actionHandler),
            stopTranslateAction: this.actionHandler.stopTranslateAction.bind(this.actionHandler)
        };
    }

    //----------------------------------
});