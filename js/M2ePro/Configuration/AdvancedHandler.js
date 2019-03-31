ConfigurationAdvancedHandler = Class.create();
ConfigurationAdvancedHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    informationPopup: function()
    {
        adnvancedPopup = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Migration Information'),
            top: 150,
            width: 750,
            height: 250,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        adnvancedPopup.options.destroyOnClose = true;
        $('modal_dialog_message').insert($('information_content').innerHTML);

        this.autoHeightFix();
    }

    // ---------------------------------------
});