EbayListingCategoryCategoryGridHandler = Class.create(EbayListingCategoryGridHandler, {

    //----------------------------------

    editCategories: function()
    {
        var url = M2ePro.url.get(
            'adminhtml_ebay_listing_categorySettings/getChooserBlockHtml'
        );

        new Ajax.Request(url, {
            method: 'get',
            parameters: {
                ids: this.getSelectedProductsString()
            },
            onSuccess: function(transport) {
                this.openPopUp(M2ePro.translator.translate('Set eBay Categories'), transport.responseText);
            }.bind(this)
        });
    },

    //----------------------------------

    editPrimaryCategories: function()
    {
        this.editCategoriesByType(
            M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN'),
            true
        )
    },

    editStorePrimaryCategories: function()
    {
        this.editCategoriesByType(
            M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_MAIN'),
            false
        )
    },

    //----------------------------------

    openPopUp: function(title, content)
    {
        var config = {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 100,
            maxHeight: 500,
            height: 310,
            width: 700,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show
        };

        this.popUp = Dialog.info(content, config);

        $('modal_dialog_message').innerHTML.evalScripts();

        $('done_button').observe('click', function() {

            if (!EbayListingCategoryChooserHandlerObj.validate()) {
                return;
            }

            this.saveCategoriesData(EbayListingCategoryChooserHandlerObj.getInternalData());

        }.bind(this));

        $('cancel_button').observe('click', function() {
            this.popUp.close();
            this.unselectAll();
        }.bind(this));
    },

    //----------------------------------

    validate: function()
    {
        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepTwoModeCategoryValidate'), {
            method: 'post',
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();

                if (response.validation == true) {
                    setLocation(M2ePro.url.get('adminhtml_ebay_listing_categorySettings'));
                } else {
                    MagentoMessageObj.addError(response.message);
                }

            }.bind(this)
        });
    },

    //----------------------------------

    confirm: function()
    {
        return true;
    }

    //----------------------------------
});