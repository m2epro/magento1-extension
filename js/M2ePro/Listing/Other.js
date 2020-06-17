window.ListingOther = Class.create(Common, {

    // ---------------------------------------

    showResetPopup: function()
    {
        this.resetPopup = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Reset 3rd Party Listings'),
            width: 430,
            height: 150,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        this.resetPopup.options.destroyOnClose = false;
        $('modal_dialog_message').insert($('reset_other_listings_popup_content').show());
    },

    resetPopupYesClick: function(url)
    {
        Windows.getFocusedWindow().close();
        setLocation(url);
    }

    // ---------------------------------------
});
