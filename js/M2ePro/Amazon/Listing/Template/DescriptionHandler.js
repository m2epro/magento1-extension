AmazonListingTemplateDescriptionHandler = Class.create(ActionHandler, {

    // ---------------------------------------

    mapToTemplateDescription: function(el, templateId, mapToGeneralId)
    {
        var self = this;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.mapToTemplateDescription, {
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
                    ListingGridHandlerObj.productSearchHandler.addNewGeneralId(response.products_ids);
                } else {
                    self.gridHandler.unselectAllAndReload();

                    if (response.messages.length > 0) {
                        MagentoMessageObj.clearAll();
                        response.messages.each(function(msg) {
                            MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](msg);
                        });
                    }
                }
            }
        });

        templateDescriptionPopup.close();
    },

    // ---------------------------------------
    unassignFromTemplateDescription: function(productsIds)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.unmapFromTemplateDescription, {
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

                MagentoMessageObj.clearAll();
                response.messages.each(function(msg) {
                    MagentoMessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                });
            }
        });
    },

    // ---------------------------------------

    validateProductsForTemplateDescriptionAssign: function(productsIds)
    {
        var self = this;
        self.flagSuccess = false;

        productsIds = productsIds || ListingGridHandlerObj.productSearchHandler.params.productId;

        new Ajax.Request(M2ePro.url.validateProductsForTemplateDescriptionAssign, {
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
                    MagentoMessageObj.clearAll();
                    response.messages.each(function(msg) {
                        MagentoMessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                    });
                }

                if (!response.data) {
                    return;
                }

                if (typeof popUp != 'undefined') {
                    popUp.close();
                }

                self.openPopUp(0,M2ePro.text.templateDescriptionPopupTitle, response.products_ids, null, response.data);
            }
        });
    },

    // ---------------------------------------

    openPopUp: function(mode, title, productsIds, magentoCategoriesIds, contentData, checkIsNewAsinAccepted)
    {
        var self = this;
        self.gridHandler.unselectAll();

        MagentoMessageObj.clearAll();

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

        self.loadTemplateDescriptionGrid();

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '600px';
        }, 50);
    },

    loadTemplateDescriptionGrid: function() {

        new Ajax.Request(M2ePro.url.viewTemplateDescriptionsGrid, {
            method: 'post',
            parameters: {
                products_ids: templateDescriptionPopup.productsIds,
                magento_categories_ids: templateDescriptionPopup.magentoCategoriesIds,
                check_is_new_asin_accepted: templateDescriptionPopup.checkIsNewAsinAccepted
            },
            onSuccess: function(transport) {
                $('template_description_grid').update(transport.responseText);
                $('template_description_grid').show();
            }
        });
    },

    // ---------------------------------------

    createTemplateDescriptionInNewTab: function(stepWindowUrl)
    {
        var win = window.open(stepWindowUrl);

        var intervalId = setInterval(function() {
            if (!win.closed) {
                return;
            }

            clearInterval(intervalId);

            amazonTemplateDescriptionGridJsObject.reload();
        }, 1000);
    }

    // ---------------------------------------
});