EbayMotorViewItemGridHandler = Class.create(GridHandler, {

    entityId: '',
    //----------------------------------

    initialize: function($super,gridId, entityId)
    {
        $super(gridId);

        this.entityId = entityId;
    },

    //##################################

    prepareActions: function()
    {
        this.actions = {
            removeItemAction: this.removeItem.bind(this)
        };
    },

    //##################################

    removeItem: function()
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/removeItemFromProduct'), {
            postmethod: 'post',
            parameters: {
                items_ids: self.getGridMassActionObj().checkedString,
                entity_id: self.entityId,
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