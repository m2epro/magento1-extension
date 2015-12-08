CommonAmazonListingGridHandler = Class.create(CommonListingGridHandler, {

    // ---------------------------------------

    getComponent: function()
    {
        return 'amazon';
    },

    // ---------------------------------------

    getMaxProductsInPart: function()
    {
        return 1000;
    },

    // ---------------------------------------

    prepareActions: function($super)
    {
        $super();
        this.movingHandler = new ListingMovingHandler(this);
        this.actionHandler = new CommonAmazonListingActionHandler(this);
        this.productSearchHandler = new CommonAmazonListingProductSearchHandler(this);
        this.templateDescriptionHandler = new CommonAmazonListingTemplateDescriptionHandler(this);
        this.templateShippingOverrideHandler = new CommonAmazonListingTemplateShippingOverrideHandler(this);
        this.variationProductManageHandler = new CommonAmazonListingVariationProductManageHandler(this);
        this.fulfillmentHandler = new CommonAmazonFulfillmentHandler(this);
        this.repricingHandler = new CommonAmazonRepricingHandler(this);

        this.actions = Object.extend(this.actions, {

            movingAction: this.movingHandler.run.bind(this.movingHandler),
            deleteAndRemoveAction: this.actionHandler.deleteAndRemoveAction.bind(this.actionHandler),

            assignTemplateDescriptionIdAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.templateDescriptionHandler.validateProductsForTemplateDescriptionAssign(id)
            }).bind(this),
            unassignTemplateDescriptionIdAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.templateDescriptionHandler.unassignFromTemplateDescrition(id)
            }).bind(this),

            assignTemplateShippingOverrideIdAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.templateShippingOverrideHandler.openPopUp(id)
            }).bind(this),
            unassignTemplateShippingOverrideIdAction: (function(id) {
                id = id || this.getSelectedProductsString();
                this.templateShippingOverrideHandler.unassign(id)
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

    unassignTemplateDescriptionIdActionConfrim: function (id)
    {
        if (!this.confirm()) {
            return;
        }

        this.templateDescriptionHandler.unassignFromTemplateDescrition(id)
    },

    // ---------------------------------------

    unassignTemplateShippingOverrideIdActionConfrim: function (id)
    {
        if (!this.confirm()) {
            return;
        }

        this.templateShippingOverrideHandler.unassign(id)
    }

    // ---------------------------------------
});