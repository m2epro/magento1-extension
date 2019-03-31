WalmartListingTemplateCategoryHandler = Class.create(ActionHandler, {

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

    mapToTemplateCategory: function(el, templateId)
    {
        var self = this;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(self.options.url.mapToTemplateCategory, {
            method: 'post',
            parameters: {
                products_ids: templateCategoryPopup.productsIds,
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

        templateCategoryPopup.close();
    },

    // ---------------------------------------

    validateProductsForTemplateCategoryAssign: function(productsIds, magentoCategoriesIds)
    {
        var self = this;
        self.flagSuccess = false;

        new Ajax.Request(self.options.url.validateProductsForTemplateCategoryAssign, {
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

                self.openPopUp(0,self.options.text.templateCategoryPopupTitle, response.products_ids, magentoCategoriesIds, response.data);
            }
        });
    },

    // ---------------------------------------

    openPopUp: function(mode, title, productsIds, magentoCategoriesIds, contentData)
    {
        var self = this;
        self.gridHandler.unselectAll();

        MagentoMessageObj.clearAll();

        templateCategoryPopup = Dialog.info(null, {
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
        templateCategoryPopup.options.destroyOnClose = true;

        templateCategoryPopup.productsIds = productsIds;
        templateCategoryPopup.magentoCategoriesIds = magentoCategoriesIds;

        $('modal_dialog_message').insert(contentData);

        self.loadTemplateCategoryGrid();

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '600px';
        }, 50);
    },

    loadTemplateCategoryGrid: function() {

        new Ajax.Request(this.options.url.viewTemplateCategoriesGrid, {
            method: 'post',
            parameters: {
                products_ids: templateCategoryPopup.productsIds,
                magento_categories_ids: templateCategoryPopup.magentoCategoriesIds
            },
            onSuccess: function(transport) {
                $('template_category_grid').update(transport.responseText);
                $('template_category_grid').show();
            }
        });
    },

    // ---------------------------------------

    createTemplateCategoryInNewTab: function(stepWindowUrl)
    {
        var self = this;
        var win = window.open(stepWindowUrl);

        var intervalId = setInterval(function() {
            if (!win.closed) {
                return;
            }

            clearInterval(intervalId);

            walmartTemplateCategoryGridJsObject.reload();
        }, 1000);
    }

    // ---------------------------------------
});