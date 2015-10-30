CommonBuyTemplateNewProductAttributeHandler = Class.create();
CommonBuyTemplateNewProductAttributeHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    attrData: '',

    // ---------------------------------------

    initialize: function()
    {
        var self = this;

        Validation.add('M2ePro-attributes-validation-int', M2ePro.translator.translate('Invalid input data. Integer value required.'), function(value, element) {

            if (!element.up('tr').visible()) {
                return true;
            }

            return self['intTypeValidator'](value,element);
        });

        Validation.add('M2ePro-attributes-validation-float', M2ePro.translator.translate('Invalid input data. Decimal value required. Example 12.05'), function(value, element) {

            if (!element.up('tr').visible()) {
                return true;
            }

            return self['floatTypeValidator'](value,element);
        });

        Validation.add('M2ePro-attributes-validation-string', M2ePro.translator.translate('Invalid input data. String value required.'), function(value, element) {

            if (!element.up('tr').visible()) {
                return true;
            }

            return self['stringTypeValidator'](value,element);
        });

        Validation.add('multi_select_validator', M2ePro.translator.translate('This is a required field.'), function(value,element) {

            if (!element.up('tr').visible()) {
                return true;
            }

            return self['multiSelectTypeValidator'](value,element);
        });
    },

    // ---------------------------------------

    intTypeValidator: function(value,element) {

        if (value.match(/[^\d]+/g) || value <= 0) {
            return false;
        }

        return true;
    },

    stringTypeValidator: function(value,element) {
        return true;
    },

    floatTypeValidator: function(value, element) {

        if (value.match(/[^\d.]+/g)) {
            return false;
        }

        if (isNaN(parseFloat(value)) ||
            substr_count(value,'.') > 1 ||
            value.substr(-1) == '.') {
            return false;
        }

        return true;
    },

    requiredGroupTypeValidator: function(value, element, group)
    {
        var countOfSelected = 0;

        $$('.' + group).each(function(el) {
            if (el.value != M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_NONE') &&
                el.value != '') {
                countOfSelected ++;
            }
        });

        return countOfSelected > 0;
    },

    multiSelectTypeValidator: function(value,element)
    {
        return element.value != '';
    },

    // ---------------------------------------

    clearAttributes: function()
    {
        var trs = $('buy_attr_container').childElements();
        for (var i = 0; i < trs.length; i++) {
            trs[i].remove();
        }
    },

    showAttributes: function(nativeId)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler;

        self.clearAttributes();

        if (nativeId <= 0) {
            var tr = $('buy_attr_container').appendChild(new Element('tr'));
            var td = tr.appendChild(new Element ('td'));
            var label = td.appendChild(new Element ('label')).insert(M2ePro.translator.translate('Select Category first.'));
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_common_buy_template_newProduct/getAttributes'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                native_id: nativeId
            },
            onSuccess: function(transport) {

                var attributes = transport.responseText.evalJSON();
                var attributesList = attributes[0].attributes.evalJSON();

                if (M2ePro.formData.attributes.length > 0) {
                    self.renderAttributes(attributesList);
                    self.renderAttributesEditMode(M2ePro.formData.attributes);
                } else {
                    self.renderAttributes(attributesList);
                }
            }
        });
    },

    renderAttributesEditMode: function(attributes)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler;

        attributes.each(function(attribute) {

            var attributeName =  attribute.attribute_name.replace(/[\s()\/]/gi,'_');

            $('attributes[' + attributeName + '][mode]').value = attribute.mode;

            if (attribute.mode == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_RECOMMENDED_VALUE')) {

                $('select_' + attributeName).show();
                var recommended_value = attribute.recommended_value.evalJSON();
                var options = $$('#recommended_value_' + attributeName + ' option');

                for (var i = 0; i < options.length; i++) {
                    recommended_value.each(function(value) {
                        if (options[i].value == value) {
                            options[i].selected = true;
                        }
                    });
                }
            } else if (attribute.mode == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_CUSTOM_VALUE')) {
                $('input_' + attributeName).show();
                $('custom_value_' + attributeName).value = attribute.custom_value;
            } else if (attribute.mode == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_CUSTOM_ATTRIBUTE')) {
                $('custom_attribute_' + attributeName).value = attribute.custom_attribute;
                var tmpOptions = $('attributes[' + attributeName + '][mode]').options;

                for (var j = 0; j < tmpOptions.length; j++) {
                    if (tmpOptions[j].getAttribute('attribute_code') == attribute.custom_attribute) {
                        tmpOptions[j].selected = true;
                        break;
                    }
                }
            }
        })
    },

    renderAttributes: function(attributes)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler,
            dataDefinition = {};

        if (attributes.length > 0) {
            var isFirstOneOfFollowingAttribute = true;
            var iterations = 0;

            attributes.each(function(attribute) {
                    iterations ++;
                    var requiredGroupId = '';
                    var helpIcon = new Element('td', {'class': 'value'});

                    if (attribute.required_group_id != '0' && typeof attribute.required_group_id !== 'undefined') {
                        requiredGroupId = attribute.required_group_id;
                        if (isFirstOneOfFollowingAttribute) {
                            var tr = $('buy_attr_container').appendChild(new Element('tr'));
                            var td = tr.appendChild(new Element ('td', {'colspan': '2','style': 'padding: 15px 0'}));
                            td.appendChild(new Element('label')).insert('<b>'+ M2ePro.translator.translate('At least one of the following Attributes must be chosen:')+'</tr></b> <span class="required">*</span>');
                        }
                        isFirstOneOfFollowingAttribute = false;
                    } else {
                        isFirstOneOfFollowingAttribute = true;
                    }

                switch (parseInt(attribute.type)) {

                    case M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::TYPE_MULTISELECT'):

                        self.renderHelpTips(
                            attribute, M2ePro.translator.translate('Multiple values ​​must be separated by comma.'), helpIcon
                        );

                        self.renderAttributeMode(attribute, requiredGroupId, helpIcon);
                        self.renderRecommendedValues(attribute);
                        self.renderCustomValue(attribute);

                        break;

                    case M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::TYPE_SELECT'):

                        self.renderHelpTips(attribute, '', helpIcon);

                        self.renderAttributeMode(attribute, requiredGroupId, helpIcon);
                        self.renderRecommendedValues(attribute);
                        self.renderCustomValue(attribute);

                        break;

                    case M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::TYPE_INT'):

                        dataDefinition.definition = M2ePro.translator.translate('Any integer value');
                        dataDefinition.tips = '';
                        dataDefinition.example = '33';

                        self.renderHelpTips(attribute, dataDefinition, helpIcon);

                        self.renderAttributeMode(attribute,requiredGroupId, helpIcon);
                        self.renderCustomValue(attribute);

                        break;

                    case M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::TYPE_DECIMAL'):

                        dataDefinition.definition = M2ePro.translator.translate('Any decimal value');
                        dataDefinition.tips = '';
                        dataDefinition.example = '10.99';

                        self.renderHelpTips(attribute, dataDefinition, helpIcon);

                        self.renderAttributeMode(attribute, requiredGroupId, helpIcon);
                        self.renderCustomValue(attribute);

                        break;

                    case M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::TYPE_STRING'):

                        dataDefinition.definition = M2ePro.translator.translate('Any string value');
                        dataDefinition.tips = '';
                        dataDefinition.example = 'Red, Small, Long, Male, XXL';

                        self.renderHelpTips(attribute, dataDefinition, helpIcon);

                        self.renderAttributeMode(attribute, requiredGroupId, helpIcon);
                        self.renderCustomValue(attribute);

                        break;

                    default:
                        self.renderDefaultNoType(attribute);
                        break;
                }

                if (requiredGroupId != '') {
                    Validation.add(requiredGroupId, M2ePro.translator.translate('At least one of these Attributes is required.'), function(value, element) {
                        return self['requiredGroupTypeValidator'](value,element,requiredGroupId);
                    });
                } else {
                    iterations < attributes.length && self.renderLine();
                }
            });
        }
    },

    // ---------------------------------------

    renderAttributeMode: function(attribute, requiredGroupId, helpIcon)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler,
            title = attribute.title.replace(/[\s()\/]/gi,'_');

        var tr = $('buy_attr_container').appendChild(new Element('tr'));
        tr.appendChild(new Element('input', {
            type: 'hidden',
            id: 'custom_attribute_' + title,
            name: 'attributes[' + attribute.title + '][custom_attribute]',
            value: ''
        }));
        var td = tr.appendChild(new Element('td', {'class': 'label'}));

        td.appendChild(new Element('label')).insert(attribute.title + ': ' + (attribute.is_required == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::TYPE_IS_REQUIRED') ? '<span class="required">*</span>' : ''));

        td = tr.appendChild(new Element('td', {'class': 'value', 'style': 'width: 280px !important;'}));
        var select = td.appendChild(
            new Element('select', {
                'name': 'attributes[' + attribute.title + '][mode]',
                'id': 'attributes[' + title + '][mode]',
                'class': 'select attributes required-entry ' + requiredGroupId + ' M2ePro-custom-attribute-can-be-created',
                'allowed_attribute_types': 'text,price,select',
                'apply_to_all_attribute_sets': '0'
            }));

        if (typeof helpIcon !== 'undefined') {
            tr.appendChild(helpIcon);
        }

        attribute.is_required == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::TYPE_IS_REQUIRED')
            ? select.appendChild(new Element('option', {'style': 'display: none; '}))
            : select.appendChild(new Element('option', {'value': M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_NONE')})).insert(M2ePro.translator.translate('None'));

        if (attribute.type == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::TYPE_MULTISELECT') ||
            attribute.type == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::TYPE_SELECT')) {

            select.appendChild(new Element('option', {'value': M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_RECOMMENDED_VALUE')})).insert(M2ePro.translator.translate('Recommended Values'));
        }

        select.appendChild(new Element('option', {'value': M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_CUSTOM_VALUE')})).insert(M2ePro.translator.translate('Custom Value'));

        var optgroup = new Element('optgroup', {
            label: 'Magento Attributes',
            class: 'M2ePro-custom-attribute-optgroup'
        });
        optgroup.insert(BuyTemplateNewProductHandlerObj.attributeHandler.attrData);
        select.appendChild(optgroup);

        var handlerObj = new AttributeCreator(title);
        handlerObj.setSelectObj(select);
        handlerObj.injectAddOption();

        self.setObserver(attribute, select);
    },

    renderRecommendedValues: function(attribute)
    {
        var title = attribute.title.replace(/[\s()\/]/gi,'_');

        var tr = $('buy_attr_container').appendChild(new Element('tr', {'id': 'select_' + title,'style': 'display: none;'}));
        var td = tr.appendChild(new Element('td', {'class': 'label'}));
        td.appendChild(new Element('label')).insert(M2ePro.translator.translate('Recommended Values') + '<span class="required">*</span> : ');

        td = tr.appendChild(new Element('td', {'class': 'value'}));

        var select = td.appendChild(new Element('select', {
            'name': 'attributes[' + attribute.title + '][recommended_value][]',
            'id': 'recommended_value_' + title,
            'class': 'select M2ePro-required-when-visible',
            'style': 'width: 280px'}));

        if (attribute.type == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::TYPE_MULTISELECT')) {
            select.setStyle({height: '150px'});
            select.setAttribute('multiple','multiple');
            select.setAttribute('class','select multi_select_validator');
        }

        var values = attribute.values.evalJSON();
        values.each(function(value) {
            select.appendChild(new Element('option', {'value': value})).insert(value);
        })
    },

    renderCustomValue: function(attribute)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler,
            title = attribute.title.replace(/[\s()\/]/gi,'_');

        var tr = $('buy_attr_container').appendChild(new Element('tr', {'id': 'input_' + title,'style': 'display: none;'}));
        var td = tr.appendChild(new Element('td', {'class': 'label'}));
        var label = td.appendChild(new Element('label')).insert(M2ePro.translator.translate('Custom Value') + '<span class="required">*</span> : ');

        td = tr.appendChild(new Element('td', {'class': 'value'}));

        var input = td.appendChild(new Element('input', {
            'id': 'custom_value_' + title,
            'name': 'attributes[' + attribute.title + '][custom_value]',
            'type': 'text',
            'class': 'input-text M2ePro-required-when-visible ' + self.getValidator(attribute)}));
    },

    getValidator: function(attribute)
    {
        var className = '';

        if (attribute.type == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::TYPE_INT')) {
            className = 'M2ePro-attributes-validation-int';
        } else if (attribute.type == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::TYPE_DECIMAL')) {
            className = 'M2ePro-attributes-validation-float';
        }

        return className;
    },

    setObserver: function(attribute, element)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler

        element.observe('change', function() {

            var title = attribute.title.replace(/[\s()\/]/gi,'_'),
                customAttribute = $('custom_attribute_' + title);

            $('input_'+ title).hide();

            if (attribute.type == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::TYPE_SELECT') ||
                attribute.type == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::TYPE_MULTISELECT')) {

                $('select_' + title).hide();
            }

            if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_RECOMMENDED_VALUE')) {
                $('select_'+ title).show();
                customAttribute.value = '';
            } else if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_CUSTOM_VALUE')) {
                $('input_'+ title).show();
                customAttribute.value = '';
            } else if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_CUSTOM_ATTRIBUTE')) {
                self.updateHiddenValue(this, customAttribute);
            }
        });
    },

    // ---------------------------------------

    renderDefaultNoType: function(attribute)
    {
        $('buy_attr_container')
            .appendChild(new Element('tr'))
            .appendChild(new Element('td'))
            .update(M2ePro.translator.translate('The Category does not have Attributes.'));
    },

    renderLine: function()
    {
        $('buy_attr_container')
            .appendChild(new Element('tr'))
            .appendChild(new Element('td', {'colspan': '2','style': 'padding: 15px 0'}))
            .appendChild(new Element('hr', {'style': 'border: 1px solid silver; border-bottom: none;'}));
    },

    // ---------------------------------------

    renderHelpTips: function(attribute, dataDefinition, container)
    {
        var self = BuyTemplateNewProductHandlerObj.attributesHandler;

        var winContent = '';

        if (typeof dataDefinition === 'object' && dataDefinition.definition) {
            winContent = '<div style="padding: 3px 0"></div><h4>' + M2ePro.translator.translate('Definition:') + ' </h4>';
            winContent += '<div>' + dataDefinition.definition + '</div>';

            if (dataDefinition.tips) {
                winContent += '<div style="padding: 5px 0"></div><h4>' + M2ePro.translator.translate('Tips:') + ' </h4>';
                winContent += '<div>' + dataDefinition.tips + '</div>'
            }
            if (dataDefinition.example) {
                winContent += '<div style="padding: 5px 0"></div><h4>' + M2ePro.translator.translate('Examples:') + ' </h4>';
                winContent += '<div>' + dataDefinition.example + '</div>'
            }
        } else {
            winContent = '<h4>' + M2ePro.translator.translate('Allowed Values') + ': </h4>';
            winContent += '<ul style="text-align: center; margin-top: 10px; max-height: 200px; overflow-y: auto;">';

            var valuesIn = attribute.values.evalJSON();
            valuesIn.each(function(value) {
                winContent += '<li><p>' + value + '</p></li>';
            });
            winContent += '</ul>';

            if (dataDefinition.length > 0) {
                winContent += '<div style="padding: 5px 0"></div><h4>' + M2ePro.translator.translate('Notes:') + '</h4>';
                winContent += '<div style="text-align: center"><p>' + dataDefinition + '</p></div>';
            }
        }

        var imgPath = M2ePro.url.get('m2epro_skin_url') + '/images/';
        container.innerHTML = '<img src="'+ imgPath +'tool-tip-icon.png" class="tool-tip-image">' +
                              '<span class="tool-tip-message" style="display: none;">' +
                              '<img src="'+ imgPath +'help.png">' +
                              '<span>'+ winContent +'</span>' +
                              '</span>';
    }

    // ---------------------------------------
});