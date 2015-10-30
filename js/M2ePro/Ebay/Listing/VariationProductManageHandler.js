EbayListingVariationProductManageHandler = Class.create(ActionHandler,{

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

    openPopUp: function(productId, title, filter)
    {
        var self = this;

        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('variationProductManage'), {
            method: 'post',
            parameters: {
                product_id : productId,
                filter: filter
            },
            onSuccess: function (transport) {

                variationProductManagePopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: title.escapeHTML(),
                    top: 5,
                    width: 1100,
                    height: 600,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });
                variationProductManagePopup.options.destroyOnClose = true;

                variationProductManagePopup.productId = productId;

                $('modal_dialog_message').update(transport.responseText);
            }
        });
    },

    closeManageVariationsPopup: function()
    {
        variationProductManagePopup.close();
    },

    loadVariationsGrid: function(showMask)
    {
        var self = this;
        showMask && $('loading-mask').show();

        var gridIframe = $('ebayVariationsProductManageVariationsGridIframe');

        if(gridIframe) {
            gridIframe.remove();
        }

        var iframe = new Element('iframe', {
            id: 'ebayVariationsProductManageVariationsGridIframe',
            src: $('ebayVariationsProductManageVariationsGridIframeUrl').value,
            width: '100%',
            height: '100%',
            style: 'border: none;'
        });

        $('ebayVariationsProductManageVariationsGrid').insert(iframe);

        Event.observe($('ebayVariationsProductManageVariationsGridIframe'), 'load', function() {
            $('loading-mask').hide();
        });
    },

    reloadVariationsGrid: function()
    {
        var gridIframe = $('ebayVariationsProductManageVariationsGridIframe');

        if(!gridIframe) {
            return;
        }
        gridIframe.contentWindow.EbayListingEbayGridHandlerObj.actionHandler.gridHandler.unselectAllAndReload();
    }

    // ---------------------------------------
});
