window.AmazonListingNewAsinTemplateProductTypeGrid = Class.create(ListingGrid, {

    // ---------------------------------------

    getLogViewUrl: function(rowId)
    {
        return M2ePro.url.get('adminhtml_amazon_log/listingProduct', {
            listing_product_id: rowId
        });
    },

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
        this.actionHandler = new AmazonListingAction(this);
        this.templateProductType = new AmazonListingTemplateProductType(this);

        this.actions = Object.extend(this.actions, {

            duplicateAction: this.duplicateProducts.bind(this),

            setProductTypeTemplateAction: (function() { this.mapToNewAsin(this.getSelectedProductsString(), null)}).bind(this),
            resetProductTypeTemplateAction: (function() { this.unmapFromNewAsin(this.getSelectedProductsString(), null)}).bind(this),

            setProductTypeTemplateByCategoryAction: (function() { this.mapToNewAsin(this.getSelectedProductsStringFromCategory(), this.getSelectedCategories())}).bind(this),
            resetProductTypeTemplateByCategoryAction: (function() { this.unmapFromNewAsin(this.getSelectedProductsStringFromCategory(), this.getSelectedCategories())}).bind(this)
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

    duplicateProducts: function()
    {
        this.scroll_page_to_top();
        MessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_amazon_listing/duplicateProducts'), {
            method: 'post',
            parameters: {
                component: this.getComponent(),
                ids: this.getSelectedProductsString()
            },
            onSuccess: (function(transport) {

                try {
                    var response = transport.responseText.evalJSON();

                    MessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.message);

                    if (response.type != 'error') {
                        this.unselectAllAndReload();
                    }

                } catch (e) {
                    MessageObj.addError('Internal Error.');
                }
            }).bind(this)
        });
    },

    // ---------------------------------------

    afterInitPage: function($super)
    {
        $super();
    },

    setProductTypeTemplateRowAction: function(id)
    {
        this.mapToNewAsin(id, null);
    },

    resetProductTypeTemplateRowAction: function(id)
    {
        this.unmapFromNewAsin(id, null);
    },

    // ---------------------------------------

    setProductTypeTemplateByCategoryRowAction: function(id)
    {
        this.mapToNewAsin(this.getSelectedProductsStringFromCategory(id), id);
    },

    resetProductTypeTemplateByCategoryRowAction: function(id)
    {
        this.unmapFromNewAsin(this.getSelectedProductsStringFromCategory(id), id);
    },

    // ---------------------------------------

    getSelectedProductsStringFromCategory: function(categoryIds)
    {
        var productsIds = [];

        categoryIds = categoryIds || this.getGridMassActionObj().checkedString;
        categoryIds = explode(',', categoryIds);

        categoryIds.each(function(categoryId) {

            var products = $('products_ids_' + categoryId).value;
            if (products !== '') {
                products.split(',').each(function(productId) {
                    if (productsIds.indexOf(productId) === -1) productsIds.push(productId);
                });
            }
        });

        return productsIds.join(',');
    },

    getSelectedCategories: function(categoryIds)
    {
        return categoryIds || this.getGridMassActionObj().checkedString;
    },

    // ---------------------------------------

    //just open popup action
    mapToNewAsin: function(listingProductIds, magentoCategoriesIds)
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

                self.templateProductType.gridHandler.unselectAllAndReload();

                if(response.products_ids.length > 0) {
                    ListingGridObj.templateProductType.openPopUp(
                        0, M2ePro.text.productTypePopupTitle,
                        response.products_ids, magentoCategoriesIds, response.data, 1
                    );
                } else {
                    if(response.messages.length > 0) {
                        MessageObj.clearAll();
                        response.messages.each(function(msg) {
                            MessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                        });
                    }
                }
            }
        });
    },

    unmapFromNewAsin: function(productsIds, magentoCategoriesIds)
    {
        var self = this;

        self.templateProductType.gridHandler.unselectAll();

        new Ajax.Request(M2ePro.url.assignByMagentoCategoryDeleteCategory, {
            method: 'post',
            parameters: {
                magento_categories_ids: magentoCategoriesIds
            },
            onSuccess: function (transport) {

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
                            self.templateProductType.unassignFromTemplateProductType(productsIds);
                        }
                    }
                });
            }
        });
    },

    mapToTemplateProductType: function(el, templateId, mapToGeneralId)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.assignByMagentoCategorySaveCategory, {
            method: 'post',
            parameters: {
                template_id: templateId,
                magento_categories_ids: templateDescriptionPopup.magentoCategoriesIds
            },
            onSuccess: function (transport) {

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
                        self.mapToNewAsin(response.products_ids);
                    }
                });
            }
        });

        templateDescriptionPopup.close();
    },

    checkProducts: function(url)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.checkNewAsinProducts, {
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
                        title: M2ePro.text.productTypePopupTitle,
                        width: 430,
                        height: 250,
                        zIndex: 100,
                        hideEffect: Element.hide,
                        showEffect: Element.show
                    });

                    nextStepWarningPopup.options.destroyOnClose = true;

                    $$('.total_count').each(function(el){ el.update(response.total_count); });
                    $$('.failed_count').each(function(el){ el.update(response.failed_count); });

                    self.autoHeightFix();
                }
            }
        });
    }

    // ---------------------------------------
});