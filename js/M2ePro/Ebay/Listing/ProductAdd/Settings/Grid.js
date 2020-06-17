window.EbayListingProductAddSettingsGrid = Class.create(EbayListingSettingsGrid, {

    // ---------------------------------------

    marketplaceId: null,
    accountId: null,

    // ---------------------------------------

    initialize: function($super, gridId, listingId, marketplaceId, accountId)
    {
        this.marketplaceId = marketplaceId;
        this.accountId = accountId;

        $super(gridId, listingId);
    },

    prepareActions: function($super)
    {
        $super();

        this.actions = Object.extend(this.actions, {
            removeItemAction: function(id) {
                var ids = id ? [id] : this.getSelectedProductsArray();
                this.removeItems(ids);
            }.bind(this)
        });

    },

    // ---------------------------------------

    removeItems: function(ids)
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        var url = M2ePro.url.get('adminhtml_ebay_listing_productAdd/delete');
        new Ajax.Request(url, {
            method: 'post',
            parameters: {
                ids: ids.join(',')
            },
            onSuccess: function() {
                this.unselectAllAndReload();
            }.bind(this)
        });
    },

    // ---------------------------------------

    continue: function()
    {
        MessageObj.clearAll();

        var url = M2ePro.url.get('adminhtml_ebay_listing_productAdd/validate');
        new Ajax.Request(url, {
            method: 'get',
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();
                if (response['validation'] == true) {
                    setLocation(M2ePro.url.get('adminhtml_ebay_listing_categorySettings'));
                } else {
                    MessageObj.addError(response['message']);
                }

            }.bind(this)
        });
    }

    // ---------------------------------------
});
