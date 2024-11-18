window.AmazonListingTemplateProductType = Class.create(Action, {

    // ---------------------------------------

    mapToTemplateProductType: function(el, templateId, mapToGeneralId)
    {
        var self = this;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.mapToTemplateProductType, {
            method: 'post',
            parameters: {
                products_ids: templateDescriptionPopup.productsIds,
                template_id: templateId
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                if (mapToGeneralId) {
                    ListingGridObj.productSearchHandler.addNewGeneralId(response.products_ids);
                } else {
                    self.gridHandler.unselectAllAndReload();

                    if (response.messages.length > 0) {
                        MessageObj.clearAll();
                        response.messages.each(function(msg) {
                            MessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](msg);
                        });
                    }
                }
            }
        });

        templateDescriptionPopup.close();
    },

    // ---------------------------------------
    unassignFromTemplateProductType: function(productsIds)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.unmapFromTemplateProductType, {
            method: 'post',
            parameters: {
                products_ids: productsIds
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                self.gridHandler.unselectAllAndReload();

                var response = transport.responseText.evalJSON();

                MessageObj.clearAll();
                response.messages.each(function(msg) {
                    MessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                });
            }
        });
    },

    // ---------------------------------------

    validateProductsForTemplateProductTypeAssign: function(productsIds)
    {
        var self = this;
        self.flagSuccess = false;

        productsIds = productsIds || ListingGridObj.productSearchHandler.params.productId;

        new Ajax.Request(M2ePro.url.validateProductsForTemplateProductTypeAssign, {
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

                self.openPopUp(0,M2ePro.text.productTypePopupTitle, response.products_ids, null, response.data);
            }
        });
    },

    // ---------------------------------------

    openPopUp: function(mode, title, productsIds, magentoCategoriesIds, contentData, checkIsNewAsinAccepted)
    {
        var self = this;
        self.gridHandler.unselectAll();

        MessageObj.clearAll();

        templateDescriptionPopup = Dialog.info(null, {
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
        templateDescriptionPopup.options.destroyOnClose = true;

        templateDescriptionPopup.productsIds = productsIds;
        templateDescriptionPopup.magentoCategoriesIds = magentoCategoriesIds;
        templateDescriptionPopup.checkIsNewAsinAccepted = checkIsNewAsinAccepted || 0;

        $('modal_dialog_message').insert(contentData);

        self.loadTemplateProductTypeGrid();

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '600px';
        }, 50);
    },

    loadTemplateProductTypeGrid: function() {

        new Ajax.Request(M2ePro.url.viewTemplateProductTypesGrid, {
            method: 'post',
            parameters: {
                products_ids: templateDescriptionPopup.productsIds,
                magento_categories_ids: templateDescriptionPopup.magentoCategoriesIds,
                check_is_new_asin_accepted: templateDescriptionPopup.checkIsNewAsinAccepted
            },
            onSuccess: function(transport) {
                $('template_product_type_grid').update(transport.responseText);
                $('template_product_type_grid').show();
            }
        });
    },

    // ---------------------------------------

    createTemplateProductTypeInNewTab: function(stepWindowUrl)
    {
        var win = window.open(stepWindowUrl);

        var intervalId = setInterval(function() {
            if (!win.closed) {
                return;
            }

            clearInterval(intervalId);

            amazonTemplateProductTypeGridJsObject.reload();
        }, 1000);
    }

    // ---------------------------------------
});