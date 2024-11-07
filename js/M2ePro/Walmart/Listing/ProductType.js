window.WalmartListingProductType = Class.create(Action, {

    // ---------------------------------------

    mapToProductType: function(el, templateId)
    {
        var self = this;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.mapToProductType, {
            method: 'post',
            parameters: {
                products_ids: productTypePopup.productsIds,
                template_id: templateId
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                self.gridHandler.unselectAllAndReload();

                if (response.messages.length > 0) {
                    MessageObj.clearAll();
                    response.messages.each(function(msg) {
                        MessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](msg);
                    });
                }
            }
        });

        productTypePopup.close();
    },

    // ---------------------------------------

    validateProductsForProductTypeAssign: function(productsIds, magentoCategoriesIds)
    {
        var self = this;
        self.flagSuccess = false;

        new Ajax.Request(M2ePro.url.validateProductsForProductTypeAssign, {
            method: 'post',
            parameters: {
                products_ids: productsIds
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                if (response.messages.length > 0) {
                    MessageObj.clearAll();
                    response.messages.each(function(msg) {
                        MessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                    });
                }

                if (!response.data) {
                    return;
                }

                if (typeof popUp != 'undefined') {
                    popUp.close();
                }

                self.openPopUp(0, M2ePro.text.productTypePopupTitle, response.products_ids, magentoCategoriesIds, response.data);
            }
        });
    },

    unassign: function (productsIds) {
        var self = this;

        new Ajax.Request(M2ePro.url.unassignProductType, {
            method: 'post',
            parameters: {
                products_ids: productsIds
            },
            onSuccess: function (transport) {

                if (!transport.responseText.isJSON()) {
                    self.alert(transport.responseText);
                    return;
                }

                self.gridHandler.unselectAllAndReload();

                var response = transport.responseText.evalJSON();

                if (response.messages.length > 0) {
                    MessageObj.clear();
                    response.messages.each(function (msg) {
                        MessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                    });
                }
            }
        });
    },

    // ---------------------------------------

    openPopUp: function(mode, title, productsIds, magentoCategoriesIds, contentData)
    {
        var self = this;
        self.gridHandler.unselectAll();

        MessageObj.clearAll();

        productTypePopup = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 70,
            width: 800,
            height: 550,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        productTypePopup.options.destroyOnClose = true;

        productTypePopup.productsIds = productsIds;
        productTypePopup.magentoCategoriesIds = magentoCategoriesIds;

        $('modal_dialog_message').insert(contentData);

        self.loadProductTypeGrid();

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '600px';
        }, 50);
    },

    loadProductTypeGrid: function() {

        new Ajax.Request(M2ePro.url.viewProductTypesGrid, {
            method: 'post',
            parameters: {
                products_ids: productTypePopup.productsIds,
                magento_categories_ids: productTypePopup.magentoCategoriesIds
            },
            onSuccess: function(transport) {
                $('product_type_grid').update(transport.responseText);
                $('product_type_grid').show();
            }
        });
    },

    // ---------------------------------------

    createProductTypeInNewTab: function(stepWindowUrl)
    {
        var self = this;
        var win = window.open(stepWindowUrl);

        var intervalId = setInterval(function() {
            if (!win.closed) {
                return;
            }

            clearInterval(intervalId);

            walmartProductTypeGridJsObject.reload();
        }, 1000);
    }

    // ---------------------------------------
});