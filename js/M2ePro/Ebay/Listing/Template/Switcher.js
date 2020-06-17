window.EbayListingTemplateSwitcher = Class.create(Common, {

    // ---------------------------------------

    storeId: null,
    marketplaceId: null,
    listingProductIds: '',

    // ---------------------------------------

    initialize: function()
    {
        Validation.add('M2ePro-validate-ebay-template-title', M2ePro.translator.translate('Policy with the same Title already exists.'), function(value, element) {

            var templateNick = element.name.substr(0, element.name.indexOf('['));

            return EbayListingTemplateSwitcherObj.isTemplateTitleUnique(templateNick, value);
        });

        Validation.add('M2ePro-validate-ebay-template-switcher', M2ePro.translator.translate('This is a required field.'), function(value, element) {

            var mode = base64_decode(value).evalJSON().mode;

            return mode !== null;
        });
    },

    // ---------------------------------------

    getSwitcherNickByElementId: function(id)
    {
        return id.replace('template_', '');
    },

    getSwitcherElementId: function(templateNick)
    {
        return 'template_' + templateNick;
    },

    getSwitcher: function(templateNick)
    {
        return $(this.getSwitcherElementId(templateNick));
    },

    getSwitcherValueId: function(templateNick)
    {
        var switcher = this.getSwitcher(templateNick);
        var switcherValue = base64_decode(switcher.value).evalJSON();

        return switcherValue.id;
    },

    getSwitcherValueMode: function(templateNick)
    {
        var switcher = this.getSwitcher(templateNick);
        var switcherValue = base64_decode(switcher.value).evalJSON();

        return switcherValue.mode;
    },

    isSwitcherValueModeEmpty: function(templateNick)
    {
        return this.getSwitcherValueMode(templateNick) === null;
    },

    isSwitcherValueModeParent: function(templateNick)
    {
        return this.getSwitcherValueMode(templateNick) == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT');
    },

    isSwitcherValueModeCustom: function(templateNick)
    {
        return this.getSwitcherValueMode(templateNick) == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM');
    },

    isSwitcherValueModeTemplate: function(templateNick)
    {
        return this.getSwitcherValueMode(templateNick) == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE');
    },

    isExistSynchronizationTab: function()
    {
        return typeof EbayTemplateSynchronizationObj != 'undefined';
    },

    isNeededSaveWatermarkImage: function(ajaxResponse)
    {
        var isDescriptionTemplate = false;

        ajaxResponse.each(function(template) {
            if (template.nick == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION')) {
                isDescriptionTemplate = true;
            }
        });

        return isDescriptionTemplate && $('watermark_image').value != '';
    },

    getTemplateDataContainer: function(templateNick)
    {
        return $('template_'+templateNick+'_data_container');
    },

    // ---------------------------------------

    change: function()
    {
        var templateNick = EbayListingTemplateSwitcherObj.getSwitcherNickByElementId(this.id);
        var templateMode = EbayListingTemplateSwitcherObj.getSwitcherValueMode(templateNick);

        EbayListingTemplateSwitcherObj.clearMessages(templateNick);

        switch (templateMode) {
            case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT'):
                EbayListingTemplateSwitcherObj.clearContent(templateNick);
                break;

            case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM'):
                EbayListingTemplateSwitcherObj.reloadContent(templateNick);
                break;

            case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE'):
                EbayListingTemplateSwitcherObj.clearContent(templateNick);
                EbayListingTemplateSwitcherObj.checkMessages(templateNick);
                break;
        }

        EbayListingTemplateSwitcherObj.hideEmptyOption(EbayListingTemplateSwitcherObj.getSwitcher(templateNick));

        if (!EbayListingTemplateSwitcherObj.isSwitcherValueModeCustom(templateNick)) {
            EbayListingTemplateSwitcherObj.updateButtonsVisibility(templateNick);
            EbayListingTemplateSwitcherObj.updateEditVisibility(templateNick);
            EbayListingTemplateSwitcherObj.updateTemplateLabelVisibility(templateNick);
        }
    },

    // ---------------------------------------

    clearMessages: function(templateNick)
    {
        $('template_switcher_' + templateNick + '_messages').innerHTML = '';
    },

    checkMessages: function(templateNick)
    {
        if (templateNick != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT') &&
            templateNick != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING')
        ) {
            return;
        }

        var id = this.getSwitcherValueId(templateNick),
            nick = templateNick,
            data = Form.serialize(this.getTemplateDataContainer(templateNick).id)
                + '&listing_product_ids=' + this.listingProductIds,
            storeId = this.storeId,
            marketplaceId = this.marketplaceId,
            container = 'template_switcher_' + templateNick + '_messages',
            callback = function() {
                var refresh = $(container).down('a.refresh-messages');
                if (refresh) {
                    refresh.observe('click', function() {
                        this.checkMessages(templateNick);
                    }.bind(this))
                }
            }.bind(this);

        TemplateManagerObj.checkMessages(
            id,
            nick,
            data,
            storeId,
            marketplaceId,
            container,
            callback
        );
    },

    // ---------------------------------------

    updateEditVisibility: function(templateNick)
    {
        var tdEdit = $('template_' + templateNick + '_edit');

        if (!tdEdit) {
            return;
        }

        if (this.isSwitcherValueModeTemplate(templateNick)) {
            tdEdit.show();
        } else {
            tdEdit.hide();
        }
    },

    updateButtonsVisibility: function(templateNick)
    {
        var divButtonsContainer = $('template_' + templateNick + '_buttons_container');

        if (!divButtonsContainer) {
            return;
        }

        if (this.isSwitcherValueModeCustom(templateNick)) {
            divButtonsContainer.show();
        } else {
            divButtonsContainer.hide();
        }
    },

    updateTemplateLabelVisibility: function(templateNick)
    {
        var labelContainer = $('template_' + templateNick + '_nick_label');
        var templateLabel  = labelContainer.down('span.template');
        var parentLabel    = labelContainer.down('span.parent');

        labelContainer.hide();
        templateLabel.hide();

        parentLabel && parentLabel.hide();

        if (this.isSwitcherValueModeTemplate(templateNick)) {
            labelContainer.show();
            templateLabel.show();
        }

        if (this.isSwitcherValueModeEmpty(templateNick) && parentLabel) {
            labelContainer.show();
            parentLabel.show();
        }
    },

    // ---------------------------------------

    scrollToFirstFailedElement: function()
    {
        if ($$('a.tab-item-link.error').length > 0) {
            ebayListingTemplateEditTabsJsTabs.showTabContent($$('a.tab-item-link.error')[0]);
        }

        var firstFailed = $$('.validation-failed').shift();
        firstFailed.up('table').scrollIntoView();
    },

    // ---------------------------------------

    saveSwitchers: function(callback)
    {
        var validationResult = editForm.validate();

        if (EbayListingTemplateSwitcherObj.isExistSynchronizationTab()) {
            EbayTemplateSynchronizationObj.checkVirtualTabValidation();
        }

        if (!validationResult) {
            EbayListingTemplateSwitcherObj.scrollToFirstFailedElement();
            return;
        }

        $('edit_form').request({
            method: 'post',
            asynchronous: true,
            parameters: {},
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();

                response.each(function(template) {
                    EbayListingTemplateSwitcherObj.afterCustomSaveAsTemplate(template.nick, template.id, template.title);
                });

                var params = {};

                $$('.template-switcher').each(function(switcher) {
                    params[switcher.name] = switcher.value;
                });

                params['tab'] = ebayListingTemplateEditTabsJsTabs.activeTab.id.split('_').pop();

                if (EbayListingTemplateSwitcherObj.isNeededSaveWatermarkImage(response)) {
                    EbayTemplateDescriptionObj.saveWatermarkImage(callback, params);
                } else if (typeof callback == 'function') {
                    callback(params);
                }

            }.bind(this)
        });
    },

    // ---------------------------------------

    isTemplateTitleUnique: function(templateNick, templateTitle)
    {
        var unique = true;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_template/isTitleUnique'), {
            method: 'get',
            asynchronous: false,
            parameters: {
                nick: templateNick,
                title: templateTitle
            },
            onSuccess: function(transport) {
                unique = transport.responseText.evalJSON()['unique'];
            }
        });

        return unique;
    },

    // ---------------------------------------

    validateCustomTemplate: function(templateNick)
    {
        var validationResult = $('template_'+templateNick+'_data_container').select('select, input').collect(Validation.validate);

        if (validationResult.indexOf(false) != -1) {
            EbayListingTemplateSwitcherObj.scrollToFirstFailedElement();
            return false;
        }

        return true;
    },

    // ---------------------------------------

    customSaveAsTemplate: function(templateNick)
    {
        if (!this.validateCustomTemplate(templateNick)) {
            return;
        }

        var template = $('dialog_confirm_container');

        template.down('.dialog_confirm_content').innerHTML = '<div style="margin: 10px;">' +
            '<input type="text" ' +
                'class="input-text required-entry M2ePro-validate-ebay-template-title" ' +
                'name="'+templateNick+'[template_title]" ' +
                'id="template_title" ' +
                'style="width: 375px;" ' +
                'placeholder="' + M2ePro.translator.translate('Please specify Policy Title') + '" ' +
            '/>' +
            '</div>';

        var me = this;
        if(!me.isCreatedDialog) {
            var html = template.innerHTML;
            template.down('.dialog_confirm_content').remove();
            me.isCreatedDialog = true;

            Dialog._openDialog(html, {
                draggable: true,
                resizable: true,
                closable: true,
                className: "magento",
                title: M2ePro.translator.translate('Save as New Policy'),
                width: 400,
                height: 80,
                zIndex: 2100,
                destroyOnClose: true,
                hideEffect: Element.hide,
                showEffect: Element.show,
                id: "save-template",
                ok: function() {
                    if (!Validation.validate($('template_title'))) {
                        return false;
                    }

                    $$('input[name="'+templateNick+'[id]"]')[0].value = '';
                    $$('input[name="'+templateNick+'[is_custom_template]"]')[0].value = 0;
                    $$('input[name="'+templateNick+'[title]"]')[0].value = $('template_title').value;

                    $('edit_form').request({
                        method: 'post',
                        asynchronous: true,
                        parameters: {
                            nick: templateNick
                        },
                        onSuccess: function(transport) {

                            var response = transport.responseText.evalJSON();

                            response.each(function(template) {
                                EbayListingTemplateSwitcherObj.addToSwitcher(template.nick, template.id, template.title);
                                EbayListingTemplateSwitcherObj.clearContent(template.nick);
                                EbayListingTemplateSwitcherObj.updateButtonsVisibility(template.nick);
                                EbayListingTemplateSwitcherObj.updateEditVisibility(template.nick);
                                EbayListingTemplateSwitcherObj.updateTemplateLabelVisibility(template.nick);
                                EbayListingTemplateSwitcherObj.checkMessages(template.nick);
                            });
                        }.bind(this)
                    });

                    return true;
                }.bind(this),
                cancel: function() {},
                onClose: function() {
                    template.insert('<div class="dialog_confirm_content"></div>');
                    me.isCreatedDialog = false;
                }
            });
        }
    },

    afterCustomSaveAsTemplate: function(templateNick, templateId, templateTitle)
    {
        $$('input[name="'+templateNick+'[id]"]')[0].value = templateId;
        $$('input[name="'+templateNick+'[title]"]')[0].value = templateTitle;

        var switcher = EbayListingTemplateSwitcherObj.getSwitcher(templateNick);

        switcher.down('.template-switcher-custom-option').value = base64_encode(
            Object.toJSON({
                mode: M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM'),
                nick: templateNick,
                id: templateId
            })
        );
    },

    // ---------------------------------------

    editTemplate: function(templateNick)
    {
        var templateId = this.getSwitcherValueId(templateNick);

        window.open(M2ePro.url.get('adminhtml_ebay_template/edit', {id: templateId, nick: templateNick, close_on_save: 1}) , '_blank');
    },

    // ---------------------------------------

    customizeTemplate: function(templateNick)
    {
        this.clearMessages(templateNick);
        this.reloadContent(templateNick, function() {
            $$('input[name="'+templateNick+'[id]"]')[0].value = EbayListingTemplateSwitcherObj.getSwitcherValueId(templateNick);
            $$('input[name="'+templateNick+'[is_custom_template]"]')[0].value = 1;
            $$('input[name="'+templateNick+'[title]"]')[0].value += ' ['+M2ePro.translator.translate('Customized')+']';
        });
        this.getSwitcher(templateNick).selectedIndex = 0;
    },

    // ---------------------------------------

    clearContent: function(templateNick)
    {
        this.getTemplateDataContainer(templateNick).innerHTML = '';
        this.getTemplateDataContainer(templateNick).hide();
    },

    // ---------------------------------------

    reloadContent: function(templateNick, callback)
    {
        var id = this.getSwitcherValueId(templateNick);

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_template/getTemplateHtml'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                id   : id,
                nick : templateNick
            },
            onSuccess: function(transport) {

                this.getTemplateDataContainer(templateNick).replace(transport.responseText);
                this.getTemplateDataContainer(templateNick).show();

                this.updateButtonsVisibility(templateNick);
                this.updateEditVisibility(templateNick);
                this.updateTemplateLabelVisibility(templateNick);

                if (typeof callback == 'function') {
                    callback();
                }

            }.bind(this)
        });
    },

    // ---------------------------------------

    addToSwitcher: function(templateNick, templateId, templateTitle)
    {
        var switcher = this.getSwitcher(templateNick);
        var optionGroup = this.getTemplatesGroup(templateNick);

        var optionValue = {
            mode: M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE'),
            nick: templateNick,
            id: templateId
        };
        var option = document.createElement('option');

        option.value = base64_encode(Object.toJSON(optionValue));
        option.innerHTML = templateTitle;

        $(optionGroup).appendChild(option);

        switcher.up('td').show();
        switcher.value = option.value;
    },

    getTemplatesGroup: function(templateNick)
    {
        var switcher = this.getSwitcher(templateNick);
        var optionGroup = switcher.down('optgroup.templates-group');

        if (typeof optionGroup != 'undefined') {
            return optionGroup;
        }

        optionGroup = document.createElement('optgroup');
        optionGroup.className = 'templates-group';
        optionGroup.label = M2ePro.translator.translate('Policies');

        switcher.appendChild(optionGroup);

        return optionGroup;
    }

    // ---------------------------------------
});