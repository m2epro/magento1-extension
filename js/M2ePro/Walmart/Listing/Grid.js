window.WalmartListingGrid = Class.create(ListingGrid, {

    // ---------------------------------------

    getComponent: function()
    {
        return 'walmart';
    },

    getLogViewUrl: function(rowId)
    {
        return M2ePro.url.get('adminhtml_walmart_log/listingProduct', {
            listing_product_id: rowId
        });
    },

    // ---------------------------------------

    getMaxProductsInPart: function()
    {
        return 10;
    },

    // ---------------------------------------

    prepareActions: function($super)
    {
        $super();
        this.movingHandler = new ListingMoving(this);
        this.mappingHandler = new ListingMapping(this, 'walmart');
        this.actionHandler = new WalmartListingAction(this);
        this.productType = new WalmartListingProductType(this);
        this.variationProductManageHandler = new WalmartListingVariationProductManage(this);
        this.editChannelDataHandler = new WalmartListingProductEditChannelData(this);

        this.actions = Object.extend(this.actions, {

            duplicateAction: this.duplicateProducts.bind(this),

            movingAction: this.movingHandler.run.bind(this.movingHandler),
            deleteAndRemoveAction: this.actionHandler.deleteAndRemoveAction.bind(this.actionHandler),

            changeProductTypeIdAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.productType.validateProductsForProductTypeAssign(id, null)
            }).bind(this),

            remapProductAction: function(id) {
                this.mappingHandler.openPopUp(id, null, this.listingId);
            }.bind(this),

        });

    },

    // ---------------------------------------

    tryToMove: function(listingId)
    {
        this.movingHandler.submit(listingId, this.onSuccess);
    },

    onSuccess: function()
    {
        this.unselectAllAndReload();
    },

    // ---------------------------------------

    duplicateProducts: function()
    {
        this.scroll_page_to_top();
        MessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_walmart_listing/duplicateProducts'), {
            method: 'post',
            parameters: {
                component: this.getComponent(),
                ids: this.getSelectedProductsString()
            },
            onSuccess: (function(transport) {

                try {
                    var response = transport.responseText.evalJSON();

                    MessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.message);

                    if (response.type != 'error') {
                        this.unselectAllAndReload();
                    }

                } catch (e) {
                    MessageObj.addError('Internal Error.');
                }
            }).bind(this)
        });
    }

    // ---------------------------------------
});
