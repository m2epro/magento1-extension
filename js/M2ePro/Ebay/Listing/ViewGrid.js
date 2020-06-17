window.EbayListingViewGrid = Class.create(ListingGrid, {

    // ---------------------------------------

    selectedProductsIds: [],

    // ---------------------------------------

    prepareActions: function($super)
    {
        this.actionHandler = new EbayListingAction(this);

        this.actions = {
            listAction: this.actionHandler.listAction.bind(this.actionHandler),
            relistAction: this.actionHandler.relistAction.bind(this.actionHandler),
            reviseAction: this.actionHandler.reviseAction.bind(this.actionHandler),
            stopAction: this.actionHandler.stopAction.bind(this.actionHandler),
            stopAndRemoveAction: this.actionHandler.stopAndRemoveAction.bind(this.actionHandler),
            previewItemsAction: this.actionHandler.previewItemsAction.bind(this.actionHandler)
        };

        this.variationProductManageHandler = new EbayListingVariationProductManage(this);
        this.listingProductBids = new EbayListingProductBids(this);
    },

    massActionSubmitClick: function($super)
    {
        if (this.getSelectedProductsString() == '' || this.getSelectedProductsArray().length == 0) {
            alert(M2ePro.translator.translate('Please select the Products you want to perform the Action on.'));
            return;
        }
        $super();
    },

    // ---------------------------------------

    getComponent: function()
    {
        return 'ebay';
    },

    // ---------------------------------------

    openPopUp: function(title, content, params)
    {
        var self = this;
        params = params || {};

        var config = Object.extend({
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            top: 50,
            maxHeight: 500,
            height: 500,
            width: 1000,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() {
                return true;
            }
        }, params);

        try {
            if (!Windows.getFocusedWindow() || !$('modal_dialog_message')) {
                Dialog.info(null, config);
            }
            Windows.getFocusedWindow().setTitle(title);
            $('modal_dialog_message').innerHTML = content;
            $('modal_dialog_message').innerHTML.evalScripts();
        } catch (ignored) {}
    }

    // ---------------------------------------
});
