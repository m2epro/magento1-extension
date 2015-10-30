EbayListingProductBidsHandler = Class.create(ActionHandler,{

    // ---------------------------------------

    initialize: function($super,gridHandler)
    {
        var self = this;

        $super(gridHandler);

    },

    // ---------------------------------------

    options: {},

    setOptions: function(options)
    {
        this.options = Object.extend(this.options,options);
        return this;
    },

    // ---------------------------------------

    parseResponse: function(response)
    {
        if (!response.responseText.isJSON()) {
            return;
        }

        return response.responseText.evalJSON();
    },

    // ---------------------------------------

    openPopUp: function(productId, title)
    {
        var self = this;

        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('getListingProductBids'), {
            method: 'post',
            parameters: {
                product_id : productId
            },
            onSuccess: function (transport) {

                listingProductBidsPopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: title,
                    width: 600,
                    height: 250,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });
                listingProductBidsPopup.options.destroyOnClose = true;

                listingProductBidsPopup.productId = productId;

                $('modal_dialog_message').update(transport.responseText);

                setTimeout(function() {
                    Windows.getFocusedWindow().content.style.height = '';
                    Windows.getFocusedWindow().content.style.maxHeight = '450px';
                }, 50);
            }
        });
    }

    // ---------------------------------------
});
