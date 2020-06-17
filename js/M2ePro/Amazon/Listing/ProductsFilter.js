window.AmazonListingProductsFilter = Class.create(Common, {

    // ---------------------------------------

    templateSellingFormatId: null,
    marketplaceId: null,

    // ---------------------------------------

    initialize: function() {},

    // ---------------------------------------

    store_id_change: function()
    {
        AmazonListingProductsFilterObj.checkMessages();
    },

    // ---------------------------------------

    checkMessages: function()
    {
        if (AmazonListingProductsFilterObj.templateSellingFormatId === null || AmazonListingProductsFilterObj.marketplaceId === null) {
            return;
        }

        var id = AmazonListingProductsFilterObj.templateSellingFormatId,
            nick = 'selling_format',
            storeId = $('store_id').value,
            marketplaceId = AmazonListingProductsFilterObj.marketplaceId,
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