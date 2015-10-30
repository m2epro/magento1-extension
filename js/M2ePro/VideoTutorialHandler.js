VideoTutorialHandler = Class.create();
VideoTutorialHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    // determines either to close or not to close popup window
    closeCallback: function() {
        return confirm(M2ePro.translator.translate('Are you sure?'));
    },

    // ---------------------------------------

    initialize: function(popUpBlockId,title,callbackWhenClose)
    {
        this.title = title;
        this.popUpBlockId = popUpBlockId;
        this.callbackWhenClose = callbackWhenClose;
    },

    // ---------------------------------------

    openPopUp: function()
    {
        var self = this;
        this.popUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: this.title,
            top: 30,
            width: 900,
            height: 525,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() {
                return self.closeCallback();
            },
            onClose: function() {
                self.callbackWhenClose();
            }
        });

        this.popUp.options.destroyOnClose = false;
        $('modal_dialog_message').insert($(this.popUpBlockId).show());
    },

    closePopUp: function()
    {
        this.popUp.close();
        this.callbackWhenClose();
    }

    // ---------------------------------------
});