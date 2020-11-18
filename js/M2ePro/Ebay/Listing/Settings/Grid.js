window.EbayListingSettingsGrid = Class.create(EbayListingViewGrid, {

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

    // ---------------------------------------

    prepareActions: function($super)
    {
        $super();

        this.movingHandler = new ListingMoving(this);

        this.actions = Object.extend(this.actions, {

            editAllSettingsAction: function(id) {
                this.editSettings(id);
            }.bind(this),
            editGeneralSettingsAction: function(id) {
                this.editSettings(id, 'general');
            }.bind(this),
            editSellingSettingsAction: function(id) {
                this.editSettings(id, 'selling');
            }.bind(this),
            editSynchSettingsAction: function(id) {
                this.editSettings(id, 'synchronization');
            }.bind(this),

            editCategorySettingsAction: function(id) {
                EbayListingCategoryObj.editCategorySettings(id, 'both');
            }.bind(this),
            editMotorsAction: function(id) {
                this.openMotorsPopup(id);
            }.bind(this),

            movingAction: this.movingHandler.run.bind(this.movingHandler),

            transferringAction: function(id) {
                this.transferring(id);
            }.bind(this)

        });
    },

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

    editSettings: function(id, tab)
    {
        this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_template/editListingProduct'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                ids: this.selectedProductsIds.join(','),
                tab: tab || ''
            },
            onSuccess: function(transport) {

                this.unselectAll();

                var title = this.getPopUpTitle(tab, this.getSelectedProductsTitles());

                this.openPopUp(title, transport.responseText);

                ebayListingTemplateEditTabsJsTabs.moveTabContentInDest();
            }.bind(this)
        });
    },

    openMotorsPopup: function(id)
    {
        EbayMotorsObj.savedNotes = {};

        this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();
        EbayMotorsObj.openAddPopUp(this.selectedProductsIds);
    },

    // ---------------------------------------

    saveSettings: function(savedTemplates)
    {
        var requestParams = {};

        // push information about saved templates into the request params
        // ---------------------------------------
        $H(savedTemplates).each(function(i) {
            requestParams[i.key] = i.value;
        });
        // ---------------------------------------

        // ---------------------------------------
        requestParams['ids'] = this.selectedProductsIds.join(',');
        // ---------------------------------------

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_template/saveListingProduct'), {
            method: 'post',
            asynchronous: true,
            parameters: requestParams,
            onSuccess: function(transport) {
                Windows.getFocusedWindow().close();
                this.getGridObj().doFilter();
            }.bind(this)
        });
    },

    // ---------------------------------------

    getSelectedProductsTitles: function()
    {
        if (this.selectedProductsIds.length > 3) {
            return '';
        }

        var title = '';

        // use the names of only first three products for pop up title
        for (var i = 0; i < 3; i++) {
            if (typeof this.selectedProductsIds[i] == 'undefined') {
                break;
            }

            if (title != '') {
                title += ', ';
            }

            title += this.getProductNameByRowId(this.selectedProductsIds[i]);
        }

        return title;
    },

    // ---------------------------------------

    getPopUpTitle: function(tab, productTitles)
    {
        var title;

        switch (tab) {
            case 'general':
                title = M2ePro.translator.translate('Edit Payment and Shipping Settings');
                break;
            case 'selling':
                title = M2ePro.translator.translate('Edit Selling Settings');
                break;
            case 'synchronization':
                title = M2ePro.translator.translate('Edit Synchronization Settings');
                break;
            default:
                title = M2ePro.translator.translate('Edit Settings');
        }

        if (productTitles) {
            title += ' ' + M2ePro.translator.translate('for') + '"' + productTitles + '"';
        }

        title += '.';

        return title;
    },

    // ---------------------------------------

    transferring: function(id)
    {
        this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();
        this.unselectAll();

        EbayListingTransferringObj.popupShow(this.selectedProductsIds);
    },

    // ---------------------------------------

    confirm: function()
    {
        return true;
    }

    // ---------------------------------------
});
