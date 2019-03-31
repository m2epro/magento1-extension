WalmartListingProductsFilterHandler = Class.create();
WalmartListingProductsFilterHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    templateSellingFormatId: null,
    marketplaceId: null,

    // ---------------------------------------

    initialize: function() {},

    // ---------------------------------------

    store_id_change: function()
    {
        WalmartListingProductsFilterHandlerObj.checkMessages();
    },

    // ---------------------------------------

    checkMessages: function()
    {
        if (WalmartListingProductsFilterHandlerObj.templateSellingFormatId === null || WalmartListingProductsFilterHandlerObj.marketplaceId === null) {
            return;
        }

        var id = WalmartListingProductsFilterHandlerObj.templateSellingFormatId,
            nick = 'selling_format',
            storeId = $('store_id').value,
            marketplaceId = WalmartListingProductsFilterHandlerObj.marketplaceId,
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

        TemplateHandlerObj
            .checkMessages(
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

    // ---------------------------------------
});