OrderMerchantFulfillmentMagentoHandler = Class.create();
OrderMerchantFulfillmentMagentoHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    openMagentoShipmentNotificationPopup: function(content)
    {
        var config = {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Amazon\'s Shipping Services'),
            top: 50,
            width: 500,
            height: 190,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show
        };

        if (!this.popUp) {
            this.popUp = Dialog.info(content, config);
        }

        $('modal_dialog_message').innerHTML.evalScripts();

        this.autoHeightFix();

        return this.popUp;
    },

    openMagentoShipmentPrimePopup: function(content)
    {
        var config = {
            draggable: true,
            resizable: true,
            closable: false,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Amazon\'s Shipping Services'),
            top: 50,
            width: 500,
            height: 100,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show
        };

        if (!this.popUp) {
            this.popUp = Dialog.info(content, config);
        }

        $('modal_dialog_message').innerHTML.evalScripts();

        this.autoHeightFix();

        return this.popUp;
    },

    // ---------------------------------------

    discardNotificationPopup: function()
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_order_merchantFulfillment/discardMagentoNotificationPopup'), {
            method: 'post',
            onSuccess: function(transport) {
                Windows.getFocusedWindow().close();
            }
        });
    }
});