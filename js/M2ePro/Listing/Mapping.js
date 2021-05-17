window.ListingMapping = Class.create(Common, {

    // ---------------------------------------

    initialize: function(gridHandler, component) {
        this.gridHandler = gridHandler;
        this.component = component;
    },

    // ---------------------------------------

    openPopUp: function(otherProductId, productTitle) {
        this.gridHandler.unselectAll();
        let self = this;
        let title = M2ePro.translator.translate('Mapping Product');

        if (productTitle) {
            title = title + ' "' + productTitle + '"';
        }

        new Ajax.Request(M2ePro.url.get('mapProductPopupHtml'), {
            method: 'post',
            parameters: {
                component_mode: self.component,
            },
            onSuccess: function(transport) {

                this.popUp = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: title,
                    top: 100,
                    width: 900,
                    height: 500,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });

                this.popUp.options.destroyOnClose = true;
                $('modal_dialog_message').insert(transport.responseText);
                $('other_product_id').value = otherProductId;
            }.bind(this)
        });
    },

    // ---------------------------------------

    map: function(productId) {
        let self = this;
        let otherProductId = $('other_product_id').value;

        MessageObj.clearAll();

        if (otherProductId == '' || (/^\s*(\d)*\s*$/i).test(otherProductId) == false) {
            return;
        }

        if (productId == '' || (/^\s*(\d)*\s*$/i).test(productId) == false) {
            return;
        }

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_listing_other_mapping/map', {}), {
            method: 'post',
            parameters: {
                component_mode: self.component,
                product_id: productId,
                other_product_id: otherProductId
            },
            onSuccess: function(transport) {

                let response = transport.responseText.evalJSON();
                if (response.result) {
                    this.gridHandler.unselectAllAndReload();
                    this.popUp.close();
                    this.scroll_page_to_top();
                    MessageObj.addSuccess(M2ePro.translator.translate('Product(s) was Mapped.'));
                } else {
                    alert(M2ePro.translator.translate('Product does not exist.'));
                }
            }.bind(this)
        });
    },

    remap: function(productId) {
        let self = this;
        let listingProductId = $('other_product_id').value;

        MessageObj.clearAll();

        if (listingProductId == '' || (/^\s*(\d)*\s*$/i).test(listingProductId) == false) {
            return;
        }

        if (productId == '' || (/^\s*(\d)*\s*$/i).test(productId) == false) {
            return;
        }

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_listing_mapping/remap'), {
            method: 'post',
            parameters: {
                component_mode: self.component,
                product_id: productId,
                listing_product_id: listingProductId
            },
            onSuccess: function(transport) {

                let response = transport.responseText.evalJSON();

                this.gridHandler.unselectAllAndReload();
                this.popUp.close();
                this.scroll_page_to_top();

                if (response.result) {
                    MessageObj.addSuccess(response.message);
                } else {
                    MessageObj.addError(response.message);
                }
            }.bind(this)
        });
    }

    // ---------------------------------------
});