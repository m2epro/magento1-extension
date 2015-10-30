CommonAmazonListingChannelSettingsHandler = Class.create();
CommonAmazonListingChannelSettingsHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    initialize: function()
    {
        Validation.add('M2ePro-validate-condition-note-length', M2ePro.text.condition_note_length_error, function(value) {

            if ($('condition_note_mode').value != AmazonListingChannelSettingsHandlerObj.CONDITION_NOTE_MODE_CUSTOM_VALUE) {
                return true;
            }

            return value.length <= 2000;
        });

        Validation.add('M2ePro-validate-sku-modification-custom-value', M2ePro.text.sku_modification_custom_value_error, function(value) {

            var self = AmazonListingChannelSettingsHandlerObj;

            if ($('sku_modification_mode').value == self.SKU_MODIFICATION_MODE_NONE) {
                return true;
            }

            if ($('sku_modification_mode').value == self.SKU_MODIFICATION_MODE_TEMPLATE) {
                return value.match(/%value%/g);
            }

            return true;
        });

        Validation.add('M2ePro-validate-sku-modification-custom-value-max-length', M2ePro.text.sku_modification_custom_value_max_length_error, function(value) {

            var self = AmazonListingChannelSettingsHandlerObj;

            if ($('sku_modification_mode').value == self.SKU_MODIFICATION_MODE_NONE) {
                return true;
            }

            if ($('sku_modification_mode').value == self.SKU_MODIFICATION_MODE_TEMPLATE) {
                value = value.replace('%value%', '');
            }

            return value.length < M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_General::SKU_MAX_LENGTH');
        });
    },

    // ---------------------------------------

    getAvailableConstantsForImages: function()
    {
        return [
            M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::CONDITION_USED_LIKE_NEW'),
            M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::CONDITION_USED_VERY_GOOD'),
            M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::CONDITION_USED_GOOD'),
            M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::CONDITION_USED_ACCEPTABLE'),
            M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::CONDITION_COLLECTIBLE_LIKE_NEW'),
            M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::CONDITION_COLLECTIBLE_VERY_GOOD'),
            M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::CONDITION_COLLECTIBLE_GOOD'),
            M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::CONDITION_COLLECTIBLE_ACCEPTABLE'),
        ];
    },

    // ---------------------------------------

    sku_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        $('sku_custom_attribute').value = '';
        if (this.value == self.SKU_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('sku_custom_attribute'));
        }
    },

    // ---------------------------------------

    sku_modification_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

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
        var self = AmazonListingChannelSettingsHandlerObj;

        $('general_id_custom_attribute').value = '';
        if (this.value == self.GENERAL_ID_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('general_id_custom_attribute'));
        }
    },

    // ---------------------------------------

    worldwide_id_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        $('worldwide_id_custom_attribute').value = '';
        if (this.value == self.WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('worldwide_id_custom_attribute'));
        }
    },

    // ---------------------------------------

    condition_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj,
            attributeCode = this.options[this.selectedIndex].getAttribute('attribute_code'),
            condition_note_mode = $('condition_note_mode'),
            conditionValue = $('condition_value'),
            conditionCustomAttribute = $('condition_custom_attribute');

        $('magento_block_amazon_listing_add_images').hide();

        conditionValue.value = '';
        conditionCustomAttribute.value = '';

        if (this.value == self.CONDITION_MODE_DEFAULT) {
            self.updateHiddenValue(this, conditionValue);
            $('condition_note_mode_tr').show();
            condition_note_mode.simulate('change');

            if (self.getAvailableConstantsForImages().indexOf(attributeCode) == -1) {
                $('image_main_attribute').value = '';
                $('image_main_mode').selectedIndex = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::IMAGE_MAIN_MODE_NONE');
                $('image_main_mode').simulate('change');
            } else {
                $('magento_block_amazon_listing_add_images').show();
            }
        } else {
            self.updateHiddenValue(this, conditionCustomAttribute);
            $('condition_note_mode_tr').show();
            condition_note_mode.simulate('change');

            $('magento_block_amazon_listing_add_images').show();
        }
    },

    // ---------------------------------------

    image_main_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        $('gallery_images_mode_tr').show();

        $('image_main_attribute').value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::IMAGE_MAIN_MODE_NONE')) {
            $('gallery_images_mode_tr').hide();
            $('gallery_images_limit').value = '';
            $('gallery_images_attribute').value = '';
            $('gallery_images_mode').selectedIndex = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::GALLERY_IMAGES_MODE_NONE');
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::IMAGE_MAIN_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('image_main_attribute'));
        }
    },

    gallery_images_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        $('gallery_images_limit').value = '';
        $('gallery_images_attribute').value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::GALLERY_IMAGES_MODE_PRODUCT')) {
            self.updateHiddenValue(this, $('gallery_images_limit'));
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::GALLERY_IMAGES_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('gallery_images_attribute'));
        }
    },

    // ---------------------------------------

    gift_wrap_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        $('gift_wrap_attribute').value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::GIFT_WRAP_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('gift_wrap_attribute'));
        }
    },

    gift_message_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        $('gift_message_attribute').value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::GIFT_MESSAGE_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('gift_message_attribute'));
        }
    },

    // ---------------------------------------

    condition_note_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        if (this.value == self.CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $('condition_note_value_tr').show();
        } else {
            $('condition_note_value_tr').hide();
        }
    },

    handling_time_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        $('handling_time_custom_attribute').value = '';
        $('handling_time_value').value = '';
        if (this.value == self.HANDLING_TIME_MODE_RECOMMENDED) {
            self.updateHiddenValue(this, $('handling_time_value'));
        }

        if (this.value == self.HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('handling_time_custom_attribute'));
        }
    },

    restock_date_mode_change: function()
    {
        var self = AmazonListingChannelSettingsHandlerObj;

        $('restock_date_value_tr').hide();

        $('restock_date_custom_attribute').value = '';
        if (this.value == self.RESTOCK_DATE_MODE_CUSTOM_VALUE) {
            $('restock_date_value_tr').show();
        }

        if (this.value == self.RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('restock_date_custom_attribute'));
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