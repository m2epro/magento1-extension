WalmartListingGridHandler = Class.create(ListingGridHandler, {

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
        this.movingHandler = new ListingMovingHandler(this);
        this.actionHandler = new WalmartListingActionHandler(this);
        this.templateCategoryHandler = new WalmartListingTemplateCategoryHandler(this);
        this.variationProductManageHandler = new WalmartListingVariationProductManageHandler(this);
        this.editChannelDataHandler = new WalmartListingProductEditChannelDataHandler(this);

        this.actions = Object.extend(this.actions, {

            duplicateAction: this.duplicateProducts.bind(this),

            movingAction: this.movingHandler.run.bind(this.movingHandler),
            deleteAndRemoveAction: this.actionHandler.deleteAndRemoveAction.bind(this.actionHandler),
            resetProductsAction: this.actionHandler.resetProductsAction.bind(this.actionHandler),

            changeTemplateCategoryIdAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.templateCategoryHandler.validateProductsForTemplateCategoryAssign(id, null)
            }).bind(this)

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
        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_walmart_listing/duplicateProducts'), {
            method: 'post',
            parameters: {
                component: this.getComponent(),
                ids: this.getSelectedProductsString()
            },
            onSuccess: (function(transport) {

                try {
                    var response = transport.responseText.evalJSON();

                    MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.message);

                    if (response.type != 'error') {
                        this.unselectAllAndReload();
                    }

                } catch (e) {
                    MagentoMessageObj.addError('Internal Error.');
                }
            }).bind(this)
        });
    }

    // ---------------------------------------
});