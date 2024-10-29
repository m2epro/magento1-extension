window.WalmartListingProductTypeGrid = Class.create(ListingGrid, {

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
        this.actionHandler = new WalmartListingAction(this);
        this.productType = new WalmartListingProductType(this);

        this.actions = Object.extend(this.actions, {

            duplicateAction: this.duplicateProducts.bind(this),

            setProductTypeAction: (function() {
                this.productType.validateProductsForProductTypeAssign(this.getSelectedProductsString(), null);
            }).bind(this),

            setProductTypeByCategoryAction: (function() {
                this.productType.validateProductsForProductTypeAssign(
                    this.getSelectedProductsStringFromCategory(),
                    this.getSelectedCategories()
                );
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
        MessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_walmart_listing/duplicateProducts'), {
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

    setProductTypeRowAction: function(id)
    {
        this.productType.validateProductsForProductTypeAssign(id, null);
    },

    // ---------------------------------------

    setProductTypeByCategoryRowAction: function(id)
    {
        this.productType.validateProductsForProductTypeAssign(this.getSelectedProductsStringFromCategory(id), id);
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

    mapToProductType: function(el, templateId)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.assignByMagentoCategorySaveProductType, {
            method: 'post',
            parameters: {
                template_id: templateId,
                magento_categories_ids: productTypePopup.magentoCategoriesIds
            },
            onSuccess: function(transport) {

                new Ajax.Request(M2ePro.url.mapToProductType, {
                    method: 'post',
                    parameters: {
                        products_ids: productTypePopup.productsIds,
                        template_id:  templateId
                    },
                    onSuccess: function(transport) {
                        if (!transport.responseText.isJSON()) {
                            alert(transport.responseText);
                            return;
                        }

                        self.productType.gridHandler.unselectAllAndReload();

                        var response = transport.responseText.evalJSON();

                        if (response.messages.length > 0) {
                            MessageObj.clearAll();
                            response.messages.each(function(msg) {
                                MessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](msg);
                            });
                        }
                    }
                });
            }
        });

        productTypePopup.close();
    },

    // ---------------------------------------

    completeCategoriesDataStep: function()
    {
        var self = this;

        new Ajax.Request(M2ePro.url.checkProductTypeProducts, {
            method: 'post',
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                if (response['validation']) {
                    return setLocation(M2ePro.url.checkProductTypeSucceed);
                }

                if (response['message']) {
                    MessageObj.clearAll();
                    return MessageObj.addError(response['message']);
                }

                this.nextStepWarningPopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Assign Product Type'),
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

    resetProductType: function()
    {
        setLocation(M2ePro.url.resetProductType);
    },

    // ---------------------------------------

    categoryNotSelectedWarningPopupContinueClick: function()
    {
        return setLocation(M2ePro.url.checkProductTypeSucceed);
    }

    // ---------------------------------------
});