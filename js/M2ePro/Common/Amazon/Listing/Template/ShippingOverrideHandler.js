CommonAmazonListingTemplateShippingOverrideHandler = Class.create(ActionHandler, {

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

    openPopUp: function(productsIds)
    {
        var self = this;
        self.gridHandler.unselectAll();

        new Ajax.Request(self.options.url.viewTemplateShippingOverridePopup, {
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

                if (!response.data) {
                    if (response.messages.length > 0) {
                        MagentoMessageObj.clearAll();
                        response.messages.each(function(msg) {
                            MagentoMessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                        });
                    }

                    return;
                }

                templateShippingOverridePopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: self.options.text.templateShippingOverridePopupTitle,
                    top: 70,
                    width: 800,
                    height: 550,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });
                templateShippingOverridePopup.options.destroyOnClose = true;

                templateShippingOverridePopup.productsIds = response.products_ids;

                $('modal_dialog_message').insert(response.data);

                $('template_shippingOverride_grid').observe('click', function(event) {
                    if (!event.target.hasClassName('assign-shipping-override-template')) {
                        return;
                    }

                    self.assign(event.target.getAttribute('templateShippingOverrideId'));
                });

                $('template_shippingOverride_grid').on('click', '.new-shipping-override-template', function() {
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

    loadGrid: function() {

        var self = this;

        new Ajax.Request(self.options.url.viewTemplateShippingOverrideGrid, {
            method: 'post',
            parameters: {
                products_ids: templateShippingOverridePopup.productsIds
            },
            onSuccess: function(transport) {
                $('template_shippingOverride_grid').update(transport.responseText);
                $('template_shippingOverride_grid').show();
            }
        });
    },

    // ---------------------------------------

    assign: function (templateId)
    {
        var self = this;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(self.options.url.assignShippingOverrideTemplate, {
            method: 'post',
            parameters: {
                products_ids: templateShippingOverridePopup.productsIds,
                template_id: templateId
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

        templateShippingOverridePopup.close();
    },

    // ---------------------------------------

    unassign: function (productsIds)
    {
        var self = this;

        new Ajax.Request(self.options.url.unassignShippingOverrideTemplate, {
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

    createInNewTab: function(url)
    {
        var self = this;
        var win = window.open(url);

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