window.WalmartListingProductsFilter = Class.create(Common, {

    // ---------------------------------------

    templateSellingFormatId: null,
    marketplaceId: null,

    // ---------------------------------------

    initialize: function() {},

    // ---------------------------------------

    store_id_change: function()
    {
        WalmartListingProductsFilterObj.checkMessages();
    },

    // ---------------------------------------

    checkMessages: function()
    {
        if (WalmartListingProductsFilterObj.templateSellingFormatId === null || WalmartListingProductsFilterObj.marketplaceId === null) {
            return;
        }

        var id = WalmartListingProductsFilterObj.templateSellingFormatId,
            nick = 'selling_format',
            storeId = $('store_id').value,
            marketplaceId = WalmartListingProductsFilterObj.marketplaceId,
            container = 'store_messages',
            callback = function() {
                var refresh = $(container).down('a.refresh-messages');
                if (refresh) {
                    refresh.observe('click', function() {
                        this.checkMessages();
                    }.bind(this))
                }
            }.bind(this);

        TemplateManagerObj.checkMessages(
            id,
            nick,
            '',
            storeId,
            marketplaceId,
            container,
            callback
        );
    }

    // ---------------------------------------
});