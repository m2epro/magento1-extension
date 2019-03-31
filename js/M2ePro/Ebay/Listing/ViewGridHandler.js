EbayListingViewGridHandler = Class.create(ListingGridHandler, {

    // ---------------------------------------

    selectedProductsIds: [],
    selectedCategoriesData: {},

    // ---------------------------------------

    prepareActions: function($super)
    {
        this.actionHandler = new EbayListingActionHandler(this);

        this.actions = {
            listAction: this.actionHandler.listAction.bind(this.actionHandler),
            relistAction: this.actionHandler.relistAction.bind(this.actionHandler),
            reviseAction: this.actionHandler.reviseAction.bind(this.actionHandler),
            stopAction: this.actionHandler.stopAction.bind(this.actionHandler),
            stopAndRemoveAction: this.actionHandler.stopAndRemoveAction.bind(this.actionHandler),
            previewItemsAction: this.actionHandler.previewItemsAction.bind(this.actionHandler),
            startTranslateAction: this.actionHandler.startTranslateAction.bind(this.actionHandler),
            stopTranslateAction: this.actionHandler.stopTranslateAction.bind(this.actionHandler)
        };

        this.variationProductManageHandler = new EbayListingVariationProductManageHandler(this);
        this.listingProductBidsHandler = new EbayListingProductBidsHandler(this);

        this.actions = Object.extend(this.actions, {

            editCategorySettingsAction: function(id) {
                this.editCategorySettings(id);
            }.bind(this)

        });

    },

    massActionSubmitClick: function($super)
    {
        if (this.getSelectedProductsString() == '' || this.getSelectedProductsArray().length == 0) {
            alert(M2ePro.translator.translate('Please select the Products you want to perform the Action on.'));
            return;
        }
        $super();
    },

    // ---------------------------------------

    editCategorySettings: function(id)
    {
        this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing/getCategoryChooserHtml'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                ids: this.selectedProductsIds.join(',')
            },
            onSuccess: function(transport) {

                this.unselectAll();

                var title = M2ePro.translator.translate('eBay Categories');

                if (this.selectedProductsIds.length == 1) {
                    var productName = this.getProductNameByRowId(this.selectedProductsIds[0]);
                    title += '&nbsp;' + M2ePro.translator.translate('of Product') + '&nbsp;"' + productName + '"';
                }

                this.openPopUp(title, transport.responseText);

                $('cancel_button').observe('click', function() { Windows.getFocusedWindow().close(); });

                $('done_button').observe('click', function() {
                    if (!EbayListingCategoryChooserHandlerObj.validate()) {
                        return;
                    }

                    this.selectedCategoriesData = EbayListingCategoryChooserHandlerObj.getInternalData();
                    this.editSpecificSettings();
                }.bind(this));
            }.bind(this)
        });
    },

    // ---------------------------------------

    editSpecificSettings: function()
    {
        var typeEbayMain = M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN');

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing/getCategorySpecificHtml'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                ids: this.selectedProductsIds.join(','),
                category_mode: EbayListingCategoryChooserHandlerObj.getSelectedCategory(typeEbayMain)['mode'],
                category_value: EbayListingCategoryChooserHandlerObj.getSelectedCategory(typeEbayMain)['value']
            },
            onSuccess: function(transport) {

                var title = M2ePro.translator.translate('Specifics');

                this.openPopUp(title, transport.responseText);

                $('cancel_button').observe('click', function() { Windows.getFocusedWindow().close(); });
                $('done_button').observe('click', this.saveCategoryTemplate.bind(this));
            }.bind(this)
        });
    },

    // ---------------------------------------

    saveCategoryTemplate: function()
    {
        if (!EbayListingCategorySpecificHandlerObj.validate()) {
            return;
        }

        var categoryTemplateData = {};
        categoryTemplateData = Object.extend(categoryTemplateData, this.selectedCategoriesData);
        categoryTemplateData = Object.extend(categoryTemplateData, EbayListingCategorySpecificHandlerObj.getInternalData());

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing/saveCategoryTemplate'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                ids: this.selectedProductsIds.join(','),
                template_category_data: Object.toJSON(categoryTemplateData)
            },
            onSuccess: function(transport) {
                Windows.getFocusedWindow().close();
                this.getGridObj().doFilter();
            }.bind(this)
        });
    },

    // ---------------------------------------

    getComponent: function()
    {
        return 'ebay';
    },

    // ---------------------------------------

    openPopUp: function(title, content, params)
    {
        var self = this;
        params = params || {};

        var config = Object.extend({
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            top: 50,
            maxHeight: 500,
            height: 500,
            width: 1000,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() {
                self.selectedProductsIds = [];
                self.selectedCategoriesData = {};

                $('excludeListPopup') && Windows.getWindow('excludeListPopup').destroy();

                self.getGridObj().reload();

                return true;
            }
        }, params);

        try {
            if (!Windows.getFocusedWindow() || !$('modal_dialog_message')) {
                Dialog.info(null, config);
            }
            Windows.getFocusedWindow().setTitle(title);
            $('modal_dialog_message').innerHTML = content;
            $('modal_dialog_message').innerHTML.evalScripts();
        } catch (ignored) {}
    }

    // ---------------------------------------
});