AmazonRepricingHandler = Class.create(ActionHandler, {

    // ---------------------------------------

    initialize: function ($super, gridHandler) {
        var self = this;
        $super(gridHandler);
    },

    // ---------------------------------------

    options: {},

    setOptions: function (options) {
        this.options = Object.extend(this.options, options);
        return this;
    },

    // ---------------------------------------

    openManagement: function () {
        window.open(M2ePro.url.get('adminhtml_amazon_listing_repricing/openManagement'));
    },

    // ---------------------------------------

    addToRepricing: function (productsIds)
    {
        var self = this;
        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_amazon_listing_repricing/validateProductsBeforeAdd'), {
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

                if(response.products_ids.length === 0) {
                    MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.message);
                    return;
                }

                if (response.products_ids.length === productsIds.split(',').length) {
                    self.addToRepricingConfirm(productsIds);
                    return;
                }

                priceWarningPopUp = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: response.title,
                    top: 150,
                    width: 400,
                    height: 220,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });
                priceWarningPopUp.options.destroyOnClose = true;

                $('modal_dialog_message').update(response.html);

                $('modal_dialog_message').down('.confirm-action').observe('click', function () {
                   self.addToRepricingConfirm(productsIds);
                });

                setTimeout(function() {
                    Windows.getFocusedWindow().content.style.height = '';
                    Windows.getFocusedWindow().content.style.maxHeight = '630px';
                }, 50);
            }
        });
    },

    addToRepricingConfirm: function (productsIds) {
        return this.postForm(M2ePro.url.get('adminhtml_amazon_listing_repricing/openAddProducts'), {'products_ids': productsIds});
    },

    showDetails: function (productsIds)
    {
        return this.postForm(M2ePro.url.get('adminhtml_amazon_listing_repricing/openShowDetails'), {'products_ids': productsIds});
    },

    editRepricing: function (productsIds)
    {
        return this.postForm(M2ePro.url.get('adminhtml_amazon_listing_repricing/openEditProducts'), {'products_ids': productsIds});
    },

    removeFromRepricing: function (productsIds)
    {
        return this.postForm(M2ePro.url.get('adminhtml_amazon_listing_repricing/openRemoveProducts'), {'products_ids': productsIds});
    }

    // ---------------------------------------
});