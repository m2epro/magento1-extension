AmazonListingGridHandler = Class.create(ListingGridHandler, {

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
        this.movingHandler = new ListingMovingHandler(this);
        this.actionHandler = new AmazonListingActionHandler(this);
        this.productSearchHandler = new AmazonListingProductSearchHandler(this);
        this.templateDescriptionHandler = new AmazonListingTemplateDescriptionHandler(this);
        this.templateShippingHandler = new AmazonListingTemplateShippingHandler(this);
        this.templateProductTaxCodeHandler = new AmazonListingTemplateProductTaxCodeHandler(this);
        this.variationProductManageHandler = new AmazonListingVariationProductManageHandler(this);
        this.fulfillmentHandler = new AmazonFulfillmentHandler(this);
        this.repricingHandler = new AmazonRepricingHandler(this);

        this.actions = Object.extend(this.actions, {

            duplicateAction: this.duplicateProducts.bind(this),

            movingAction: this.movingHandler.run.bind(this.movingHandler),
            deleteAndRemoveAction: this.actionHandler.deleteAndRemoveAction.bind(this.actionHandler),

            assignTemplateDescriptionIdAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.templateDescriptionHandler.validateProductsForTemplateDescriptionAssign(id)
            }).bind(this),
            unassignTemplateDescriptionIdAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.templateDescriptionHandler.unassignFromTemplateDescription(id)
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

            addToRepricingAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.repricingHandler.addToRepricing(id);
            }).bind(this),
            showDetailsAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.repricingHandler.showDetails(id);
            }).bind(this),
            editRepricingAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.repricingHandler.editRepricing(id);
            }).bind(this),
            removeFromRepricingAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.repricingHandler.removeFromRepricing(id);
            }).bind(this),

            assignGeneralIdAction: (function() { this.productSearchHandler.searchGeneralIdAuto(this.getSelectedProductsString())}).bind(this),
            newGeneralIdAction: (function() { this.productSearchHandler.addNewGeneralId(this.getSelectedProductsString())}).bind(this),
            unassignGeneralIdAction: (function() { this.productSearchHandler.unmapFromGeneralId(this.getSelectedProductsString())}).bind(this)

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
        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_amazon_listing/duplicateProducts'), {
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
    },

    // ---------------------------------------

    unassignTemplateDescriptionIdActionConfrim: function (id)
    {
        if (!this.confirm()) {
            return;
        }

        this.templateDescriptionHandler.unassignFromTemplateDescription(id)
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