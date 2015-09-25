EbayListingGridHandler = Class.create(GridHandler, {

    //----------------------------------

    backParam: base64_encode('*/adminhtml_ebay_listing/index'),

    //----------------------------------

    prepareActions: function()
    {
        return false;
    },

    //----------------------------------

    addProductsSourceProductsAction: function(id)
    {
        setLocation(M2ePro.url.get('adminhtml_ebay_listing_productAdd/index', {
            listing_id: id,
            source: 'products',
            clear: true,
            back: this.backParam
        }));
    },

    //----------------------------------

    addProductsSourceCategoriesAction: function(id)
    {
        setLocation(M2ePro.url.get('adminhtml_ebay_listing_productAdd/index', {
            listing_id: id,
            source: 'categories',
            clear: true,
            back: this.backParam
        }));
    }

    //----------------------------------
});