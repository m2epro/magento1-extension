WalmartListingOtherGridHandler = Class.create(ListingOtherGridHandler, {

    // ---------------------------------------

    tryToMove: function(listingId)
    {
        this.movingHandler.submit(listingId, this.onSuccess);
    },

    onSuccess: function(listingId)
    {
        setLocation(
            M2ePro.url.get('adminhtml_walmart_listing_productAdd/index', {id: listingId})
        );
    },

    // ---------------------------------------

    getComponent: function()
    {
        return 'walmart';
    },

    getLogViewUrl: function(rowId)
    {
        return M2ePro.url.get('adminhtml_walmart_log/listingOther', {
            id: rowId,
            filter: base64_encode('component_mode=' + this.getComponent())
        });
    }

    // ---------------------------------------
});