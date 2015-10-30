CommonAmazonFulfillmentHandler = Class.create(ActionHandler, {

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

    switchToAFN: function(productsIds)
    {
        var self = this;
        self.gridHandler.unselectAll();

        new Ajax.Request(self.options.url.switchToAFN, {
            method: 'post',
            parameters: {
                products_ids: productsIds
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

        new Ajax.Request(self.options.url.switchToMFN, {
            method: 'post',
            parameters: {
                products_ids: productsIds
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