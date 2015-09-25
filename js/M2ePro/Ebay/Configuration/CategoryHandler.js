EbayConfigurationCategoryHandler = Class.create();
EbayConfigurationCategoryHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    categoryMode: null,
    categoryValue: null,
    templates: null,

    //----------------------------------

    initialize: function(categoryMode, categoryValue)
    {
        this.categoryMode = categoryMode;
        this.categoryValue = categoryValue;
    },

    //----------------------------------

    setTemplates: function(templates)
    {
        this.templates = templates;
    },

    //----------------------------------

    save_click: function(type, isEdit)
    {
        var self = EbayConfigurationCategoryHandlerObj;

        if (!EbayListingCategoryChooserHandlerObj.validate()) {
            return;
        }

        if (type == 'primary') {
            var isValid = true;

            $$('.template-info').each(function(element) {
                if (!window[element.getAttribute('obj_name')].validate()) {
                    isValid = false;
                }
            });

            if (!isValid) {
                return;
            }
        }

        self.confirm(self.saveCategory, type, isEdit);
    },

    saveCategory: function(type, isEdit)
    {
        var self = EbayConfigurationCategoryHandlerObj;

        var saveParams = self.getSaveParameters(type);
        if (saveParams === false) {
            return;
        }

        var redirectUrl = M2ePro.url.get('adminhtml_ebay_category/index');
        if (isEdit && saveParams.category_mode && saveParams.category_value) {

            var activeTabId = null;
            if (type == 'primary') {
                var activeTab = ebayConfigurationCategoryEditPrimaryTabsJsTabs.activeTab;
                activeTabId = activeTab.id.replace('ebayConfigurationCategoryEditPrimaryTabs_', '');
            }

            redirectUrl = M2ePro.url.get('adminhtml_ebay_category/edit', {
                mode: saveParams.category_mode,
                value: saveParams.category_value,
                marketplace: saveParams.marketplace,
                account: saveParams.account,
                type: saveParams.category_type,
                tab: activeTabId
            })
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_category/save'), {
            method: 'post',
            parameters: saveParams,
            onSuccess: function(transport) {
                setLocation(redirectUrl);
            }
        });
    },

    //----------------------------------

    chooserDoneCallback: function()
    {
        var self = EbayConfigurationCategoryHandlerObj;

        if ($('ebayConfigurationCategoryEditPrimaryTabs_specific_content') === null
            ||$('ebayConfigurationCategoryEditPrimaryTabs_specific_content').length === 0
        ) {
            return;
        }

        if (!EbayListingCategoryChooserHandlerObj.validate()) {
            $('ebayConfigurationCategoryEditPrimaryTabs_specific_content').innerHTML = '';
            return;
        }

        var categoryData = EbayListingCategoryChooserHandlerObj.getInternalData();
        var marketplaceId = EbayListingCategoryChooserHandlerObj.marketplaceId;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_category/getConfigurationCategorySpecificHtml'), {
            method: 'post',
            parameters: {
                category_mode: categoryData.mode,
                category_value: categoryData.value,
                marketplace: marketplaceId,
                templates: self.templates
            },
            onSuccess: function(transport) {
                $('ebayConfigurationCategoryEditPrimaryTabs_specific_content').innerHTML = transport.responseText;
                $('ebayConfigurationCategoryEditPrimaryTabs_specific_content').innerHTML.extractScripts()
                    .map(function(script) {
                        try {
                            eval(script);
                        } catch(e) {}
                    });
            }
        });
    },

    getSaveParameters: function(type)
    {
        var parameters = {
            old_category_mode: this.categoryMode,
            old_category_value: this.categoryValue,
            category_mode: M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE'),
            category_type: EbayListingCategoryChooserHandlerObj.singleCategoryType,
            marketplace: EbayListingCategoryChooserHandlerObj.marketplaceId,
            account: EbayListingCategoryChooserHandlerObj.accountId
        };

        var categoryData = EbayListingCategoryChooserHandlerObj.getInternalData();

        if (categoryData) {
            parameters['category_mode'] = categoryData.mode;
            parameters['category_value'] = categoryData.value;
        }

        if (type == 'primary') {
            var specificsData = {};
            $$('.template-info').each(function(element) {
                specificsData[element.getAttribute('template_id')] = window[element.getAttribute('obj_name')].getItemSpecifics();
            });

            parameters['specifics_data'] = Object.toJSON(specificsData);
        }

        return parameters;
    },

    confirm: function(okCallback, type, isEdit)
    {
        var self = EbayConfigurationCategoryHandlerObj;

        var skipConfirmation = getCookie('ebay_configuration_category_skip_save_confirmation');
        var confirmText = M2ePro.translator.translate('<b>Note:</b> All changes you have made will be automatically applied to all M2E Pro Listings where this Category is used.');

        if (skipConfirmation) {
            okCallback(type, isEdit);
            return;
        }

        self.saveConfirm = false;
        var template = $('dialog_confirm_container');

        template.down('.dialog_confirm_content').innerHTML = '<div class="magento-message"><br/>'+confirmText+'</div>' +
            '<div style="position: absolute; bottom: 0; left: 0; padding: 10px;">' +
            '<input type="checkbox" id="do_not_show_again" name="do_not_show_again">&nbsp;' +
                M2ePro.translator.translate('Do not show any more') +
            '</div>';

        var me = this;
        if(!me.isCreatedDialog) {
            me.isCreatedDialog = true;
            Dialog._openDialog(template.innerHTML, {
                draggable: true,
                resizable: true,
                closable: true,
                className: "magento",
                title: 'Save Category',
                height: 80,
                width: 650,
                zIndex: 2100,
                destroyOnClose: true,
                hideEffect: Element.hide,
                showEffect: Element.show,
                id: "save-template",
                buttonClass: "form-button button",
                ok: function() {
                    if ($('do_not_show_again').checked) {
                        setCookie('ebay_configuration_category_skip_save_confirmation', 1, 3*365, '/');
                    }

                    okCallback(type, isEdit);
                },
                cancel: function() {},
                onClose: function() {
                    me.isCreatedDialog = false;
                }
            });
        }
    }

    //----------------------------------
});