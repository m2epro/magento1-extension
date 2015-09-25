EbayListingCategoryProductGridHandler = Class.create(EbayListingCategoryGridHandler, {

    //----------------------------------

    productIdCellIndex: 1,
    productTitleCellIndex: 2,

    //----------------------------------

    prepareActions: function($super)
    {
        $super();

        this.actions = Object.extend(this.actions, {

            getSuggestedCategoriesAction: function(id) {
                this.getSuggestedCategories(id);
            }.bind(this),
            resetCategoriesAction: function(id) {
                this.resetCategories(id);
            }.bind(this),
            removeItemAction: function(id) {
                var ids = id ? [id] : this.getSelectedProductsArray();
                this.removeItems(ids);
            }.bind(this)

        });
    },

    //----------------------------------

    getSuggestedCategories: function(id)
    {
        this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();
        this.unselectAll();

        if (id && !confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        EbayListingCategoryProductSuggestedSearchHandlerObj.search(
            this.selectedProductsIds.join(','), function(searchResult) {
                this.getGridObj().doFilter();
                this.selectedProductsIds = [];

                MagentoMessageObj.clearAll();

                if (searchResult.failed > 0) {
                    MagentoMessageObj.addError(
                        M2ePro.translator.translate('eBay could not assign Categories for %product_title% Products.')
                            .replace('%product_title%', searchResult.failed)
                    );
                } else if (searchResult.succeeded > 0) {
                    MagentoMessageObj.addSuccess(
                        M2ePro.translator.translate('Suggested Categories were successfully Received for %product_title% Product(s).')
                            .replace('%product_title%', searchResult.succeeded)
                    );
                }
            }.bind(this)
        );
    },

    getSuggestedCategoriesForAll: function()
    {
        var gridIds = this.getGridMassActionObj().getGridIds().split(',')
        if (gridIds.length > 100 && !confirm('Are you sure?')) {
            return;
        }

        this.getGridMassActionObj().selectAll();
        this.getSuggestedCategories();
    },

    //----------------------------------

    editPrimaryCategories: function()
    {
        this.editCategoriesByType(M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN'))
    },

    editStorePrimaryCategories: function()
    {
        this.editCategoriesByType(M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_MAIN'))
    },

    //----------------------------------

    editCategories: function(id)
    {
        this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_categorySettings/getChooserBlockHtml'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                ids: this.selectedProductsIds.join(',')
            },
            onSuccess: function(transport) {

                var title = M2ePro.translator.translate('Set eBay Category for Product(s)');

                if (this.selectedProductsIds.length == 1) {
                    var productName = this.getProductNameByRowId(this.selectedProductsIds[0]);
                    title += '&nbsp;"' + productName + '"';
                }

                this.showChooserPopup(title, transport.responseText);
            }.bind(this)
        });
    },

    //----------------------------------

    resetCategories: function(id)
    {
        if (id && !confirm('Are you sure?')) {
            return;
        }

        this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepTwoSuggestedReset'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                ids: this.selectedProductsIds.join(',')
            },
            onSuccess: function(transport) {
                this.getGridObj().doFilter();
                this.unselectAll();
            }.bind(this)
        });
    },

    //----------------------------------

    showChooserPopup: function(title, content)
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
            height: 350,
            width: 700,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() {
                this.selectedProductsIds = [];

                return true;
            }
        };

        Dialog.info(content, config);

        $('cancel_button').observe('click', function() {
            Windows.getFocusedWindow().close();
            this.unselectAll();
        }.bind(this));

        $('done_button').observe('click', function() {
            if(!this.validate()) {
                return;
            }

            this.saveCategoriesData(EbayListingCategoryChooserHandlerObj.getInternalData());
        }.bind(this));

        $('modal_dialog_message').innerHTML.evalScripts();
    },

    //----------------------------------

    nextStep: function()
    {
        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepTwoModeProductValidate'), {
            method: 'get',
            asynchronous: true,
            parameters: {},
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();

                if (response['validation']) {
                    return setLocation(M2ePro.url.get('adminhtml_ebay_listing_categorySettings'));
                }

                if (response['message']) {
                    return MagentoMessageObj.addError(response['message']);
                }

                this.nextStepWarningPopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Set eBay Category'),
                    width: 430,
                    height: 200,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });

                this.nextStepWarningPopup.options.destroyOnClose = false;
                $('modal_dialog_message').insert($('next_step_warning_popup_content').show());

                $('total_count').update(response['total_count']);
                $('failed_count').update(response['failed_count']);

            }.bind(this)
        });
    },

    //----------------------------------

    removeItems: function(ids)
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        var url = M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepTwoDeleteProductsModeProduct');
        new Ajax.Request(url, {
            method: 'post',
            parameters: {
                ids: ids.join(',')
            },
            onSuccess: function() {
                this.unselectAllAndReload();
            }.bind(this)
        });
    },

    //----------------------------------

    confirm: function($super)
    {
        var action = '';

        $$('select#'+this.gridId+'_massaction-select option').each(function(o) {
            if (o.selected && o.value != '') {
                action = o.value;
            }
        });

        if (action == 'removeItem' ||
            action == 'editCategories' ||
            action == 'editPrimaryCategories' ||
            action == 'editStorePrimaryCategories') {
            return true;
        }

        var result = $super();
        if (action == 'getSuggestedCategories' && !result) {
            this.unselectAll();
        }

        return result;
    },

    //----------------------------------

    validate: function()
    {
        if($$('.main-store-empty-advice').length <= 0) {
            return true;
        }

        $$('.main-store-empty-advice')[0].hide();

        var primary = $('magento_block_ebay_listing_category_chooser_store_primary_not_selected')==null;
        var secondary = $('magento_block_ebay_listing_category_chooser_store_secondary_not_selected')==null;

        if(primary==false && secondary==true) {
            $$('.main-store-empty-advice')[0].show();
            return false;
        }

        return true;
    }

    //----------------------------------
});