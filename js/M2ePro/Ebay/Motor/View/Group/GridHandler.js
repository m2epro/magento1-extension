EbayMotorViewGroupGridHandler = Class.create(GridHandler, {

    //----------------------------------

    initialize: function($super,gridId, listingProductId)
    {
        $super(gridId);
        this.listingProductId = listingProductId;
    },

    //##################################

    prepareActions: function()
    {
        this.actions = {
            removeGroupAction: this.removeGroup.bind(this)
        };
    },

    //##################################

    removeGroup: function()
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/removeGroupFromListingProduct'), {
            postmethod: 'post',
            parameters: {
                groups_ids: self.getGridMassActionObj().checkedString,
                listing_product_id: self.listingProductId,
                motors_type: EbayMotorsHandlerObj.motorsType
            },
            onSuccess: function(transport) {

                if (transport.responseText == '0') {
                    self.unselectAllAndReload();
                }
            }
        });
    },

    //##################################

});