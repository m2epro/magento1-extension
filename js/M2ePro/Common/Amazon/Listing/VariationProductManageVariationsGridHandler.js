CommonAmazonListingVariationProductManageVariationsGridHandler = Class.create(CommonListingGridHandler, {

    // ---------------------------------------

    getComponent: function()
    {
        return 'amazon';
    },

    // ---------------------------------------

    getMaxProductsInPart: function()
    {
        return 1000;
    },

    // ---------------------------------------

    prepareActions: function($super)
    {
        $super();
        this.actionHandler = new CommonAmazonListingActionHandler(this);
        this.templateDescriptionHandler = new CommonAmazonListingTemplateDescriptionHandler(this);

        this.actions = Object.extend(this.actions, {
            deleteAndRemoveAction: this.actionHandler.deleteAndRemoveAction.bind(this.actionHandler)
        });
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

    afterInitPage: function($super)
    {
        $super();

        $$('.attributes-options-filter').each(this.initAttributesOptionsFilter, this);
    },

    initAttributesOptionsFilter: function(filterEl)
    {
        var srcElement = Element.down(filterEl, 'select');

        srcElement.observe('change', this.onAttributesOptionsFilterChange.bind(this));

        var valuesDiv = Element.down(filterEl, '.attributes-options-filter-values');
        valuesDiv.optionsCount = valuesDiv.childElementCount;

        if(valuesDiv.optionsCount == srcElement.childElementCount - 1) {
            srcElement.hide();
        }

        valuesDiv.optionsIterator = 0;
        valuesDiv.childElements().each(function(attrValue) {

            var removeImg = Element.down(attrValue, '.filter-param-remove'),
                attrName = Element.down(attrValue, 'input[type="hidden"]'),
                selectedOption = Element.down(filterEl, 'select option[value="' + attrName.value + '"]');

            selectedOption.hide();

            valuesDiv.optionsIterator++;

            removeImg.show();
            removeImg.observe('click', function() {
                valuesDiv.optionsCount--;
                selectedOption.show();
                srcElement.show();
                Element.remove(attrValue);
            });
        }, this);
    },

    onAttributesOptionsFilterChange: function(e)
    {
        var srcElement = e.target || e.srcElement,
            parentDiv = Element.up(srcElement, '.attributes-options-filter'),
            valuesDiv = Element.down(parentDiv, '.attributes-options-filter-values'),
            selectedOption = Element.down(srcElement, '[value="' + srcElement.value + '"]');

        selectedOption.hide();

        valuesDiv.optionsCount++;
        valuesDiv.optionsIterator++;

        srcElement.enable();
        if(valuesDiv.optionsCount == srcElement.childElementCount - 1) {
            srcElement.hide();
        }

        var filterName = parentDiv.id.replace('attributes-options-filter_', '');

        var newOptionContainer = new Element('div'),
            newOptionLabel = new Element('div'),
            newOptionValue = new Element('input', {
                type: 'text',
                name: filterName + '[' + valuesDiv.optionsIterator + '][value]'
            }),
            newOptionAttr = new Element('input', {
                type: 'hidden',
                name: filterName + '[' + valuesDiv.optionsIterator + '][attr]',
                value: srcElement.value
            }),
            removeImg = Element.clone(Element.down(parentDiv, '.attributes-options-filter-selector .filter-param-remove'));

        newOptionLabel.innerHTML = srcElement.value + ': ';
        removeImg.show();

        Event.observe(newOptionValue, 'keypress', this.getGridObj().filterKeyPress.bind(this.getGridObj()));

        newOptionContainer.insert({ bottom: newOptionLabel });
        newOptionContainer.insert({ bottom: newOptionValue });
        newOptionContainer.insert({ bottom: newOptionAttr });
        newOptionContainer.insert({ bottom: removeImg });

        valuesDiv.insert({ bottom: newOptionContainer });

        removeImg.observe('click', function() {
            valuesDiv.optionsCount--;
            selectedOption.show();
            srcElement.show();
            newOptionContainer.remove();
        }, this);

        srcElement.value = '';
    },

    editProductOptions: function(el, attributes, variationsTree, listingProductId)
    {
        this.renderProductOptionsForm(el, attributes, variationsTree, listingProductId);
        this.validateAttributeOptions(el, variationsTree);

        var mainContainer = Element.up(el, '.product-options-main'),
            listContainer = Element.down(mainContainer, '.product-options-list'),
            form = mainContainer.down('.product-options-edit'),
            options = form.select('select.product-option');

        var productOptions = [];
        listContainer.select('.attribute-row').each(function(el) {
            productOptions.push(el.down('.value').innerHTML);
        });

        var i = 0;
        options.each(function(el) {
            el.setValue(productOptions[i]).simulate('change');
            i++;
        });
    },

    renderProductOptionsForm: function(el, attributes, variationsTree, listingProductId)
    {
        var mainContainer = Element.up(el, '.product-options-main'),
            editContainer = Element.down(mainContainer, '.product-options-edit'),
            listContainer = Element.down(mainContainer, '.product-options-list'),
            self = this;

        el.hide();
        listContainer && listContainer.hide();

        for (var i = 0; i < attributes.length; i++) {

            var optionContainer = new Element('div'),
                optionLabel = new Element('div'),
                optionValue = new Element('select', {
                    class: 'product-option',
                    name: 'product_options[values][]'
                }),
                optionAttr = new Element('input', {
                    class: 'product-attribute',
                    type: 'hidden',
                    name: 'product_options[attr][]',
                    value: attributes[i]
                });

            optionLabel.update(attributes[i] + ': ');
            optionValue.observe('change', function() {
                self.validateAttributeOptions(el, variationsTree);
            });

            optionContainer.insert({ bottom: optionLabel });
            optionContainer.insert({ bottom: optionValue });
            optionContainer.insert({ bottom: optionAttr });

            editContainer.insert({ bottom: optionContainer });
        }

        var confirmBtn = new Element('button', {
                class: 'scalable confirm-btn',
                style: 'margin-top: 8px; margin-right: 9px; float: right;'
            }),
            cancelBtn = new Element('a', {
                href: 'javascript:void(0);',
                class: 'scalable',
                style: 'margin: 7px 8px; float: right;'
            }),
            listingProductIdEl = new Element('input', {
                type: 'hidden',
                name: 'product_id',
                value: listingProductId
            }),
            errorMsg = new Element('p', {
                class: 'error',
                style: 'display: none;'
            });

        confirmBtn.update(M2ePro.text.confirm_word);
        confirmBtn.observe('click', function(event) {
            event.stop();
            var data = editContainer.serialize(true);

            if (!self.validateAttributeOptions(el, variationsTree)) {
                var errorMsg = editContainer.down('p.error');
                errorMsg.show();
                errorMsg.innerHTML= M2ePro.text.error_changing_product_options;
                return;
            }

            new Ajax.Request(self.actionHandler.options.url.setChildListingProductOptions, {
                method: 'post',
                parameters: data,
                onSuccess: function(transport) {

                    var response = self.parseResponse(transport);

                    if (response['vocabulary_attribute_options']) {
                        window.parent.ListingGridHandlerObj.variationProductManageHandler.openVocabularyOptionsPopUp(response['vocabulary_attribute_options']);
                        return;
                    }

                    if (response.success) {
                        self.actionHandler.gridHandler.unselectAllAndReload();
                        $('loading-mask').hide();

                        window.parent.ListingGridHandlerObj.variationProductManageHandler.reloadSettings();
                    }
                }
            });

            el.show();
            listContainer && listContainer.show();
            editContainer.childElements().each(Element.remove);
        });

        cancelBtn.update(M2ePro.text.cancel_word);
        cancelBtn.observe('click', function(event) {
            event.stop();
            el.show();
            listContainer && listContainer.show();
            editContainer.childElements().each(Element.remove);
        });

        editContainer.insert({ bottom: listingProductIdEl });
        editContainer.insert({ bottom: errorMsg });
        editContainer.insert({ bottom: confirmBtn });
        editContainer.insert({ bottom: cancelBtn });
    },

    validateAttributeOptions: function(el, variations, i)
    {
        var variation = $H(variations),
            mainContainer = Element.up(el, '.product-options-main'),
            form = mainContainer.down('.product-options-edit'),
            attributes = form.select('input.product-attribute'),
            options = form.select('select.product-option'),

            index = i || 0,
            valid = false;

        if (index === 0) {
            options.each(function(el) {
                el.disable();
            });
        }

        if (!attributes[index] || !options[index]) {
            if (variation.size() === 0) {
                valid = true;
                options.each(function(el) {
                    if (el.value == '') {
                        valid = false;
                        throw $break;
                    }
                });

                return valid;
            }
        }

        var attr = variation.keys()[0];
        attributes[index].value = attr;

        var oldValue = options[index].value;
        options[index].update();
        options[index].enable();
        options[index].appendChild(new Element('option', {style: 'display: none'}));

        $H(variation.get(attr)).each(function(option) {
            options[index].appendChild(new Element('option', {value: option[0]})).insert(option[0]);

            if (option[0] == oldValue) {
                options[index].value = oldValue;
            }
        });

        if (oldValue) {
            index++;
            valid = this.validateAttributeOptions(el, variation.get(attr)[oldValue], index);
        }

        return valid;
    },

    // ---------------------------------------

    showNewChildForm: function(createNewAsin)
    {
        $('new_variation_child_form_block').show();

        $('add_new_child_button').hide();

        $$('#manage_variations_new_child_product_variation select').each(function(el) {
            el.select('option').invoke('remove');
        });

        $$('#manage_variations_new_child_channel_variation select').each(function(el) {
            el.select('option').invoke('remove');
        });

        $('new_child_product_product_options_error').hide();
        $('new_child_product_channel_options_error').hide();

        ListingGridHandlerObj.validateNewChildAttributeOptions('product');
        ListingGridHandlerObj.validateNewChildAttributeOptions('channel');

        if (createNewAsin) {
            ListingGridHandlerObj.createNewAsinBtn();
        } else {
            ListingGridHandlerObj.selectOptionsBtn();
        }
    },

    closeNewChildForm: function()
    {
        $('new_variation_child_form_block').hide();
        $('add_new_child_button').show();
    },

    // ---------------------------------------

    validateNewChildAttributeOptions: function(type, variations, i)
    {
        if (!$('variation_manager_unused_' + type + '_variations_tree')) {
            return true;
        }

        var variation = $H(variations || decodeHtmlentities($('variation_manager_unused_' + type + '_variations_tree').innerHTML).evalJSON()),
            attributes = $$('.new-child-' + type + '-attribute'),
            options = $$('.new-child-' + type + '-option'),
            index = i || 0,
            valid = false;

        if (type == 'product') {
            var channelOptions = $$('.new-child-channel-option'),
                formData = $('variation_manager_product_options_form').serialize(true);
        }

        if (index === 0) {
            options.each(function(el) {
                el.disable();
            });
        }

        if (!attributes[index] || !options[index]) {
            if (variation.size() === 0) {
                valid = this.validateNewChild();
            }
            return valid;
        }

        var attr = variation.keys()[0];
        attributes[index].value = attr;

        var oldValue = options[index].value;
        options[index].update();
        options[index].enable();
        options[index].appendChild(new Element('option', {style: 'display: none'}));

        if (type == 'product' && formData.create_new_asin == 1) {
            var channelEl = $('new_child_product_channel_option_' + index);

            channelEl.update();
            channelEl.appendChild(new Element('option', {value: oldValue})).insert(oldValue);
            channelEl.value = oldValue;
        }

        $H(variation.get(attr)).each(function(option) {
            options[index].appendChild(new Element('option', {value: option[0]})).insert(option[0]);

            if (option[0] == oldValue) {
                options[index].value = oldValue;
            }
        });

        if (oldValue) {
            index++;
            valid = this.validateNewChildAttributeOptions(type, variation.get(attr)[oldValue], index);
        }

        return valid;
    },

    validateNewChild: function(showErrors)
    {
        var data = $('variation_manager_product_options_form').serialize(true),
            valid = true;

        $('new_child_product_product_options_error').hide();
        $$('#manage_variations_new_child_product_variation select').each(function(el) {
            if (el.value == '') {
                showErrors && $('new_child_product_product_options_error').show();
                valid = false;
                throw $break;
            }
        });

        if (data.create_new_asin != 1) {
            $('new_child_product_channel_options_error').hide();
            $$('#manage_variations_new_child_channel_variation select').each(function(el) {
                if (el.value == '') {
                    showErrors && $('new_child_product_channel_options_error').show();
                    valid = false;
                    throw $break;
                }
            });
        }

        return valid;
    },

    // ---------------------------------------

    addNewChildProduct: function()
    {
        var self = this,
            data;

        if (!self.validateNewChild(true)) {
            return;
        }

        data = $('variation_manager_product_options_form').serialize(true);

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_listing_variation_product_manage/createNewChild'), {
            method: 'post',
            parameters: data,
            onSuccess: function(transport) {
                var response = self.parseResponse(transport);
                if (response.msg) {
                    MagentoMessageObj.clearAll();
                    MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.msg);
                }

                if (response['vocabulary_attribute_options']) {
                    window.parent.ListingGridHandlerObj.variationProductManageHandler.openVocabularyOptionsPopUp(response['vocabulary_attribute_options']);
                    return;
                }

                self.actionHandler.gridHandler.unselectAllAndReload();
            }
        });
    },

    // ---------------------------------------

    createNewAsinBtn: function()
    {
        var optEl = $('manage_variations_select_options'),
            newAsinLinkEl = $('manage_variations_create_new_asin');

        optEl && optEl.show().down('input').enable();

        $('new_child_product_channel_options_error_row').setStyle({visibility: 'hidden'});
        newAsinLinkEl && newAsinLinkEl.hide();

        $$('.new-child-channel-option').each(function(el){
            el.disable();
        });

        $('manage_variations_create_new_asin_title').show();

        ListingGridHandlerObj.validateNewChildAttributeOptions('product');
    },

    selectOptionsBtn: function()
    {
        var optEl = $('manage_variations_select_options'),
            newAsinEl = $('manage_variations_create_new_asin');

        optEl && optEl.hide().down('input').disable();

        $('new_child_product_channel_options_error_row').setStyle({visibility: 'visible'});
        newAsinEl && newAsinEl.show();

        $$('.new-child-channel-option').each(function(el){
            el.enable();
            el.update();
        });

        $('manage_variations_create_new_asin_title').hide();

        ListingGridHandlerObj.validateNewChildAttributeOptions('channel');
    },

    // ---------------------------------------

    unselectAllAndReload: function($super)
    {
        $super();

        window.parent.ListingGridHandlerObj.variationProductManageHandler.reloadSettings(null, true);
        window.parent.ListingGridHandlerObj.variationProductManageHandler.reloadVocabulary(null, true);
    }

    // ---------------------------------------
});