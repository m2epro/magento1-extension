window.ListingOtherMapping = Class.create(Common, {

    // ---------------------------------------

    initialize: function(gridHandler,component)
    {
        this.gridHandler = gridHandler;
        this.component = component;

        this.attachEvents();
    },

    // ---------------------------------------

    openPopUp: function(productTitle, otherProductId)
    {
        this.attachEvents();
        this.gridHandler.unselectAll();

        this.popUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Mapping Product') + ' "' + productTitle + '"',
            top: 100,
            width: 900,
            height: 500,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        this.popUp.options.destroyOnClose = false;
        $('modal_dialog_message').insert($('pop_up_content').show());

        $('other_product_id').value = otherProductId;
    },

    // ---------------------------------------

    attachEvents: function()
    {
        var self = this;

        $('mapping_submit_button').stopObserving('click').observe('click',function(event) {
            self.map();
        });
        $('mapped_product_id').stopObserving('keypress').observe('keypress',function(event) {
            event.keyCode == Event.KEY_RETURN && self.map();
        });
        $('sku').stopObserving('keypress').observe('keypress',function(event) {
            event.keyCode == Event.KEY_RETURN && self.map();
        });
    },

    // ---------------------------------------

    map: function()
    {
        var self = this;
        var productId = $('mapped_product_id').value;
        var sku = $('sku').value;
        var otherProductId = $('other_product_id').value;

        MessageObj.clearAll();

        if (otherProductId == '' || (/^\s*(\d)*\s*$/i).test(otherProductId) == false) {
            return;
        }

        if ((sku == '' && productId == '')) {
            $('mapped_product_id').focus();
            alert(M2ePro.translator.translate('Please enter correct Product ID or SKU'));
            return;
        }
        if (((/^\s*(\d)*\s*$/i).test(productId) == false)) {
            alert(M2ePro.translator.translate('Please enter correct Product ID.'));
            $('mapped_product_id').focus();
            $('mapped_product_id').value = '';
            $('sku').value = '';
            return;
        }

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_listing_other_mapping/map', {}), {
            method: 'post',
            parameters: {
                componentMode: self.component,
                productId: productId,
                sku: sku,
                otherProductId: otherProductId
            },
            onSuccess: function(transport) {

                if (transport.responseText == 0) {
                    self.gridHandler.unselectAllAndReload();
                    self.popUp.close();
                    self.scroll_page_to_top();
                    MessageObj.addSuccess(M2ePro.translator.translate('Product(s) was Mapped.'));
                } else if (transport.responseText == 1) {
                    alert(M2ePro.translator.translate('Product does not exist.'));
                } else if (transport.responseText == 2) {
                    alert(M2ePro.translator.translate('Current version only supports Simple Products. Please, choose Simple Product.'));
                } else if (transport.responseText == 3) {
                    self.popUp.close();
                    self.scroll_page_to_top();
                    MessageObj.addError(M2ePro.translator.translate('Item was not Mapped as the chosen %product_id% Simple Product has Custom Options.', productId));
                }
            }
        });
    }

    // ---------------------------------------
});