CategoryTemplateGridHandler = Class.create(ListingGridHandler, {

    // ---------------------------------------

    getLogViewUrl: function(rowId)
    {
        return M2ePro.url.get('adminhtml_walmart_log/listingProduct', {
            listing_product_id: rowId
        });
    },

    // ---------------------------------------

    getComponent: function()
    {
        return 'walmart';
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
        this.actionHandler = new WalmartListingActionHandler(this);
        this.templateCategoryHandler = new WalmartListingTemplateCategoryHandler(this);

        this.actions = Object.extend(this.actions, {

            duplicateAction: this.duplicateProducts.bind(this),

            setCategoryTemplateAction: (function() {
                this.templateCategoryHandler.validateProductsForTemplateCategoryAssign(this.getSelectedProductsString(), null);
            }).bind(this),

            setCategoryTemplateByCategoryAction: (function() {
                this.templateCategoryHandler.validateProductsForTemplateCategoryAssign(this.getSelectedProductsStringFromCategory(), this.getSelectedCategories());
            }).bind(this)
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
        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_walmart_listing/duplicateProducts'), {
            method: 'post',
            parameters: {
                component: this.getComponent(),
                ids: this.getSelectedProductsString()
            },
            onSuccess: (function(transport) {

                try {
                    var response = transport.responseText.evalJSON();

                    MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.message);

                    if (response.type != 'error') {
                        this.unselectAllAndReload();
                    }

                } catch (e) {
                    MagentoMessageObj.addError('Internal Error.');
                }
            }).bind(this)
        });
    },

    // ---------------------------------------

    afterInitPage: function($super)
    {
        $super();
    },

    setCategoryTemplateRowAction: function(id)
    {
        this.templateCategoryHandler.validateProductsForTemplateCategoryAssign(id, null);
    },

    // ---------------------------------------

    setCategoryTemplateByCategoryRowAction: function(id)
    {
        this.templateCategoryHandler.validateProductsForTemplateCategoryAssign(this.getSelectedProductsStringFromCategory(id), id);
    },

    // ---------------------------------------

    getSelectedCategories: function(categoryIds)
    {
        return categoryIds || this.getGridMassActionObj().checkedString;
    },

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

    // ---------------------------------------

    mapToTemplateCategory: function(el, templateId)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.assignByMagentoCategorySaveCategory, {
            method: 'post',
            parameters: {
                template_id: templateId,
                magento_categories_ids: templateCategoryPopup.magentoCategoriesIds
            },
            onSuccess: function(transport) {

                new Ajax.Request(M2ePro.url.mapToTemplateCategory, {
                    method: 'post',
                    parameters: {
                        products_ids: templateCategoryPopup.productsIds,
                        template_id:  templateId
                    },
                    onSuccess: function(transport) {
                        if (!transport.responseText.isJSON()) {
                            alert(transport.responseText);
                            return;
                        }

                        self.templateCategoryHandler.gridHandler.unselectAllAndReload();

                        var response = transport.responseText.evalJSON();

                        if (response.messages.length > 0) {
                            MagentoMessageObj.clearAll();
                            response.messages.each(function(msg) {
                                MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](msg);
                            });
                        }
                    }
                });
            }
        });

        templateCategoryPopup.close();
    },

    // ---------------------------------------

    completeCategoriesDataStep: function()
    {
        var self = this;

        new Ajax.Request(M2ePro.url.checkCategoryTemplateProducts, {
            method: 'post',
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                if (response['validation']) {
                    return setLocation(M2ePro.url.checkCategoryTemplateSucceed);
                }

                if (response['message']) {
                    MagentoMessageObj.clearAll();
                    return MagentoMessageObj.addError(response['message']);
                }

                this.nextStepWarningPopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Assign Category Policy'),
                    width: 430,
                    minHeight: 200,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });

                this.nextStepWarningPopup.options.destroyOnClose = false;
                $('modal_dialog_message').insert($('next_step_warning_popup_content').show());

                $('next_step_warning_popup_content').select('span.total_count').each(function(el){
                    $(el).update(response['total_count']);
                });

                $('next_step_warning_popup_content').select('span.failed_count').each(function(el){
                    $(el).update(response['failed_count']);
                });

                self.autoHeightFix();

            }.bind(this)
        });
    },

    // ---------------------------------------

    resetCategoryTemplate: function()
    {
        setLocation(M2ePro.url.resetCategoryTemplate);
    },

    // ---------------------------------------

    categoryNotSelectedWarningPopupContinueClick: function()
    {
        return setLocation(M2ePro.url.checkCategoryTemplateSucceed);
    }

    // ---------------------------------------
});