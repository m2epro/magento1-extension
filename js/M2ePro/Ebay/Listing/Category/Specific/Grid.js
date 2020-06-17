window.EbayListingCategorySpecificGrid = Class.create(Grid, {

    categoriesData: {},
    marketplaceId: null,
    selectedCategoryHash: null,

    // ---------------------------------------

    prepareActions: function()
    {
        this.actions = {
            editSpecificsAction: function(categoryHash) {
                this.editSpecifics(categoryHash);
            }.bind(this)
        };
    },

    editSpecifics: function(categoryHash)
    {
        var typeMain = M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN'),
            selectedCategory = this.categoriesData[categoryHash][typeMain];

        var specifics = {};
        if (typeof selectedCategory['specific'] !== 'undefined') {
            specifics = selectedCategory['specific'];
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_category/getCategorySpecificHtml'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                marketplace_id     : this.marketplaceId,
                selected_specifics : Object.toJSON(specifics),
                template_id        : selectedCategory['template_id'],
                category_mode      : selectedCategory['mode'],
                category_value     : selectedCategory['value']
            },
            onSuccess: function(transport) {
                this.selectedCategoryHash = categoryHash;
                this.openPopUp(transport.responseText, categoryHash);
            }.bind(this)
        });
    },

    openPopUp: function(html, categoryId)
    {
        var self = EbayListingCategorySpecificGridObj;

        var popup = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Specifics'),
            top: 100,
            maxHeight: 500,
            height: 500,
            width: 1000,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() {
                self.getGridObj().reload();
                self.selectedCategoryHash = null;
                return true;
            }
        });

        popup.options.destroyOnClose = true;

        $('modal_dialog_message').insert(html);
        $('modal_dialog_message').innerHTML.evalScripts();

        var button = $('ebay_specifics_edit_save_btn');
        button.removeAttribute('onclick');
        button.stopObserving('click');
        button.observe('click', self.confirmSpecifics.bind(self));
    },

    // ---------------------------------------

    confirmSpecifics: function()
    {
        var editForm = new varienForm('edit_form');
        if (!editForm.validate()) {
            return;
        }

        var self = EbayListingCategorySpecificGridObj,
            typeMain = M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN'),
            selectedCategory = this.categoriesData[this.selectedCategoryHash][typeMain];

        selectedCategory['specific'] = EbayTemplateCategorySpecificsObj.collectSpecifics();

        new Promise(function (resolve, reject) {
            new Ajax.Request(M2ePro.url.get('adminhtml_ebay_category/getSelectedCategoryDetails'), {
                method: 'post',
                parameters: {
                    marketplace_id : self.marketplaceId,
                    account_id     : null,
                    value          : selectedCategory['value'],
                    mode           : selectedCategory['mode'],
                    category_type  : typeMain
                },
                onSuccess: function(transport) {

                    var response = transport.responseText.evalJSON();

                    if (response.is_custom_template === null) {
                        selectedCategory.template_id = null;
                        selectedCategory.is_custom_template = '0';
                    } else {
                        selectedCategory.template_id = null;
                        selectedCategory.is_custom_template = '1';
                    }

                    return resolve();
                }
            });
        })
        .then(function() {
            var templateData = {};
            templateData[typeMain] = selectedCategory;

            return new Promise(function (resolve, reject) {
                new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepTwoSaveToSession'), {
                    method: 'post',
                    parameters: {
                        ids           : self.categoriesData[self.selectedCategoryHash]['listing_products_ids'].join(','),
                        template_data : Object.toJSON(templateData)
                    },
                    onSuccess: function(transport) {
                        return resolve(transport);
                    }
                });
            });
        })
        .then(function() {
            Windows.getFocusedWindow().close();
            self.getGridObj().reload();
        });
    },

    // ---------------------------------------

    categoryNotSelectedWarningPopupContinueClick: function()
    {
        setLocation(M2ePro.url.get('adminhtml_ebay_listing_categorySettings'));
    },

    // ----------------------------------------

    setCategoriesData: function(data)
    {
        this.categoriesData = data;
    },

    setMarketplaceId: function(id)
    {
        this.marketplaceId = id;
    }

    // ---------------------------------------
});
