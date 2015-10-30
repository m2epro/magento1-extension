CommonAmazonListingNewAsinTemplateDescriptionGridHandler = Class.create(CommonListingGridHandler, {

    // ---------------------------------------

    getComponent: function()
    {
        return 'amazon';
    },

    // ---------------------------------------

    getMaxProductsInPart: function()
    {
        return 1000;
    },

    // ---------------------------------------

    prepareActions: function($super)
    {
        $super();
        this.actionHandler = new CommonAmazonListingActionHandler(this);
        this.templateDescriptionHandler = new CommonAmazonListingTemplateDescriptionHandler(this);

        this.actions = Object.extend(this.actions, {

            setDescriptionTemplateAction: (function() { this.mapToNewAsin(this.getSelectedProductsString())}).bind(this),
            resetDescriptionTemplateAction: (function() { this.unmapFromNewAsin(this.getSelectedProductsString())}).bind(this),

            setDescriptionTemplateByCategoryAction: (function() { this.mapToNewAsin(this.getSelectedProductsStringFromCategory())}).bind(this),
            resetDescriptionTemplateByCategoryAction: (function() { this.unmapFromNewAsin(this.getSelectedProductsStringFromCategory())}).bind(this)
        });
    },

    // ---------------------------------------

    parseResponse: function(response)
    {
        if (!response.responseText.isJSON()) {
            return;
        }

        return response.responseText.evalJSON();
    },

    // ---------------------------------------

    afterInitPage: function($super)
    {
        $super();
    },

    setDescriptionTemplateRowAction: function(id)
    {
        this.mapToNewAsin(id);
    },

    resetDescriptionTemplateRowAction: function(id)
    {
        this.unmapFromNewAsin(id);
    },

    // ---------------------------------------

    setDescriptionTemplateByCategoryRowAction: function(id)
    {
        this.mapToNewAsin(this.getSelectedProductsStringFromCategory(id));
    },

    resetDescriptionTemplateByCategoryRowAction: function(id)
    {
        this.unmapFromNewAsin(this.getSelectedProductsStringFromCategory(id));
    },

    // ---------------------------------------

    getSelectedProductsStringFromCategory: function(categoryIds)
    {
        var productsIdsStr = '';

        categoryIds = categoryIds || this.getGridMassActionObj().checkedString;
        categoryIds = explode(',', categoryIds);

        categoryIds.each(function(categoryId) {

            if (productsIdsStr != '') {
                productsIdsStr += ',';
            }
            productsIdsStr += $('products_ids_' + categoryId).value;
        });

        return productsIdsStr;
    },

    // ---------------------------------------

    mapToNewAsin: function(listingProductIds)
    {
        var self = this;
        new Ajax.Request(M2ePro.url.mapToNewAsin, {
            method: 'post',
            parameters: {
                products_ids: listingProductIds
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                self.templateDescriptionHandler.gridHandler.unselectAllAndReload();

                if(response.products_ids.length > 0) {
                    ListingGridHandlerObj.templateDescriptionHandler.openPopUp(
                        0, M2ePro.text.templateDescriptionPopupTitle,
                        response.products_ids, response.data, 1);
                } else {
                    if(response.messages.length > 0) {
                        MagentoMessageObj.clearAll();
                        response.messages.each(function(msg) {
                            MagentoMessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                        });
                    }
                }
            }
        });
    },

    unmapFromNewAsin: function(productsIds)
    {
        var self = this;

        self.templateDescriptionHandler.gridHandler.unselectAll();

        new Ajax.Request(M2ePro.url.unmapFromNewAsin, {
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

                if (response.type == 'success') {
                    self.templateDescriptionHandler.unassignFromTemplateDescrition(productsIds);
                }
            }
        });
    },

    mapToTemplateDescription: function(el, templateId, mapToGeneralId)
    {
        var self = this;

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
                self.mapToNewAsin(response.products_ids);
            }
        });

        templateDescriptionPopup.close();
    },

    checkCategoryProducts: function(url)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.checkNewAsinCategoryProducts, {
            method: 'post',
            onSuccess: function(transport) {

                if (transport.responseText == 1) {
                    setLocation(url);
                } else {
                    if (!transport.responseText.isJSON()) {
                        alert(transport.responseText);
                        return;
                    }

                    var response = transport.responseText.evalJSON();

                    MagentoMessageObj.clearAll();
                    MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.text);
                }
            }
        });
    },

    checkManualProducts: function(url)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.checkNewAsinManualProducts, {
            method: 'post',
            onSuccess: function(transport) {

                if (transport.responseText == 1) {
                    setLocation(url);
                } else {
                    if (!transport.responseText.isJSON()) {
                        alert(transport.responseText);
                        return;
                    }

                    var response = transport.responseText.evalJSON();

                    nextStepWarningPopup = Dialog.info(response.html, {
                        draggable: true,
                        resizable: true,
                        closable: true,
                        className: "magento",
                        windowClassName: "popup-window",
                        title: M2ePro.text.setDescriptionPolicy,
                        width: 430,
                        height: 250,
                        zIndex: 100,
                        hideEffect: Element.hide,
                        showEffect: Element.show
                    });

                    nextStepWarningPopup.options.destroyOnClose = true;

                    $('total_count').innerHTML = response.total_count;
                    $('failed_count').update(response.failed_count);
                }
            }
        });
    }

    // ---------------------------------------
});