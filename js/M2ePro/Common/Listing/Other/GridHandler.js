CommonListingOtherGridHandler = Class.create(ListingOtherGridHandler, {

    //----------------------------------

    getLogViewUrl: function(rowId)
    {
        return M2ePro.url.get('adminhtml_common_log/listingOther', {
            id: rowId,
            filter: base64_encode('component_mode=' + this.getComponent())
        });
    }

    //----------------------------------
});