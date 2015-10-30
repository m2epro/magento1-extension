CommonBuyListingChannelSettingsHandler = Class.create();
CommonBuyListingChannelSettingsHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    initialize: function()
    {
        Validation.add('M2ePro-validate-condition-note-length', M2ePro.text.condition_note_length_error, function(value) {

            if ($('condition_note_mode').value != BuyListingChannelSettingsHandlerObj.CONDITION_NOTE_MODE_CUSTOM_VALUE) {
                return true;
            }

            return value.length <= 250;
        });

        Validation.add('M2ePro-validate-shipping-value-positive', M2ePro.text.shipping_rate_error, function(value) {
            return value >= 0;
        });

        Validation.add('M2ePro-validate-sku-modification-custom-value', M2ePro.text.sku_modification_custom_value_error, function(value) {

            var self = BuyListingChannelSettingsHandlerObj;

            if ($('sku_modification_mode').value == self.SKU_MODIFICATION_MODE_NONE) {
                return true;
            }

            if ($('sku_modification_mode').value == self.SKU_MODIFICATION_MODE_TEMPLATE) {
                return value.match(/%value%/g);
            }

            return true;
        });

        Validation.add('M2ePro-validate-sku-modification-custom-value-max-length', M2ePro.text.sku_modification_custom_value_max_length_error, function(value) {

            var self = BuyListingChannelSettingsHandlerObj;

            if ($('sku_modification_mode').value == self.SKU_MODIFICATION_MODE_NONE) {
                return true;
            }

            if ($('sku_modification_mode').value == self.SKU_MODIFICATION_MODE_TEMPLATE) {
                value = value.replace('%value%', '');
            }

            return value.length < M2ePro.php.constant('Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_List_Validator_Sku_General::SKU_MAX_LENGTH');
        });
    },

    // ---------------------------------------

    save_click: function(url)
    {
        if (typeof url == 'undefined' || url == '') {
            url = M2ePro.url.formSubmit + 'back/'+base64_encode('list')+'/';
        }

        $('shipping_standard_value').disabled = false;
        $('shipping_expedited_value').disabled = false;
        $('shipping_two_day_value').disabled = false;
        $('shipping_one_day_value').disabled = false;

        this.submitForm(url);
    },

    // ---------------------------------------

    account_id_change: function()
    {
        BuyListingChannelSettingsHandlerObj.hideEmptyOption($('account_id'));
    },

    // ---------------------------------------

    sku_mode_change: function()
    {
        var self = BuyListingChannelSettingsHandlerObj;

        $('sku_custom_attribute').value = '';
        if (this.value == self.SKU_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('sku_custom_attribute'));
        }
    },

    // ---------------------------------------

    sku_modification_mode_change: function()
    {
        var self = BuyListingChannelSettingsHandlerObj;

        if ($('sku_modification_mode').value == self.SKU_MODIFICATION_MODE_TEMPLATE) {
            $('sku_modification_custom_value').value = '%value%';
        } else {
            $('sku_modification_custom_value').value = '';
        }

        if ($('sku_modification_mode').value == self.SKU_MODIFICATION_MODE_NONE) {
            $('sku_modification_custom_value_tr').hide();
        } else {
            $('sku_modification_custom_value_tr').show();
        }
    },

    // ---------------------------------------

    general_id_mode_change: function()
    {
        var self = BuyListingChannelSettingsHandlerObj;

        $('general_id_custom_attribute').value = '';
        if (this.value == self.GENERAL_ID_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('general_id_custom_attribute'));
        }
    },

    // ---------------------------------------

    shipping_standard_mode_change: function()
    {
        var self = BuyListingChannelSettingsHandlerObj;

        if ($('advice-M2ePro-required-when-visible-shipping_standard_custom_attribute')) {
            $('advice-M2ePro-required-when-visible-shipping_standard_custom_attribute').hide();
        }
        if ($('advice-M2ePro-validate-shipping-value-positive-shipping_standard_value')) {
            $('advice-M2ePro-validate-shipping-value-positive-shipping_standard_value').hide();
        }

        $('shipping_standard_value').hide();
        $('shipping_standard_custom_attribute').hide();

        if (this.value == self.SHIPPING_MODE_DEFAULT) {
            $('shipping_standard_value').disabled = true;
            $('shipping_standard_value').value = '';
            $('shipping_standard_value').show();
        }

        if (this.value == self.SHIPPING_MODE_FREE) {
            $('shipping_standard_value').disabled = true;
            $('shipping_standard_value').value = '0';
            $('shipping_standard_value').show();
        }

        if (this.value == self.SHIPPING_MODE_VALUE) {
            $('shipping_standard_value').disabled = false;
            $('shipping_standard_value').show();
            $('shipping_standard_value').focus();
        }

        if (this.value == self.SHIPPING_MODE_CUSTOM_ATTRIBUTE) {
            $('shipping_standard_custom_attribute').show();
        }
    },

    shipping_expedited_mode_change: function()
    {
        var self = BuyListingChannelSettingsHandlerObj;

        if ($('advice-M2ePro-required-when-visible-shipping_expedited_custom_attribute')) {
            $('advice-M2ePro-required-when-visible-shipping_expedited_custom_attribute').hide();
        }
        if ($('advice-M2ePro-validate-shipping-value-positive-shipping_expedited_value')) {
            $('advice-M2ePro-validate-shipping-value-positive-shipping_expedited_value').hide();
        }

        $('shipping_expedited_value').hide();
        $('shipping_expedited_custom_attribute').hide();

        if (this.value == self.SHIPPING_MODE_DEFAULT
            || this.value == self.SHIPPING_MODE_DISABLED
        ) {
            $('shipping_expedited_value').disabled = true;
            $('shipping_expedited_value').value = '';
            $('shipping_expedited_value').show();
        }

        if (this.value == self.SHIPPING_MODE_FREE) {
            $('shipping_expedited_value').disabled = true;
            $('shipping_expedited_value').value = '0';
            $('shipping_expedited_value').show();
        }

        if (this.value == self.SHIPPING_MODE_VALUE) {
            $('shipping_expedited_value').disabled = false;
            $('shipping_expedited_value').show();
            $('shipping_expedited_value').focus();
        }

        if (this.value == self.SHIPPING_MODE_CUSTOM_ATTRIBUTE) {
            $('shipping_expedited_custom_attribute').show();
        }
    },

    shipping_two_day_mode_change: function()
    {
        var self = BuyListingChannelSettingsHandlerObj;

        if ($('advice-M2ePro-required-when-visible-shipping_two_day_custom_attribute')) {
            $('advice-M2ePro-required-when-visible-shipping_two_day_custom_attribute').hide();
        }
        if ($('advice-M2ePro-validate-shipping-value-positive-shipping_two_day_value')) {
            $('advice-M2ePro-validate-shipping-value-positive-shipping_two_day_value').hide();
        }

        $('shipping_two_day_value').hide();
        $('shipping_two_day_custom_attribute').hide();

        if (this.value == self.SHIPPING_MODE_DEFAULT
            || this.value == self.SHIPPING_MODE_DISABLED
            ) {
            $('shipping_two_day_value').disabled = true;
            $('shipping_two_day_value').value = '';
            $('shipping_two_day_value').show();
        }

        if (this.value == self.SHIPPING_MODE_FREE) {
            $('shipping_two_day_value').disabled = true;
            $('shipping_two_day_value').value = '0';
            $('shipping_two_day_value').show();
        }

        if (this.value == self.SHIPPING_MODE_VALUE) {
            $('shipping_two_day_value').disabled = false;
            $('shipping_two_day_value').show();
            $('shipping_two_day_value').focus();
        }

        if (this.value == self.SHIPPING_MODE_CUSTOM_ATTRIBUTE) {
            $('shipping_two_day_custom_attribute').show();
        }
    },

    shipping_one_day_mode_change: function()
    {
        var self = BuyListingChannelSettingsHandlerObj;

        if ($('advice-M2ePro-required-when-visible-shipping_one_day_custom_attribute')) {
            $('advice-M2ePro-required-when-visible-shipping_one_day_custom_attribute').hide();
        }
        if ($('advice-M2ePro-validate-shipping-value-positive-shipping_one_day_value')) {
            $('advice-M2ePro-validate-shipping-value-positive-shipping_one_day_value').hide();
        }

        $('shipping_one_day_value').hide();
        $('shipping_one_day_custom_attribute').hide();

        if (this.value == self.SHIPPING_MODE_DEFAULT
            || this.value == self.SHIPPING_MODE_DISABLED
            ) {
            $('shipping_one_day_value').disabled = true;
            $('shipping_one_day_value').value = '';
            $('shipping_one_day_value').show();
        }

        if (this.value == self.SHIPPING_MODE_FREE) {
            $('shipping_one_day_value').disabled = true;
            $('shipping_one_day_value').value = '0';
            $('shipping_one_day_value').show();
        }

        if (this.value == self.SHIPPING_MODE_VALUE) {
            $('shipping_one_day_value').disabled = false;
            $('shipping_one_day_value').show();
            $('shipping_one_day_value').focus();
        }

        if (this.value == self.SHIPPING_MODE_CUSTOM_ATTRIBUTE) {
            $('shipping_one_day_custom_attribute').show();
        }
    },

    // ---------------------------------------

    condition_mode_change: function()
    {
        var self = BuyListingChannelSettingsHandlerObj;

        var condition_note_mode = $('condition_note_mode');

        $('condition_custom_attribute').value = '';
        $('condition_value').value = '';
        if (this.value == self.CONDITION_MODE_DEFAULT) {
            self.updateHiddenValue(this, $('condition_value'));
        } else {
            self.updateHiddenValue(this, $('condition_custom_attribute'));
        }
    },

    // ---------------------------------------

    condition_note_mode_change: function()
    {
        var self = BuyListingChannelSettingsHandlerObj;

        if (this.value == self.CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $('condition_note_value_tr').show();
        } else {
            $('condition_note_value_tr').hide();
        }
    },

    // ---------------------------------------

    appendToText: function(ddId, targetId)
    {
        if ($(ddId).value == '') {
            return;
        }

        var attributePlaceholder = '#' + $(ddId).value + '#',
            element              = $(targetId);

        if (document.selection) {
            /* IE */
            element.focus();
            document.selection.createRange().text = attributePlaceholder;
            element.focus();
        } else if (element.selectionStart || element.selectionStart == '0') {
            /* Webkit */
            var startPos  = element.selectionStart,
                endPos    = element.selectionEnd,
                scrollTop = element.scrollTop,
                tempValue;

            tempValue = element.value.substring(0, startPos);
            tempValue += attributePlaceholder;
            tempValue += element.value.substring(endPos, element.value.length);
            element.value = tempValue;

            element.focus();
            element.selectionStart = startPos + attributePlaceholder.length;
            element.selectionEnd   = startPos + attributePlaceholder.length;
            element.scrollTop      = scrollTop;
        } else {
            element.value += attributePlaceholder;
            element.focus();
        }
    }

    // ---------------------------------------
});
