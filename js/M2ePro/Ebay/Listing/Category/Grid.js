window.EbayListingCategoryGrid = Class.create(Grid, {

    // ---------------------------------------

    prepareActions: function() {
        this.actions = {
            editCategoriesAction: function(id) {
                id && this.selectByRowId(id);
                this.editCategories('both');
            }.bind(this),

            resetCategoriesAction: function(id) {
                this.resetCategories(id);
            }.bind(this)
        };
    },

    editCategories: function(mode) {
        this.selectedProductsIds = this.getSelectedProductsString();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_categorySettings/getChooserBlockHtml'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                ids: this.selectedProductsIds,
                category_mode: mode
            },
            onSuccess: function(transport) {
                this.openPopUp('Category Settings', transport.responseText);
            }.bind(this)
        });
    },

    resetCategories: function(id) {
        if (id && !confirm('Are you sure?')) {
            return;
        }

        this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepTwoReset'), {
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

    //----------------------------------------

    openPopUp: function(title, content) {
        var popup = Dialog.info(content, {
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
        });

        popup.options.destroyOnClose = true;

        $('modal_dialog_message').innerHTML.evalScripts();
    },

    cancelCategoriesData: function() {
        Windows.getFocusedWindow() && Windows.getFocusedWindow().close();
        this.unselectAllAndReload();
    },

    confirmCategoriesData: function() {
        var editForm = new varienForm('edit_form');
        if (!editForm.validate()) {
            return;
        }

        var selectedCategories = EbayTemplateCategoryChooserObj.selectedCategories;
        var typeMain = M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN');
        if (typeof selectedCategories[typeMain] !== 'undefined') {
            selectedCategories[typeMain]['specific'] = EbayTemplateCategoryChooserObj.selectedSpecifics;
        }

        this.saveCategoriesData(selectedCategories);
    },

    // ---------------------------------------

    saveCategoriesData: function(templateData) {
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepTwoSaveToSession'), {
            method: 'post',
            parameters: {
                ids: this.getSelectedProductsString(),
                template_data: Object.toJSON(templateData)
            },
            onSuccess: function(transport) {
                this.cancelCategoriesData();
            }.bind(this)
        });
    },

    // ---------------------------------------

    completeCategoriesDataStep: function(validateCategory, validateSpecifics) {
        var self = this;

        MessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepTwoModeValidate'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                validate_category: validateCategory,
                validate_specifics: validateSpecifics
            },
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();

                if (response['validation']) {
                    return setLocation(M2ePro.url.get('adminhtml_ebay_listing_categorySettings'));
                }

                if (response['message']) {
                    return MessageObj.addError(response['message']);
                }

                this.nextStepWarningPopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Set eBay Category'),
                    top: 50,
                    width: 430,
                    height: 400,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });

                this.nextStepWarningPopup.options.destroyOnClose = false;
                $('modal_dialog_message').insert($('next_step_warning_popup_content').show());

                $('next_step_warning_popup_content').select('span.total_count').each(function(el) {
                    $(el).update(response['total_count']);
                });

                $('next_step_warning_popup_content').select('span.failed_count').each(function(el) {
                    $(el).update(response['failed_count']);
                });

                self.autoHeightFix();

            }.bind(this)
        });
    },

    // ---------------------------------------

    validateCategories: function(isAlLeasOneCategorySelected, showErrorMessage) {
        var button = $('ebay_listing_category_continue_btn');
        if (parseInt(isAlLeasOneCategorySelected)) {
            button.addClassName('disabled');
            button.disable();
            if (parseInt(showErrorMessage)) {
                MessageObj.removeError('category-data-must-be-specified');
                MessageObj.addError(M2ePro.translator.translate('select_relevant_category'), 'category-data-must-be-specified');
            }
        } else {
            button.removeClassName('disabled');
            button.enable();
            MessageObj.clear('error');
        }
    },

    // ---------------------------------------

    categoryNotSelectedWarningPopupContinueClick: function() {
        setLocation(M2ePro.url.get('adminhtml_ebay_listing_categorySettings'));
    },

    getComponent: function() {
        return 'ebay';
    }

    // ---------------------------------------
});
