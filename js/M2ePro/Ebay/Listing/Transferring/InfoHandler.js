EbayListingTransferringInfoHandler = Class.create(EbayListingViewGridHandler, {

    //----------------------------------

    initialize: function() {},

    // --------------------------------

    showTranslationDetails: function(title, content)
    {
        this.openPopUp(title, content, {width: 500, height: 405});
    },

    //----------------------------------

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

    //----------------------------------
});