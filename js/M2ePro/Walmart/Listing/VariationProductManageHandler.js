WalmartListingVariationProductManageHandler = Class.create(ActionHandler,{

    MATCHING_TYPE_EQUAL: 1,
    MATCHING_TYPE_VIRTUAL_WALMART: 2,
    MATCHING_TYPE_VIRTUAL_MAGENTO: 3,

    // ---------------------------------------

    initialize: function($super,gridHandler)
    {
        $super(gridHandler);

        Validation.add('M2ePro-walmart-required-channel-attribute', M2ePro.translator.translate('At least one Variant Attribute must be selected.'), function(value,el) {

            var result = false;

            $$('.M2ePro-walmart-required-channel-attribute').each(function(obj) {
                if (obj.checked) {
                    result = true;
                    return;
                }
            });

            return result;
        });

        this.initValidators();
    },

    // ---------------------------------------

    matchingType: 1,
    matchedAttributes: [],
    productAttributes: [],
    destinationAttributes: [],
    selectedDestinationAttributes: [],
    magentoVariationSet: [],
    walmartVariationSet: false,

    initValidators: function()
    {
        var self = this;

        Validation.add('M2ePro-walmart-attribute-unique-value', M2ePro.text.variation_manage_matched_attributes_error_duplicate, function(value, el) {

            var existedValues = [],
                isValid = true,
                form = el.up('form');

            form.select('select').each(function(el) {
                if (el.value != '') {
                    if(existedValues.indexOf(el.value) === -1) {
                        existedValues.push(el.value);
                    } else {
                        isValid = false;
                    }
                }
            });

            return isValid;
        });
    },

    initSettingsTab: function()
    {
        var self = this,
            form = $('variation_manager_attributes_form');

        if (form && self.matchingType == self.MATCHING_TYPE_EQUAL) {
            form.observe('change', function(e) {
                if (e.target.tagName != 'SELECT') {
                    return;
                }

                $(e.target).select('.empty') && $(e.target).select('.empty').length && $(e.target).select('.empty')[0].hide();
            });
        }
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

    openPopUp: function(productId, title, filter)
    {
        var self = this;

        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.variationProductManage, {
            method: 'post',
            parameters: {
                product_id : productId,
                filter: filter
            },
            onSuccess: function (transport) {

                variationProductManagePopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: title.escapeHTML(),
                    top: 5,
                    width: 1100,
                    height: 600,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show,
                    onClose: function() {
                        ListingGridHandlerObj.unselectAllAndReload();
                    }
                });
                variationProductManagePopup.options.destroyOnClose = true;

                variationProductManagePopup.productId = productId;

                $('modal_dialog_message').update(transport.responseText);
                self.initSettingsTab();
            }
        });
    },

    closeManageVariationsPopup: function()
    {
        variationProductManagePopup.close();
    },

    // ---------------------------------------

    openVocabularyAttributesPopUp: function (attributes)
    {
        var self = this;

        vocabularyAttributesPopUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: 'Vocabulary',
            top: 5,
            width: 400,
            height: 220,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            onClose: function() {
                self.reloadVariationsGrid();
            }.bind(this)
        });
        vocabularyAttributesPopUp.options.destroyOnClose = true;

        $('vocabulary_attributes_data').value = Object.toJSON(attributes);

        var attributesHtml = '';
        $H(attributes).each(function(element) {
            attributesHtml += '<li>'+element.key+' > '+element.value+'</li>';
        });

        attributesHtml = '<ul>'+attributesHtml+'</ul>';

        var bodyHtml = str_replace('%attributes%', attributesHtml, $('vocabulary_attributes_pupup_template').innerHTML);

        $('modal_dialog_message').update(bodyHtml);

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '630px';
        }, 50);
    },

    addAttributesToVocabulary: function(needAdd)
    {
        var self = this;

        var isRemember = $('vocabulary_attributes_remember_checkbox').checked;

        if (!needAdd && !isRemember) {
            Windows.getFocusedWindow().close();
            return;
        }

        new Ajax.Request(M2ePro.url.addAttributesToVocabulary, {
            method: 'post',
            parameters: {
                attributes : $('vocabulary_attributes_data').value,
                need_add:    needAdd ? 1 : 0,
                is_remember: isRemember ? 1 : 0
            },
            onSuccess: function (transport) {
                vocabularyAttributesPopUp.close();
            }
        });
    },

    openVocabularyOptionsPopUp: function (options)
    {
        var self = this;

        vocabularyOptionsPopUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: 'Vocabulary',
            top: 15,
            width: 400,
            height: 220,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            onClose: function() {
                self.reloadVariationsGrid();
            }.bind(this)
        });
        vocabularyOptionsPopUp.options.destroyOnClose = true;

        $('vocabulary_options_data').value = Object.toJSON(options);

        var optionsHtml = '';
        $H(options).each(function(element) {

            var valuesHtml = '';
            $H(element.value).each(function (value) {
                valuesHtml += value.key + ' > ' + value.value;
            });

            optionsHtml += '<li>'+element.key+': '+valuesHtml+'</li>';
        });

        optionsHtml = '<ul>'+optionsHtml+'</ul>';

        var bodyHtml = str_replace('%options%', optionsHtml, $('vocabulary_options_pupup_template').innerHTML);

        $('modal_dialog_message').update(bodyHtml);

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '500px';
        }, 50);
    },

    addOptionsToVocabulary: function(needAdd)
    {
        var self = this;

        var isRemember = $('vocabulary_options_remember_checkbox').checked;

        if (!needAdd && !isRemember) {
            Windows.getFocusedWindow().close();
            return;
        }

        new Ajax.Request(M2ePro.url.addOptionsToVocabulary, {
            method: 'post',
            parameters: {
                options_data : $('vocabulary_options_data').value,
                need_add:    needAdd ? 1 : 0,
                is_remember: isRemember ? 1 : 0
            },
            onSuccess: function (transport) {
                vocabularyOptionsPopUp.close();
            }
        });
    },

    // ---------------------------------------

    openSkuPopUp: function()
    {
        var self = this;
        manageVariationSkuPopUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.text.variation_manage_matched_sku_popup_title,
            top: 70,
            width: 470,
            height: 190,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            onClose: function() {
                $('variation_manager_sku_form').reset();
                var errorBlock = $('variation_manager_sku_form_error');
                errorBlock.hide();
            }
        });
        manageVariationSkuPopUp.options.destroyOnClose = false;

        $('modal_dialog_message').insert($('manage_variation_sku_popup').show());

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '630px';
        }, 50);
    },

    setProductSku: function()
    {
        var self = this,
            errorBlock = $('variation_manager_sku_form_error'),
            data;

        data = $('variation_manager_sku_form').serialize(true);

        errorBlock.hide();

        if (data.sku == '') {
            errorBlock.show();
            errorBlock.innerHTML = M2ePro.text.empty_sku_error;
            return;
        }

        data.product_id = variationProductManagePopup.productId;

        new Ajax.Request(M2ePro.url.variationProductSetListingProductSku, {
            method: 'post',
            parameters: data,
            onSuccess: function (transport) {

                errorBlock.hide();
                var response = self.parseResponse(transport);
                if(response.success) {
                    manageVariationSkuPopUp.close();
                    self.setGeneralIdOwner(1, true);
                } else {
                    errorBlock.show();
                    errorBlock.innerHTML = response.msg;
                }
            }
        });
    },

    // ---------------------------------------

    changeChannelAttributes: function(el)
    {
        $('channel_attributes_view').hide();

        $('variation_manager_channel_attributes_form').show();

        this.channelAttributesForm = new varienForm('variation_manager_channel_attributes_form', false);
    },

    setChannelAttributes: function()
    {
        var self = this,
            form = $('variation_manager_channel_attributes_form');

        if(this.channelAttributesForm.validate()) {

            var data = form.serialize(true);
            data.product_id = variationProductManagePopup.productId;

            new Ajax.Request(M2ePro.url.variationProductSetChannelAttributes, {
                method: 'post',
                parameters: data,
                onSuccess: function (transport) {
                    var response = self.parseResponse(transport);
                    if (response.success) {
                        self.reloadVariationsGrid();
                        self.reloadSettings();
                    }
                }
            });
        }
    },

    cancelChannelAttributes: function(el) {
        $('channel_attributes_view').show();

        $('variation_manager_channel_attributes_form').hide();
        $('variation_manager_channel_attributes_form').reset();
    },

    // ---------------------------------------

    setSwatchImagesAttribute: function()
    {
        var self = this,
            el = $('swatch_images_attributes');

        new Ajax.Request(M2ePro.url.variationProductSetSwatchImagesAttribute, {
            method: 'post',
            parameters: {
                product_id: variationProductManagePopup.productId,
                attribute: el.value
            },
            onSuccess: function (transport) {
                var response = self.parseResponse(transport);
                if (response.success) {
                    self.reloadSettings();
                }
            }
        });
    },

    // ---------------------------------------

    changeMatchedAttributes: function(el)
    {
        $$('.variation_manager_attributes_walmart_value').each(function(el){
            el.hide();
        });

        $$('.variation_manager_attributes_walmart_select').each(function(el){
            el.show();
        });

        el.hide();
        el.next().show();
        el.next().next().show();
    },

    // ---------------------------------------

    isValidAttributes: function()
    {
        var self = this,
            existedValues = [],
            isValid = true,
            form = $('variation_manager_attributes_form');

        if (!form || (form && form.serialize() == '')) {
            return true;
        }
        var data = form.serialize(true);

        form.select('.validation-advice').each(function(el){
            el.hide();
        });

        if (typeof data['variation_attributes[walmart_attributes][]'] == 'string') {

            if (data['variation_attributes[walmart_attributes][]'] != '') {
                return true;
            }

            var errorEl = form.select('.validation-advice')[0];
            errorEl.show();
            errorEl.update(M2ePro.text.variation_manage_matched_attributes_error);

            return false;
        }

        var i = 0;
        data['variation_attributes[walmart_attributes][]'].each(function(attrVal){
            if(attrVal != '' && existedValues.indexOf(attrVal) === -1) {
                existedValues.push(attrVal);
            } else {
                isValid = false;

                var errorEl = $('variation_manager_attributes_error_'+i);
                errorEl.show();
                if(attrVal == '') {
                    errorEl.update(M2ePro.text.variation_manage_matched_attributes_error);
                } else {
                    errorEl.update(M2ePro.text.variation_manage_matched_attributes_error_duplicate)
                }
            }
            i++;
        });

        return isValid;
    },

    setMatchedAttributes: function()
    {
        var self = this,
            data;

        if(!self.isValidAttributes()) {
            return;
        }

        $('variation_manager_attributes_form').select('.validation-advice').each(function(el){
            el.hide();
        });

        data = $('variation_manager_attributes_form').serialize(true);
        data.product_id = variationProductManagePopup.productId;

        new Ajax.Request(M2ePro.url.variationProductSetMatchedAttributes, {
            method: 'post',
            parameters: data,
            onSuccess: function (transport) {
                var response = self.parseResponse(transport);
                if (response.success) {
                    self.reloadVariationsGrid();

                    if (response['vocabulary_attributes']) {
                        self.openVocabularyAttributesPopUp(response['vocabulary_attributes']);
                    }
                }
            }
        });
    },

    cancelMatchedAttributes: function(el)
    {
        $$('.variation_manager_attributes_walmart_value').each(function(el){
            el.show();
        });

        $$('.variation_manager_attributes_walmart_select').each(function(el){
            el.hide();
        });

        $('variation_manager_attributes_form').select('.validation-advice').each(function(el){
            el.hide();
        });

        el.hide();
        el.previous().show();
        el.next().hide();
    },

    // ---------------------------------------

    renderMatchedAttributesNotSetView: function(type)
    {
        var self = this,
            form = $('variation_manager_attributes_form'),
            tBody = form.down('tbody');

        tBody.update();

        $H(self.matchedAttributes).each(function (attribute) {
            var tr = new Element('tr'),
                tdLabel = new Element('td', {
                    class: 'label',
                    style: 'border-right: 1px solid #D6D6D6 !important;'
                }),
                label = new Element('td'),
                tdValue = new Element('td', {
                    class: 'value'
                }),
                valueSpan = new Element('span', {
                    style: 'color: red;'
                });

            label.innerHTML = attribute.key;
            valueSpan.innerHTML = M2ePro.text.not_set;

            tdLabel.insert({ bottom: label });
            tdValue.insert({ bottom: valueSpan });

            tr.insert({ bottom: tdLabel });
            tr.insert({ bottom: tdValue });

            tBody.insert({ bottom: tr });
        });

        var tr = new Element('tr'),
            tdBtns = new Element('td', {
                class: 'label',
                colspan: '2',
                style: 'text-align: right;'
            }),
            setBtn = new Element('button');

        setBtn.update(M2ePro.text.set_attributes);
        setBtn.observe('click', function(event) {
            if (self.matchingType === self.MATCHING_TYPE_VIRTUAL_WALMART) {
                self.renderMatchedAttributesVirtualWalmartView();
            }

            if (self.matchingType === self.MATCHING_TYPE_VIRTUAL_MAGENTO) {
                self.renderMatchedAttributesVirtualMagentoView();
            }
        });

        tdBtns.insert({ bottom: setBtn });
        tr.insert({ bottom: tdBtns });
        tBody.insert({ bottom: tr });

    },

    renderMatchedAttributesVirtualWalmartView: function()
    {
        var self = this,
            form = $('variation_manager_attributes_form'),
            tBody = form.down('tbody');

        tBody.update();
        self.selectedDestinationAttributes = [];

        var prematchedAttributes = [];
        var i = 0;
        $H(self.matchedAttributes).each(function (attribute) {

            var tr = new Element('tr'),
                tdLabel = new Element('td', {
                    class: 'label value',
                    style: 'border-right: 1px solid #D6D6D6 !important;'
                }),
                labelMagentoAttr = new Element('label', {
                    class: 'magento-attribute-name'
                }),
                inputVirtualAttribute = new Element('input', {
                    style: 'display: none',
                    value: attribute.key,
                    type: 'hidden',
                    disabled: 'disabled',
                    class: 'virtual-walmart-attribute-name-value',
                    name: 'variation_attributes[virtual_walmart_attributes]['+i+']'
                }),
                selectVirtualAttributeOption = new Element('select', {
                    style: 'display: none; width: 175px;',
                    disabled: 'disabled',
                    class: 'required-entry virtual-walmart-option',
                    name: 'variation_attributes[virtual_walmart_option]['+i+']'
                }),
                selectVirtualAttributeOptionGroup = new Element('optgroup', {
                    label: attribute.key
                }),
                labelVirtualAttributeAndOption = new Element('span', {
                    style: 'display: none',
                    class: 'virtual-walmart-attribute-and-option'
                }),
                spanLeftHelpIcon = new Element('span', {
                    style: 'display: none',
                    class: 'left-help-icon'
                }),
                tdValue = new Element('td', {
                    class: 'value'
                }),
                inputMagentoAttr = new Element('input', {
                    value: attribute.key,
                    type: 'hidden',
                    class: 'magento-attribute-name-value',
                    name: 'variation_attributes[magento_attributes]['+i+']'
                }),
                selectWalmartAttr = new Element('select', {
                    class: 'required-entry M2ePro-walmart-attribute-unique-value walmart-attribute-name',
                    name: 'variation_attributes[walmart_attributes]['+i+']'
                }),
                spanVirtualAttribute = new Element('span', {
                    style: 'display: none',
                    class: 'virtual-walmart-attribute-name'
                }),
                spanRightHelpIcon = new Element('span', {
                    style: 'display: none',
                    class: 'right-help-icon'
                });

            var helpIconTpl = $('product_search_help_icon_tpl');

            spanLeftHelpIcon.update(helpIconTpl.innerHTML);
            spanLeftHelpIcon.down('.tool-tip-message-text').update(M2ePro.text.help_icon_magento_greater_left);
            spanRightHelpIcon.update(helpIconTpl.innerHTML);
            spanRightHelpIcon.down('.tool-tip-message-text').update(M2ePro.text.help_icon_magento_greater_right);

            var attributeStr = attribute.key;
            if (attribute.key.length > 13) {
                attributeStr = attribute.key.substr(0, 12) + '...';
                labelVirtualAttributeAndOption.title = attribute.key;
                spanVirtualAttribute.title = attribute.key;
            }

            if (attribute.key.length < 31) {
                labelMagentoAttr.update(attribute.key);
            } else {
                labelMagentoAttr.update(attribute.key.substr(0, 28) + '...');
                labelMagentoAttr.title = attribute.key;
            }

            spanVirtualAttribute.update(attributeStr+' (<span>&ndash;</span>)');
            labelVirtualAttributeAndOption.update(attributeStr+' (<a href="javascript:void(0);"></a>)');
            labelVirtualAttributeAndOption.down('a').title = '';

            labelVirtualAttributeAndOption.down('a').observe('click', function(event) {
                labelVirtualAttributeAndOption.hide();
                selectVirtualAttributeOption.show();
                selectVirtualAttributeOption.value = '';
                spanVirtualAttribute.down('span').update('&ndash;');
                spanVirtualAttribute.down('span').title = '';
            });

            var option = new Element('option', {
                value: ''
            });
            selectWalmartAttr.insert({bottom: option});

            self.destinationAttributes.each(function(destinationAttribute){
                var option = new Element('option', {
                    value: destinationAttribute
                });
                option.update(destinationAttribute);
                selectWalmartAttr.insert({bottom: option});

                if (attribute.value == destinationAttribute) {
                    selectWalmartAttr.value = destinationAttribute;
                    prematchedAttributes.push(selectWalmartAttr);
                }
            });
            selectWalmartAttr.prevValue = '';

            selectWalmartAttr.observe('change', function(event) {

                var result = true;
                if (selectWalmartAttr.value != '' && inputMagentoAttr.value != selectWalmartAttr.value &&
                    self.productAttributes.indexOf(selectWalmartAttr.value) !== -1) {
                    result = false;

                    if (attribute.value == null) {
                        alert(M2ePro.text.duplicate_walmart_attribute_error);
                    }
                    selectWalmartAttr.value = '';
                }
                attribute.value = null;

                var prevValueIndex = self.selectedDestinationAttributes.indexOf(selectWalmartAttr.prevValue);
                if (prevValueIndex > -1) {
                    self.selectedDestinationAttributes.splice(prevValueIndex, 1);
                }

                if (selectWalmartAttr.value != '') {
                    self.selectedDestinationAttributes.push(selectWalmartAttr.value);
                }
                selectWalmartAttr.prevValue = selectWalmartAttr.value;

                form.select('select').each(function(el){
                    result = Validation.get('M2ePro-walmart-attribute-unique-value').test($F(el), el) ? result : false;
                });

                if (result && self.selectedDestinationAttributes.length == self.destinationAttributes.length) {
                    self.showVirtualWalmartAttributes();
                } else {
                    self.hideVirtualWalmartAttributes();
                }
            });

            selectVirtualAttributeOption.observe('change', function(event) {
                var value = selectVirtualAttributeOption.value;

                labelVirtualAttributeAndOption.show();
                selectVirtualAttributeOption.hide();

                if (attributeStr.length + value.length < 28) {
                    spanVirtualAttribute.down('span').update(value);
                    spanVirtualAttribute.down('span').title = '';
                    labelVirtualAttributeAndOption.down('a').update(value);
                } else {
                    spanVirtualAttribute.down('span').update(value.substr(0, 24 - attributeStr.length) + '...');
                    spanVirtualAttribute.down('span').title = value;
                    labelVirtualAttributeAndOption.down('a').update(value.substr(0, 24 - attributeStr.length) + '...');
                }

                labelVirtualAttributeAndOption.down('a').title = M2ePro.text.change_option + ' "' + value + '"';
            });

            var option = new Element('option', {
                value: ''
            });
            selectVirtualAttributeOption.insert({bottom: option});

            self.magentoVariationSet[attribute.key].each(function(optionValue){
                var option = new Element('option', {
                    value: optionValue
                });
                option.update(optionValue);
                selectVirtualAttributeOptionGroup.insert({bottom: option});
            });
            selectVirtualAttributeOption.insert({bottom: selectVirtualAttributeOptionGroup});

            tdLabel.insert({ bottom: labelMagentoAttr });
            tdLabel.insert({ bottom: inputVirtualAttribute });
            tdLabel.insert({ bottom: labelVirtualAttributeAndOption });
            tdLabel.insert({ bottom: selectVirtualAttributeOption });
            tdLabel.insert({ bottom: spanLeftHelpIcon });
            tdValue.insert({ bottom: inputMagentoAttr });
            tdValue.insert({ bottom: selectWalmartAttr });
            tdValue.insert({ bottom: spanVirtualAttribute });
            tdValue.insert({ bottom: spanRightHelpIcon });

            tr.insert({ bottom: tdLabel });
            tr.insert({ bottom: tdValue });

            tBody.insert({ bottom: tr });

            i++;
        });

        var tr = new Element('tr'),
            tdBtns = new Element('td', {
                class: 'label',
                colspan: '2',
                style: 'text-align: right;'
            }),
            cancelBtn = new Element('a', {
                href: 'javascript:void(0);',
                style: 'margin-left: 9px;'
            }),
            confirmBtn = new Element('button', {
                style: 'margin-left: 9px;'
            });

        cancelBtn.update(M2ePro.translator.translate('Cancel'));
        confirmBtn.update(M2ePro.translator.translate('Confirm'));

        cancelBtn.observe('click', function(event) {
            self.renderMatchedAttributesNotSetView();
        });
        confirmBtn.observe('click', function(event) {
            form.select('.validation-advice').each(function(el){
                el.hide();
            });

            var result = true;
            form.select('select').each(function(el){
                el.classNames().each(function (className) {
                    var validationResult = Validation.test(className, el);
                    result = validationResult ? result : false;

                    if (!validationResult) {
                        throw $break;
                    }
                });
            });

            if (result) {
                var data = form.serialize(true);
                data.product_id = variationProductManagePopup.productId;

                new Ajax.Request(M2ePro.url.variationProductSetMatchedAttributes, {
                    method: 'post',
                    parameters: data,
                    onSuccess: function (transport) {
                        var response = self.parseResponse(transport);
                        if (response.success) {
                            self.reloadVariationsGrid();

                            if (response['vocabulary_attributes']) {
                                self.openVocabularyAttributesPopUp(response['vocabulary_attributes']);
                            }
                        }
                    }
                });
            }
        });

        tdBtns.insert({ bottom: cancelBtn });
        tdBtns.insert({ bottom: confirmBtn });
        tr.insert({ bottom: tdBtns });
        tBody.insert({ bottom: tr });

        prematchedAttributes.each(function(el){
            el.simulate('change');
        });

        tBody.select('.tool-tip-image').each(function(element) {
            element.observe('mouseover', MagentoFieldTipObj.showToolTip);
            element.observe('mouseout', MagentoFieldTipObj.onToolTipIconMouseLeave);
        });

        tBody.select('.tool-tip-message').each(function(element) {
            element.observe('mouseout', MagentoFieldTipObj.onToolTipMouseLeave);
            element.observe('mouseover', MagentoFieldTipObj.onToolTipMouseEnter);
        });
    },

    showVirtualWalmartAttributes: function()
    {
        var self = this,
            form = $('variation_manager_attributes_form');

        var virtualWalmartAttr = form.select('select.walmart-attribute-name[value=""]');
        virtualWalmartAttr.each(function(el){
            el.disable().hide();

            var tr = el.up('tr');
            tr.down('.magento-attribute-name-value').disable();
            tr.down('.virtual-walmart-attribute-name').show();
            tr.down('.magento-attribute-name').hide();
            tr.down('.virtual-walmart-attribute-name-value').enable();
            tr.down('.virtual-walmart-option').enable().show();
            tr.down('.right-help-icon').show();
            tr.down('.left-help-icon').show();
        });
    },

    hideVirtualWalmartAttributes: function()
    {
        var self = this,
            form = $('variation_manager_attributes_form');

        var virtualWalmartAttr = form.select('select.walmart-attribute-name[value=""]');
        virtualWalmartAttr.each(function(el){
            el.enable().show();

            var tr = el.up('tr');
            tr.down('.magento-attribute-name-value').enable();
            tr.down('.virtual-walmart-attribute-name').hide();
            tr.down('.magento-attribute-name').show();
            tr.down('.virtual-walmart-attribute-name-value').disable();
            tr.down('.virtual-walmart-option').disable().hide();
            tr.down('.virtual-walmart-attribute-and-option').hide();
            tr.down('.right-help-icon').hide();
            tr.down('.left-help-icon').hide();
        });
    },

    // ---------------------------------------

    renderMatchedAttributesVirtualMagentoView: function()
    {
        var self = this,
            form = $('variation_manager_attributes_form'),
            tBody = form.down('tbody');

        tBody.update();

        var prematchedAttributes = [];
        var i = 0;
        $H(self.matchedAttributes).each(function (attribute) {

            var tr = new Element('tr'),
                tdLabel = new Element('td', {
                    class: 'label',
                    style: 'border-right: 1px solid #D6D6D6 !important;'
                }),
                labelMagentoAttr = new Element('label'),
                tdValue = new Element('td', {
                    class: 'value'
                }),
                inputMagentoAttr = new Element('input', {
                    value: attribute.key,
                    type: 'hidden',
                    name: 'variation_attributes[magento_attributes]['+i+']'
                }),
                selectWalmartAttr = new Element('select', {
                    class: 'required-entry M2ePro-walmart-attribute-unique-value walmart-attribute-name',
                    name: 'variation_attributes[walmart_attributes]['+i+']'
                });

            if (attribute.key.length < 31) {
                labelMagentoAttr.update(attribute.key);
            } else {
                labelMagentoAttr.update(attribute.key.substr(0, 28) + '...');
                labelMagentoAttr.title = attribute.key;
            }

            var option = new Element('option', {
                value: ''
            });
            selectWalmartAttr.insert({bottom: option});

            self.destinationAttributes.each(function(destinationAttribute){
                var option = new Element('option', {
                    value: destinationAttribute
                });
                option.update(destinationAttribute);
                selectWalmartAttr.insert({bottom: option});

                if (attribute.value == destinationAttribute) {
                    selectWalmartAttr.value = destinationAttribute;
                    prematchedAttributes.push(selectWalmartAttr);
                }
            });
            selectWalmartAttr.prevValue = '';

            selectWalmartAttr.observe('change', function(event) {
                var result = true;
                if (selectWalmartAttr.value != '' && inputMagentoAttr.value != selectWalmartAttr.value &&
                    self.destinationAttributes.indexOf(inputMagentoAttr.value) !== -1) {
                    result = false;

                    if (attribute.value == null) {
                        alert(M2ePro.text.duplicate_magento_attribute_error);
                    }
                    selectWalmartAttr.value = '';
                }
                attribute.value = null;

                form.select('select.walmart-attribute-name').each(function(el){
                    el.classNames().each(function (className) {
                        var v = Validation.get(className),
                            validationResult = v.test($F(el), el);

                        result = validationResult ? result : false;

                        if (!validationResult) {
                            throw $break;
                        }
                    });
                });

                if (result) {
                    self.showVirtualMagentoAttributes(i);
                } else {
                    self.hideVirtualMagentoAttributes();
                }
            });

            tdLabel.insert({ bottom: labelMagentoAttr });
            tdValue.insert({ bottom: inputMagentoAttr });
            tdValue.insert({ bottom: selectWalmartAttr });

            tr.insert({ bottom: tdLabel });
            tr.insert({ bottom: tdValue });

            tBody.insert({ bottom: tr });

            i++;
        });

        var tr = new Element('tr',{
                class: 'buttons-row'
            }),
            tdBtns = new Element('td', {
                class: 'label',
                colspan: '2',
                style: 'text-align: right;'
            }),
            cancelBtn = new Element('a', {
                href: 'javascript:void(0);',
                style: 'margin-left: 9px;'
            }),
            confirmBtn = new Element('button', {
                style: 'margin-left: 9px;'
            });

        cancelBtn.update(M2ePro.translator.translate('Cancel'));
        confirmBtn.update(M2ePro.translator.translate('Confirm'));

        cancelBtn.observe('click', function(event) {
            self.renderMatchedAttributesNotSetView();
        });
        confirmBtn.observe('click', function(event) {
            form.select('.validation-advice').each(function(el){
                el.hide();
            });

            var result = true;
            form.select('select', 'input').each(function(el){
                el.classNames().each(function (className) {
                    var validationResult = Validation.test(className, el);
                    result = validationResult ? result : false;

                    if (!validationResult) {
                        throw $break;
                    }
                });
            });

            if (result) {
                var data = form.serialize(true);
                data.product_id = variationProductManagePopup.productId;

                new Ajax.Request(M2ePro.url.variationProductSetMatchedAttributes, {
                    method: 'post',
                    parameters: data,
                    onSuccess: function (transport) {
                        var response = self.parseResponse(transport);
                        if (response.success) {
                            self.reloadVariationsGrid();

                            if (response['vocabulary_attributes']) {
                                self.openVocabularyAttributesPopUp(response['vocabulary_attributes']);
                            }
                        }
                    }
                });
            }
        });

        tdBtns.insert({ bottom: cancelBtn });
        tdBtns.insert({ bottom: confirmBtn });
        tr.insert({ bottom: tdBtns });
        tBody.insert({ bottom: tr });

        prematchedAttributes.each(function(el){
            el.simulate('change');
        });
    },

    showVirtualMagentoAttributes: function(lastAttributeIndex)
    {
        var self = this,
            form = $('variation_manager_attributes_form'),
            buttonsRow = form.down('.buttons-row');

        form.select('tr.virtual-attribute').each(function(el){
            el.remove();
        });

        var selectedValues = [];
        form.select('select.walmart-attribute-name').each(function(el){
            selectedValues.push(el.value);
        });

        var i = lastAttributeIndex;
        self.destinationAttributes.each(function(attribute){
            if (selectedValues.indexOf(attribute) !== -1) {
                return true;
            }
            var tr = new Element('tr', {
                    class: 'virtual-attribute'
                }),
                tdLabel = new Element('td', {
                    class: 'label',
                    style: 'border-right: 1px solid #D6D6D6 !important;'
                }),
                labelMagentoAttr = new Element('label'),
                spanLeftHelpIcon = new Element('span', {
                    class: 'left-help-icon'
                }),
                tdValue = new Element('td', {
                    class: 'value'
                }),
                inputMagentoAttr = new Element('input', {
                    value: attribute,
                    type: 'hidden',
                    name: 'variation_attributes[virtual_magento_attributes]['+i+']'
                }),
                spanVirtualAttribute = new Element('span', {
                    style: 'display: none'
                }),
                spanRightHelpIcon = new Element('span', {
                    class: 'right-help-icon'
                });

            var helpIconTpl = $('product_search_help_icon_tpl');

            spanLeftHelpIcon.update(helpIconTpl.innerHTML);
            spanLeftHelpIcon.down('.tool-tip-message-text').update(M2ePro.text.help_icon_walmart_greater_left);
            spanRightHelpIcon.update(helpIconTpl.innerHTML);
            spanRightHelpIcon.down('.tool-tip-message-text').update(M2ePro.text.help_icon_walmart_greater_right);

            var attributeStr = attribute;
            if (attribute.length > 13) {
                attributeStr = attribute.substr(0, 12) + '...';
                labelMagentoAttr.title = attribute;
                spanVirtualAttribute.title = attribute;
            }

            labelMagentoAttr.update(attributeStr+' (<span>&ndash;</span>)');

            if (self.walmartVariationSet === false) {
                spanVirtualAttribute.update(attributeStr+' (<span></span>)');
                spanVirtualAttribute.show();

                var virtualAttrOption = new Element('input', {
                    type: 'text',
                    style: 'width: 153px;',
                    class: 'required-entry',
                    name: 'variation_attributes[virtual_magento_option]['+i+']'
                });

                virtualAttrOption.observe('keyup', function(event) {
                    var value = virtualAttrOption.value;

                    if (value == '') {
                        labelMagentoAttr.down('span').update('&ndash;');
                        labelMagentoAttr.down('span').title = '';
                        return;
                    }

                    if (attributeStr.length + value.length < 21) {
                        labelMagentoAttr.down('span').update(value);
                        labelMagentoAttr.down('span').title = '';
                    } else {
                        labelMagentoAttr.down('span').update(value.substr(0, 20 - attributeStr.length) + '...');
                        labelMagentoAttr.down('span').title = value;
                    }
                });

                labelMagentoAttr.insert({bottom: spanLeftHelpIcon});
                spanVirtualAttribute.down('span').insert({bottom: virtualAttrOption});

                tdLabel.insert({bottom: labelMagentoAttr});
                tdValue.insert({bottom: inputMagentoAttr});
                tdValue.insert({bottom: spanVirtualAttribute});
                tdValue.insert({bottom: spanRightHelpIcon});
            } else {
                var virtualAttrOption = new Element('select', {
                    style: 'width: 255px;',
                    class: 'required-entry virtual-magento-option',
                    name: 'variation_attributes[virtual_magento_option]['+i+']'
                }),
                virtualAttrOptionGroup = new Element('optgroup', {
                    label: attribute
                });

                spanVirtualAttribute.update(attributeStr+' (<a href="javascript:void(0);"></a>)');
                spanVirtualAttribute.down('a').title = '';

                spanVirtualAttribute.down('a').observe('click', function(event) {
                    spanVirtualAttribute.hide();
                    virtualAttrOption.show();
                    virtualAttrOption.value = '';
                    labelMagentoAttr.down('span').update('&ndash;');
                    labelMagentoAttr.down('span').title = '';
                });

                var option = new Element('option', {
                    value: ''
                });
                virtualAttrOption.insert({bottom: option});

                self.walmartVariationSet[attribute].each(function(optionValue){
                    var option = new Element('option', {
                        value: optionValue
                    });
                    option.update(optionValue);
                    virtualAttrOptionGroup.insert({bottom: option});
                });
                virtualAttrOption.insert({bottom: virtualAttrOptionGroup});

                virtualAttrOption.observe('change', function(event) {
                    var value = virtualAttrOption.value;

                    spanVirtualAttribute.show();
                    virtualAttrOption.hide();

                    if (attributeStr.length + value.length < 28) {
                        labelMagentoAttr.down('span').update(value);
                        labelMagentoAttr.down('span').title = '';
                    } else {
                        labelMagentoAttr.down('span').update(value.substr(0, 22 - attributeStr.length) + '...');
                        labelMagentoAttr.down('span').title = value;
                    }

                    if (attributeStr.length + value.length < 45) {
                        spanVirtualAttribute.down('a').update(value);
                    } else {
                        spanVirtualAttribute.down('a').update(value.substr(0, 40 - attributeStr.length) + '...');
                    }
                    spanVirtualAttribute.down('a').title = M2ePro.text.change_option + ' "' + value + '"';
                });

                labelMagentoAttr.insert({bottom: spanLeftHelpIcon});
                tdLabel.insert({bottom: labelMagentoAttr});
                tdValue.insert({bottom: inputMagentoAttr});
                tdValue.insert({bottom: spanVirtualAttribute});
                tdValue.insert({bottom: virtualAttrOption});
                tdValue.insert({bottom: spanRightHelpIcon});
            }

            tr.insert({bottom: tdLabel});
            tr.insert({bottom: tdValue});

            buttonsRow.insert({ before: tr });

            i++;
        });

        form.select('.tool-tip-image').each(function(element) {
            element.observe('mouseover', MagentoFieldTipObj.showToolTip);
            element.observe('mouseout', MagentoFieldTipObj.onToolTipIconMouseLeave);
        });

        form.select('.tool-tip-message').each(function(element) {
            element.observe('mouseout', MagentoFieldTipObj.onToolTipMouseLeave);
            element.observe('mouseover', MagentoFieldTipObj.onToolTipMouseEnter);
        });
    },

    hideVirtualMagentoAttributes: function()
    {
        var self = this,
            form = $('variation_manager_attributes_form');

        form.select('tr.virtual-attribute').each(function(el){
            el.remove();
        });
    },

    // ---------------------------------------

    reloadSettings: function(callback, hideMask)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.viewVariationsSettingsAjax, {
            method: 'post',
            parameters: {
                product_id : variationProductManagePopup.productId
            },
            onSuccess: function (transport) {

                var response = self.parseResponse(transport);

                $('walmartVariationProductManageTabs_settings_content').update(response.html);
                self.initSettingsTab();

                var img = $('walmartVariationProductManageTabs_settings').down('img');

                img.hide();
                if (response.error_icon != '') {
                    img.src = M2ePro.url.get('m2epro_skin_url') + '/images/' + response.error_icon + '.png';
                    img.show();
                }

                if(callback) {
                    callback.call();
                }
            }
        });

        hideMask && $('loading-mask').hide();
    },

    loadVariationsGrid: function(showMask)
    {
        var self = this;
        showMask && $('loading-mask').show();

        var gridIframe = $('walmartVariationsProductManageVariationsGridIframe');

        if(gridIframe) {
            gridIframe.remove();
        }

        var iframe = new Element('iframe', {
            id: 'walmartVariationsProductManageVariationsGridIframe',
            src: $('walmartVariationsProductManageVariationsGridIframeUrl').value,
            width: '100%',
            height: '100%',
            style: 'border: none;'
        });

        $('walmartVariationsProductManageVariationsGrid').insert(iframe);

        Event.observe($('walmartVariationsProductManageVariationsGridIframe'), 'load', function() {
            $('loading-mask').hide();
        });
    },

    reloadVariationsGrid: function()
    {
        var gridIframe = $('walmartVariationsProductManageVariationsGridIframe');

        if(!gridIframe) {
            return;
        }
        gridIframe.contentWindow.ListingGridHandlerObj.actionHandler.gridHandler.unselectAllAndReload();
    },

    // ---------------------------------------

    openVariationsTab: function (createNewAsin) {
        walmartVariationProductManageTabsJsTabs.showTabContent(walmartVariationProductManageTabsJsTabs.tabs[0]);
        $('walmartVariationsProductManageVariationsGridIframe').contentWindow.ListingGridHandlerObj.showNewChildForm();
    },

    // ---------------------------------------

    reloadVocabulary: function(callback, hideMask)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.viewVocabularyAjax, {
            method: 'post',
            parameters: {
                product_id : variationProductManagePopup.productId
            },
            onSuccess: function (transport) {
                $('walmartVariationProductManageTabs_vocabulary_content').update(transport.responseText);

                if(callback) {
                    callback.call();
                }
            }
        });

        hideMask && $('loading-mask').hide();
    },

    saveAutoActionSettings: function()
    {
        new Ajax.Request(M2ePro.url.saveAutoActionSettings, {
            method: 'post',
            parameters: $('auto_action_settings_form').serialize(true)
        });
    },

    removeAttributeFromVocabulary: function (el)
    {
        var self = this,
            attrRowEl = el.up('.matched-attributes-pair');

        if(!confirm(M2ePro.text.confirm)) {
            return;
        }

        new Ajax.Request(M2ePro.url.removeAttributeFromVocabulary, {
            method: 'post',
            parameters: {
                magento_attr : decodeHtmlentities(el.up().down('.magento-attribute-name').innerHTML),
                channel_attr : decodeHtmlentities(el.up().down('.channel-attribute-name').innerHTML)
            },
            onSuccess: function (transport) {
                self.reloadVocabulary();
            }
        });
    },

    removeOptionFromVocabulary: function (el)
    {
        var self = this,
            optionGroupRowEl = el.up('.channel-attribute-options-group'),
            attrOptionsRowEl = el.up('.magento-attribute-options');

        if(!confirm(M2ePro.text.confirm)) {
            return;
        }

        new Ajax.Request(M2ePro.url.removeOptionFromVocabulary, {
            method: 'post',
            parameters: {
                product_option : decodeHtmlentities(optionGroupRowEl.down('.product-option').innerHTML),
                product_options_group : decodeHtmlentities(optionGroupRowEl.down('.product-options-group').innerHTML),
                channel_attr : decodeHtmlentities(optionGroupRowEl.down('.channel-attribute-name').innerHTML)
            },
            onSuccess: function (transport) {
                self.reloadVocabulary();
            }
        });
    }

    // ---------------------------------------
});