window.EbayListingCategory = Class.create(Common, {

    // ---------------------------------------

    gridObj: null,
    selectedProductsIds: [],

    // ---------------------------------------

    initialize: function(gridObj)
    {
        this.gridObj = gridObj;
    },

    // ---------------------------------------

    editCategorySettings: function(id, categoryMode)
    {
        this.selectedProductsIds = id ? [id] : this.gridObj.getSelectedProductsArray();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing/getCategoryChooserHtml'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                ids            : this.selectedProductsIds.join(','),
                account_id     : this.gridObj.accountId,
                marketplace_id : this.gridObj.marketplaceId,
                category_mode  : categoryMode,
            },
            onSuccess: function(transport) {
                this.openPopUp(M2ePro.translator.translate('Category Settings'), transport.responseText);
            }.bind(this)
        });
    },

    saveCategorySettings: function()
    {
        var editForm = new varienForm('edit_form');
        if (!editForm.validate()) {
            return;
        }

        var selectedCategories = EbayTemplateCategoryChooserObj.selectedCategories;
        var typeMain = M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN');
        if (typeof selectedCategories[typeMain] !== 'undefined') {
            selectedCategories[typeMain]['specific'] = EbayTemplateCategoryChooserObj.selectedSpecifics;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing/saveCategoryTemplate'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                ids                    : this.selectedProductsIds.join(','),
                account_id             : this.gridObj.accountId,
                marketplace_id         : this.gridObj.marketplaceId,
                template_category_data : Object.toJSON(selectedCategories)
            },
            onSuccess: function(transport) {
                this.cancelCategorySettings();
            }.bind(this)
        });
    },

    cancelCategorySettings: function()
    {
        Windows.getFocusedWindow().close();
        this.gridObj.unselectAllAndReload();
    },

    // ---------------------------------------

    openPopUp: function(title, content, params)
    {
        params = params || {};

        var popup = Dialog.info(null, Object.extend({
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 50,
            maxHeight: 500,
            height: 500,
            width: 1000,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show
        }, params));

        popup.options.destroyOnClose = true;

        $('modal_dialog_message').innerHTML = content;
        $('modal_dialog_message').innerHTML.evalScripts();
    },

    //----------------------------------------

    modeSameSubmitData: function(url)
    {
        var editForm = new varienForm('edit_form');
        if (!editForm.validate()) {
            return;
        }

        var selectedCategories = EbayTemplateCategoryChooserObj.selectedCategories;
        var typeMain = M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN');
        if (typeof selectedCategories[typeMain] !== 'undefined') {
            selectedCategories[typeMain]['specific'] = EbayTemplateCategoryChooserObj.selectedSpecifics;
        }

        this.postForm(url, {category_data: Object.toJSON(selectedCategories)});
    }

    // ---------------------------------------
});