AmazonFulfillmentHandler = Class.create(ActionHandler, {

    // ---------------------------------------

    switchToAFN: function(productsIds)
    {
        var self = this;
        self.gridHandler.unselectAll();

        new Ajax.Request(M2ePro.url.switchToAFN, {
            method: 'post',
            parameters: {
                selected_products: productsIds
            },
            onSuccess: function (transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                self.gridHandler.unselectAllAndReload();

                MagentoMessageObj.clearAll();
                response.messages.each(function(msg) {
                    MagentoMessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                });
            }
        });
    },

    // ---------------------------------------

    switchToMFN: function(productsIds)
    {
        var self = this;
        self.gridHandler.unselectAll();

        new Ajax.Request(M2ePro.url.switchToMFN, {
            method: 'post',
            parameters: {
                selected_products: productsIds
            },
            onSuccess: function (transport) {
                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                self.gridHandler.unselectAllAndReload();

                MagentoMessageObj.clearAll();
                response.messages.each(function(msg) {
                    MagentoMessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                });
            }
        });
    }
});