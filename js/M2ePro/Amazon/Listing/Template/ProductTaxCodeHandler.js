AmazonListingTemplateProductTaxCodeHandler = Class.create(ActionHandler, {

    // ---------------------------------------

    openPopUp: function(productsIds)
    {
        var self = this;
        self.gridHandler.unselectAll();

        new Ajax.Request(M2ePro.url.viewProductTaxCodePopup, {
            method: 'post',
            parameters: {
                products_ids:  productsIds
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                if (!response.data) {
                    if (response.messages.length > 0) {
                        MagentoMessageObj.clearAll();
                        response.messages.each(function(msg) {
                            MagentoMessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                        });
                    }

                    return;
                }

                templateProductTaxCodePopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.text.templateProductTaxCodePopupTitle,
                    top: 70,
                    width: 800,
                    height: 550,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });
                templateProductTaxCodePopup.options.destroyOnClose = true;

                templateProductTaxCodePopup.productsIds = response.products_ids;

                $('modal_dialog_message').insert(response.data);

                var grid = $('template_productTaxCode_grid');

                grid.observe('click', function(event) {
                    if (!event.target.hasClassName('assign-productTaxCode-template')) {
                        return;
                    }

                    self.assign(event.target.getAttribute('templateProductTaxCodeId'));
                });

                grid.on('click', '.new-productTaxCode-template', function() {
                    self.createInNewTab(self.newTemplateUrl);
                });

                self.loadGrid();

                setTimeout(function() {
                    Windows.getFocusedWindow().content.style.height = '';
                    Windows.getFocusedWindow().content.style.maxHeight = '600px';
                }, 50);
            }
        });
    },

    // ---------------------------------------

    assign: function(templateId)
    {
        var self = this;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.assignProductTaxCode, {
            method: 'post',
            parameters: {
                products_ids: templateProductTaxCodePopup.productsIds,
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
                    MagentoMessageObj.clearAll();
                    response.messages.each(function(msg) {
                        MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](msg);
                    });
                }
            }
        });

        templateProductTaxCodePopup.close();
    },

    // ---------------------------------------
    unassign: function(productsIds)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.unassignProductTaxCode, {
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

    loadGrid: function() {

        new Ajax.Request(M2ePro.url.viewProductTaxCodeGrid, {
            method: 'post',
            parameters: {
                products_ids: templateProductTaxCodePopup.productsIds
            },
            onSuccess: function(transport) {
                var grid = $('template_productTaxCode_grid');
                grid.update(transport.responseText);
                grid.show();
            }
        });
    },

    // ---------------------------------------

    createInNewTab: function(stepWindowUrl)
    {
        var self = this;
        var win = window.open(stepWindowUrl);

        var intervalId = setInterval(function() {
            if (!win.closed) {
                return;
            }

            clearInterval(intervalId);

            self.loadGrid();
        }, 1000);
    }

    // ---------------------------------------
});