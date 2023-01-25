window.AmazonListingGrid = Class.create(ListingGrid, {

    // ---------------------------------------

    getComponent: function()
    {
        return 'amazon';
    },

    getLogViewUrl: function(rowId)
    {
        return M2ePro.url.get('adminhtml_amazon_log/listingProduct', {
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
        this.mappingHandler = new ListingMapping(this, 'amazon');
        this.actionHandler = new AmazonListingAction(this);
        this.productSearchHandler = new AmazonListingProductSearch(this);
        this.templateDescription = new AmazonListingTemplateDescription(this);
        this.templateShippingHandler = new AmazonListingTemplateShipping(this);
        this.templateProductTaxCodeHandler = new AmazonListingTemplateProductTaxCode(this);
        this.variationProductManageHandler = new AmazonListingVariationProductManage(this);
        this.fulfillmentHandler = new AmazonFulfillment(this);

        this.actions = Object.extend(this.actions, {

            duplicateAction: this.duplicateProducts.bind(this),
            transferringAction: this.transferring.bind(this),

            movingAction: this.movingHandler.run.bind(this.movingHandler),
            deleteAndRemoveAction: this.actionHandler.deleteAndRemoveAction.bind(this.actionHandler),

            assignTemplateDescriptionIdAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.templateDescription.validateProductsForTemplateDescriptionAssign(id)
            }).bind(this),
            unassignTemplateDescriptionIdAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.templateDescription.unassignFromTemplateDescription(id)
            }).bind(this),

            assignTemplateShippingIdAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.templateShippingHandler.openPopUp(id)
            }).bind(this),
            unassignTemplateShippingIdAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.templateShippingHandler.unassign(id)
            }).bind(this),

            assignTemplateProductTaxCodeIdAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.templateProductTaxCodeHandler.openPopUp(id)
            }).bind(this),
            unassignTemplateProductTaxCodeIdAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.templateProductTaxCodeHandler.unassign(id)
            }).bind(this),

            switchToAfnAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.fulfillmentHandler.switchToAFN(id);
            }).bind(this),
            switchToMfnAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.fulfillmentHandler.switchToMFN(id);
            }).bind(this),

            assignGeneralIdAction: (function() {
                this.productSearchHandler.searchGeneralIdAuto(this.getSelectedProductsString())
            }).bind(this),
            newGeneralIdAction: (function() {
                this.productSearchHandler.addNewGeneralId(this.getSelectedProductsString())
            }).bind(this),
            unassignGeneralIdAction: (function() {
                this.productSearchHandler.unmapFromGeneralId(this.getSelectedProductsString())
            }).bind(this),

            remapProductAction: function(id) {
                this.mappingHandler.openPopUp(id, null, this.listingId);
            }.bind(this),
        });

    },

    // ---------------------------------------

    tryToMove: function(listingId)
    {
        this.movingHandler.submit(listingId, this.onSuccess)
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

        new Ajax.Request(M2ePro.url.get('adminhtml_amazon_listing/duplicateProducts'), {
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
    },

    transferring: function(id)
    {
        this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();
        this.unselectAll();

        AmazonListingTransferringObj.popupShow(this.selectedProductsIds);
    },

    // ---------------------------------------

    unassignTemplateDescriptionIdActionConfrim: function (id)
    {
        if (!this.confirm()) {
            return;
        }

        this.templateDescription.unassignFromTemplateDescription(id)
    },

    // ---------------------------------------

    unassignTemplateShippingIdActionConfrim: function (id)
    {
        if (!this.confirm()) {
            return;
        }

        this.templateShippingHandler.unassign(id)
    },

    unassignTemplateProductTaxCodeIdActionConfrim: function (id)
    {
        if (!this.confirm()) {
            return;
        }

        this.templateProductTaxCodeHandler.unassign(id)
    }

    // ---------------------------------------
});