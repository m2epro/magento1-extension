EbayListingProductAddSettingsGridHandler = Class.create(EbayListingSettingsGridHandler, {

    //----------------------------------

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

    //----------------------------------

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

    //----------------------------------

    continue: function()
    {
        MagentoMessageObj.clearAll();

        var url = M2ePro.url.get('adminhtml_ebay_listing_productAdd/validate');
        new Ajax.Request(url, {
            method: 'get',
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();
                if (response['validation'] == true) {
                    setLocation(M2ePro.url.get('adminhtml_ebay_listing_categorySettings'));
                } else {
                    MagentoMessageObj.addError(response['message']);
                }

            }.bind(this)
        });
    }

    //----------------------------------
});
