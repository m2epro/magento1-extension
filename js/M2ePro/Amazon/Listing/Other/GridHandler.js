AmazonListingOtherGridHandler = Class.create(ListingOtherGridHandler, {

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

    getComponent: function()
    {
        return 'amazon';
    }

    // ---------------------------------------
});