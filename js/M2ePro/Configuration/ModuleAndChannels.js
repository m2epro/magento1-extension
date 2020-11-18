window.ConfigurationModuleAndChannels = Class.create(Common, {

    // ---------------------------------------

    moduleModePopup: function (title)
    {
        var self = this;

            self.moduleModePopup = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 150,
            width: 450,
            height: 250,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        self.moduleModePopup.options.destroyOnClose = true;
        $('modal_dialog_message').insert($('module_mode_information_content').innerHTML);

        this.autoHeightFix();
    }

    // ---------------------------------------
});