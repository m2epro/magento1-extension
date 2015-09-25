AmazonListingProductsFilterHandler = Class.create();
AmazonListingProductsFilterHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    templateSellingFormatId: null,
    marketplaceId: null,

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    store_id_change: function()
    {
        AmazonListingProductsFilterHandlerObj.checkMessages();
    },

    //----------------------------------

    checkMessages: function()
    {
        if (AmazonListingProductsFilterHandlerObj.templateSellingFormatId === null || AmazonListingProductsFilterHandlerObj.marketplaceId === null) {
            return;
        }

        var id = AmazonListingProductsFilterHandlerObj.templateSellingFormatId,
            nick = 'selling_format',
            storeId = $('store_id').value,
            marketplaceId = AmazonListingProductsFilterHandlerObj.marketplaceId,
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

    //----------------------------------
});