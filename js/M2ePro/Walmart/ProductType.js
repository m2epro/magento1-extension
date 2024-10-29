window.WalmartProductType = Class.create(Common, {
    skipSaveConfirmationPostFix: '_skip_save_confirmation',

    originalFormData: null,
    isPageLeavingSafe: false,

    initialize: function () {
        if (this.getProductType()) {
            this.updateProductTypeScheme();
        } else {
            this.originalFormData = $('edit_form').serialize();
        }

        Validation.add(
            'M2ePro-general-product-type-title',
            M2ePro.translator.translate(
                'The specified Product Title is already used for other Product Type. Product Type Title must be unique.'
            ),
            function (productTypeTitle) {
                const marketplaceId = document.getElementById('general_marketplace_id').value;
                const productTypeId = document.getElementById('general_id').value;
                let isValid = false;

                new Ajax.Request(M2ePro.url.get('adminhtml_walmart_productType/isUniqueTitle'), {
                    method: 'post',
                    asynchronous: false,
                    parameters: {
                        title: productTypeTitle,
                        marketplace_id: marketplaceId,
                        product_type_id: productTypeId,
                    },
                    onSuccess: function (transport) {
                        isValid = transport.responseText.evalJSON()['result'];
                    }
                });

                return isValid;
            }
        );

        $(document).on('change', '#general_product_type_title', this.clearValidationMessage.bind(this));
        $('general_marketplace_id').down('option').setStyle({ display: 'none' });
    },

    initObservers: function () {
        $('general_marketplace_id').observe(
            'change',
            WalmartProductTypeObj.onChangeMarketplaceId.bind(this)
        );
        $('product_type_edit_activator').observe(
            'click',
            WalmartProductTypeObj.openSearchPopup.bind(this)
        );

        addEventListener(
            "beforeunload",
            function (event) {
                const currentFormData = $('edit_form').serialize();
                if (!this.isPageLeavingSafe && currentFormData !== this.originalFormData) {
                    event.preventDefault();
                    return event.returnValue = "";
                }
            }.bind(this),
            {capture: true}
        );
    },

    getMarketplaceId: function () {
        const marketplaceId = $('general_marketplace_id').value;

        return marketplaceId !== undefined ? marketplaceId : 0;
    },

    getProductType: function () {
        const productType = $('general_product_type').value;

        return productType !== undefined ? productType : '';
    },

    setProductType: function (productType) {
        const productTypeField = $('general_product_type');
        if (productType === productTypeField.value) {
            return;
        }

        productTypeField.value = productType;
        const searchPopupNotSelected = $('general_product_type_not_selected');
        const selectedProductTypeTitle = $('general_selected_product_type_title');

        if (productType) {
            searchPopupNotSelected.hide();
            selectedProductTypeTitle
                .update(WalmartProductTypeSearchObj.getProductTypeTitle(productType))
                .show();
        } else {
            searchPopupNotSelected.show();
            selectedProductTypeTitle.hide();
        }

        this.updateProductTypeScheme();
    },

    onChangeMarketplaceId: function () {
        this.setProductType('');
        this.updateProductTypeScheme();
        this.openSearchPopup();
    },

    resetProductTypeScheme: function () {
        WalmartProductTypeTabsObj.resetTabs(
            WalmartProductTypeContentObj.getGroupList()
        );

        $$('.product_type_generated_field').map(
            function (item) {
                item.remove();
            }
        );
    },

    openSearchPopup: function () {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_walmart_productType/searchProductTypePopup'), {
            method: 'post',
            asynchronous: false,
            parameters: {
                marketplace_id: self.getMarketplaceId()
            },
            onSuccess: function (transport) {
                self.openPopUp(transport.responseText);
            }
        });
    },

    openPopUp: function(html)
    {
        this.popUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Search Product Type'),
            top: 100,
            width: 850,
            height: 420,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        $('modal_dialog_message').style.paddingTop = '20px';
        $('modal_dialog_message').insert(html);
        $('modal_dialog_message').innerHTML.evalScripts();

        this.setSearchActivatorVisibility(true);
        WalmartProductTypeFinderObj.renderRootCategories('product_type_browse_results');

        $('product_type_confirm').observe('click', () => this.confirmSearchProductTypePopup());
    },

    cancelPopUp: function()
    {
        this.popUp.close();
    },

    confirmSearchProductTypePopup: function () {
        var currentTabId = this.getActiveTabId();
        if (currentTabId === 'walmartProductTypeSearchPopupTabs_search') {
            this.setProductType(WalmartProductTypeSearchObj.currentProductType);
        } else if (currentTabId === 'walmartProductTypeSearchPopupTabs_browse') {
            this.setProductType(WalmartProductTypeFinderObj.currentProductType);
        }

        this.cancelPopUp();
    },

    getActiveTabId: function () {
        var activeTab = $$('.tabs-horiz li a.tab-item-link.active')[0];
        if (activeTab) {
            return activeTab.readAttribute('id');
        }

        throw 'Unresolved tab';
    },

    setSearchActivatorVisibility: function (visible) {
        $('product_type_edit_activator').style.display = visible ? 'inline' : 'none';
    },

    updateProductTypeScheme: function () {
        var self = this;
        this.resetProductTypeScheme();

        const marketplaceId = this.getMarketplaceId(),
            productType = this.getProductType();
        if (!marketplaceId || !productType) {
            return;
        }
        self.loadProductTypeForm(marketplaceId, productType);
    },

    loadProductTypeForm: function (marketplaceId, productType) {
        var self = this;

        var generalProductTypeTitle = $('general_product_type_title');
        if (generalProductTypeTitle.value === '') {
            generalProductTypeTitle.value = $('general_selected_product_type_title').innerHTML.strip();
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_walmart_productType/getProductTypeInfo'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                marketplace_id: marketplaceId,
                product_type: productType,
                is_new_product_type: $('is_new_product_type').getValue()
            },
            onSuccess: function (transport) {
                const response = transport.responseText.evalJSON();
                if (!response.result) {
                    messageObj.clear();
                    messageObj.addError(response.message);
                    return;
                }

                WalmartProductTypeContentObj.load(
                    response.data['scheme'],
                    response.data['settings'],
                    response.data['groups'],
                    response.data['timezone_shift'],
                    response.data['specifics_default_settings'],
                );

                self.originalFormData = $('edit_form').serialize();
            }
        });
    },

    confirm: function (isNeedConfirm, okCallback) {
        if (!isNeedConfirm) {
            okCallback();
            return;
        }

        if (confirm(M2ePro.translator.translate('Save Product Type Settings'))) {
            okCallback();
        }
    },

    saveClick: function (isNeedConfirm) {
        var self = this;
        if (!this.validateForm()) {
            return;
        }

        if (isNeedConfirm) {
            this.confirm(isNeedConfirm, function () {
                self.isPageLeavingSafe = true;
                self.saveFormUsingAjax((response) => setLocation(response.backUrl))
            });
        } else {
            self.isPageLeavingSafe = true;
            self.saveFormUsingAjax((response) => setLocation(response.backUrl))
        }
    },

    saveAndEditClick: function (isNeedConfirm) {
        var self = this;
        if (!this.validateForm()) {
            return;
        }

        if (isNeedConfirm) {
            this.confirm(isNeedConfirm, function () {
                self.isPageLeavingSafe = true;
                self.saveFormUsingAjax(response => setLocation(response.editUrl));
            });

            return;
        }

        self.isPageLeavingSafe = true;
        self.saveFormUsingAjax(response => setLocation(response.editUrl));
    },

    saveAndCloseClick: function (isNeedConfirm) {
        var self = this;
        if (!this.validateForm()) {
            return;
        }

        if (isNeedConfirm) {
            this.confirm(isNeedConfirm, function () {
                self.isPageLeavingSafe = true;
                self.saveFormUsingAjax(() => window.close());
            });

            return;
        }

        self.isPageLeavingSafe = true;
        self.saveFormUsingAjax(() => window.close());
    },

    saveFormUsingAjax: function (successCallback) {
        new Ajax.Request(M2ePro.url.get('formSubmit'), {
            method: 'post',
            parameters: Form.serialize($('edit_form')),
            onSuccess: function (transport) {
                const response = transport.responseText.evalJSON();
                if (!response.status) {
                    messageObj.clear();
                    messageObj.addError(response.message);

                    return;
                }
                if (successCallback) {
                    successCallback({
                        backUrl: response.back_url,
                        editUrl: response.edit_url
                    });
                }
            }
        });
    },

    validateForm: function() {
        return editForm.validate();
    },

    deleteClick: function () {
        if (confirm(M2ePro.translator.translate('Delete Product Type'))) {
            WalmartProductTypeObj.isPageLeavingSafe = true;
            setLocation(M2ePro.url.get('deleteAction'));
        }
    },

    clearValidationMessage: function () {
        const adviceTitle = $('advice-M2ePro-general-product-type-title-general_product_type_title');
        if (adviceTitle) {
            adviceTitle.remove();
        }
    },
});
