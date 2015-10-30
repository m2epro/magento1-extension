AttributeCreator = Class.create();
AttributeCreator.prototype = {

    id: null,

    popupObj:   null,
    selectObj: null,

    delayTimer: null,
    selectIndexBeforeCreation: 0,

    // it is for close callback [in order to rest selected option for selectObj]
    attributeWasCreated: false,

    formId:         'general_create_new_attribute_form',
    addOptionValue: 'new-one-attribute',

    onSuccessCallback: null,
    onFailedCallback:  null,

    // ---------------------------------------

    initialize: function(id) {

        id = 'AttributeCreator_' + id + '_Obj';

        this.id = id;
        window[id] = this;
    },

    // ---------------------------------------

    setSelectObj: function(selectObj)
    {
        this.selectObj = selectObj;
    },

    setSelectIndexBeforeCreation:function(index)
    {
        this.selectIndexBeforeCreation = index;
    },

    // ---------------------------------------

    setOnSuccessCallback: function(funct)
    {
        this.onSuccessCallback = funct;
    },

    setOnFailedCallback: function(funct)
    {
        this.onFailedCallback = funct;
    },

    // ---------------------------------------

    showPopup: function(params)
    {
        var self = this;
        params = params || {};

        if (self.selectObj && self.selectObj.getAttribute('allowed_attribute_types')) {
            params['allowed_attribute_types'] = self.selectObj.getAttribute('allowed_attribute_types');
        }

        if (self.selectObj && self.selectObj.getAttribute('apply_to_all_attribute_sets') == '0') {
            params['apply_to_all_attribute_sets'] = '0';
        }

        params['handler_id'] = self.id;

        new Ajax.Request(M2ePro.url.get('adminhtml_general/getCreateAttributeHtmlPopup'), {
            method: 'post',
            asynchronous: true,
            parameters: params,
            onSuccess: function(transport) {

                self.popupObj = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Creation of New Magento Attribute'),
                    top: 160,
                    maxHeight: 520,
                    width: 550,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show,
                    onOk: function() {
                        return self.onOkPopupCallback();
                    },
                    onCancel: function() {
                        return self.onCancelPopupCallback();
                    },
                    onClose: function() {
                        return self.onClosePopupCallback();
                    }
                });

                self.attributeWasCreated = false;
                self.popupObj.options.destroyOnClose = true;
                self.autoHeightFix();

                $('modal_dialog_message').insert(transport.responseText);
                $('modal_dialog_message').evalScripts();
            }
        });
    },

    create: function(attributeParams)
    {
        var self = this;

        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_general/createAttribute'), {
            method: 'post',
            asynchronous: true,
            parameters: attributeParams,
            onSuccess: function(transport) {

                var result = transport.responseText.evalJSON();
                if (!result || !result['result']) {

                    typeof self.onFailedCallback == 'function'
                        ? self.onFailedCallback.call(self, attributeParams, result)
                        : self.defaultOnFailedCallback(attributeParams, result);

                    return;
                }

                typeof self.onSuccessCallback == 'function'
                    ? self.onSuccessCallback.call(self, attributeParams, result)
                    : self.defaultOnSuccessCallback(attributeParams, result);
            }
        });
    },

    // ---------------------------------------

    defaultOnSuccessCallback: function(attributeParams, result)
    {
        MagentoMessageObj.addSuccess(M2ePro.translator.translate('Attribute has been created.'));
        this.chooseNewlyCreatedAttribute(attributeParams, result);
    },

    defaultOnFailedCallback: function(attributeParams, result)
    {
        MagentoMessageObj.addError(result['error']);
        this.onCancelPopupCallback();
    },

    // ---------------------------------------

    onOkPopupCallback: function()
    {
        if (!new varienForm(this.formId).validate()) {
            return false;
        }

        this.create($(this.formId).serialize(true));
        this.attributeWasCreated = true;

        return true;
    },

    onCancelPopupCallback: function()
    {
        this.selectObj.selectedIndex = this.selectIndexBeforeCreation;
        return true;
    },

    onClosePopupCallback: function()
     {
         if (!this.attributeWasCreated) {
             this.onCancelPopupCallback();
         }
         return true;
     },

    chooseNewlyCreatedAttribute: function(attributeParams, result)
    {
        var optionsTitles = [];
        this.selectObj.select('option').each(function(el) {
            el.removeAttribute('selected');
            optionsTitles.push(trim(el.innerHTML));
        });
        optionsTitles.push(attributeParams['store_label']);
        optionsTitles.sort();

        var neededOptionPosition = optionsTitles.indexOf(attributeParams['store_label']),
            beforeOptionTitle = optionsTitles[neededOptionPosition - 1];

        if (this.haveOptgroup()) {

            var optGroupObj = this.selectObj.down('optgroup.M2ePro-custom-attribute-optgroup'),
                optionValue;

            optionValue = optGroupObj.hasAttribute('new_option_value') ? optGroupObj.getAttribute('new_option_value')
                                                                       : optGroupObj.down('option').value;

            var option = new Element('option', {
                attribute_code: attributeParams['code'],
                class: 'simple_mode_disallowed',
                value: optionValue
            });

        } else {

            var option = new Element('option', { value: attributeParams['code']});
        }

        this.selectObj.select('option').each(function(el){

            if (trim(el.innerHTML) == beforeOptionTitle) {
                $(el).insert({after: option});
                return true;
            }
        });

        option.update(attributeParams['store_label']);
        option.setAttribute('selected', 'selected');

        this.selectObj.simulate('change');
    },

    // ---------------------------------------

    injectAddOption: function()
    {
        var self = this;

        var option = new Element('option', {
            style: 'color: brown;',
            value: this.addOptionValue
        }).update(M2ePro.translator.translate('Create a New One...'));

        self.haveOptgroup() ? self.selectObj.down('optgroup.M2ePro-custom-attribute-optgroup').appendChild(option)
                            : self.selectObj.appendChild(option);

        $(self.selectObj).observe('change', function(event) {

            this.value == self.addOptionValue
                ? self.showPopup()
                : self.setSelectIndexBeforeCreation(self.selectObj.selectedIndex);
        });
    },

    validateAttributeCode: function(value, el)
    {
        if (!$(el).up('tr').visible()) {
            return true;
        }

        if (!value.match(/^[a-z][a-z_0-9]{1,254}$/)) {
            return false;
        }

        return true;
    },

    validateAttributeCodeToBeUnique: function(value, el)
    {
        var result = false;

        new Ajax.Request(M2ePro.url.get('adminhtml_general/isAttributeCodeUnique'), {
            method: 'post',
            asynchronous: false,
            parameters: {
                code: value
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    return;
                }

                result = transport.responseText.evalJSON();
            }
        });

        return result;
    },

    // ---------------------------------------

    onChangeCode: function(event)
    {
        if (!$('code').hasClassName('changed-by-user')) {
            $('code').addClassName('changed-by-user');
        }
    },

    onChangeLabel: function(event)
    {
        var self = this;

        if (event.target.value.length < 3) {
            return;
        }

        if ($('code').hasClassName('changed-by-user')) {
            return;
        }

        self.delayTimer && clearTimeout(self.delayTimer);
        self.delayTimer = setTimeout(function () {
            self.updateCode(event.target.value);
        }, 600);
    },

    updateCode: function(label)
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_general/generateAttributeCodeByLabel'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                store_label: label
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    return;
                }

                if ($('code').hasClassName('changed-by-user')) {
                    return;
                }

                $('code').value = transport.responseText.evalJSON();
            }
        });
    },

    // ---------------------------------------

    autoHeightFix: function()
    {
        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '650px';
        }, 50);
    },

    haveOptgroup: function()
    {
        return Boolean(this.selectObj.down('optgroup.M2ePro-custom-attribute-optgroup'));
    },

    alreadyHaveAddedOption: function()
    {
        return Boolean(this.selectObj.down('option[value="' + this.addOptionValue + '"]'));
    }

    // ---------------------------------------
};