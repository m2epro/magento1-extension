BuyListingProductsFilterHandler = Class.create();
BuyListingProductsFilterHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    templateSellingFormatId: null,
    marketplaceId: null,

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    store_id_change: function()
    {
        BuyListingProductsFilterHandlerObj.checkMessages();
    },

    //----------------------------------

    checkMessages: function()
    {
        if (BuyListingProductsFilterHandlerObj.templateSellingFormatId === null || BuyListingProductsFilterHandlerObj.marketplaceId === null) {
            return;
        }

        var id = BuyListingProductsFilterHandlerObj.templateSellingFormatId,
            nick = 'selling_format',
            storeId = $('store_id').value,
            marketplaceId = BuyListingProductsFilterHandlerObj.marketplaceId,
            checkAttributesAvailability = false,
            container = 'store_messages',
            callback = function() {
                var refresh = $(container).down('a.refresh-messages');
                if (refresh) {
                    refresh.observe('click', function() {
                        this.checkMessages();
                    }.bind(this))
                }
            }.bind(this);

        TemplateHandlerObj.checkMessages(
            id,
            nick,
            '',
            storeId,
            marketplaceId,
            checkAttributesAvailability,
            container,
            callback
        );
    }

    //----------------------------------
});