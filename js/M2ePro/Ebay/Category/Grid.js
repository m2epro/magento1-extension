window.EbayCategoryGrid = Class.create(Grid, {

    // ---------------------------------------

    accountId     : null,
    marketplaceId : null,
    templateId    : null,

    // ---------------------------------------

    initialize: function($super, gridId, marketplaceId, accountId, templateId)
    {
        this.templateId    = templateId;
        this.marketplaceId = marketplaceId;
        this.accountId     = accountId;

        $super(gridId);
    },

    // ---------------------------------------

    prepareActions: function()
    {
        this.actions = {
            resetSpecificsToDefaultAction: function() {
                this.resetSpecificsToDefault();
            }.bind(this),
            editEbayCategoryAction: function() {
                EbayListingCategoryObj.editCategorySettings();
            }
        };
    },

    confirm: function($super)
    {
        var action = '';
        $$('select#'+this.gridId+'_massaction-select option').each(function(o) {
            if (o.selected && o.value != '') {
                action = o.value;
            }
        });

        if (action === 'editEbayCategory') {
            return true;
        }

        return $super();
    },

    // ---------------------------------------

    resetSpecificsToDefault: function ()
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing/resetSpecificsToDefault'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                ids         : this.getSelectedProductsString(),
                template_id : this.templateId
            },
            onSuccess: function(transport) {
                this.unselectAllAndReload();
            }.bind(this)
        });
    }

    // ---------------------------------------

});
