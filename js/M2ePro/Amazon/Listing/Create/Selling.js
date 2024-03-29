window.AmazonListingCreateSelling = Class.create(Common, {

    // ---------------------------------------

    initialize: function () {
        Validation.add('M2ePro-validate-condition-note-length', M2ePro.text.condition_note_length_error, function(value) {

            if ($('condition_note_mode').value != M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::CONDITION_NOTE_MODE_CUSTOM_VALUE')) {
                return true;
            }

            return value.length <= 2000;
        });

        Validation.add('M2ePro-validate-sku-modification-custom-value', M2ePro.text.sku_modification_custom_value_error, function(value) {

            if ($('sku_modification_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::SKU_MODIFICATION_MODE_NONE')) {
                return true;
            }

            if ($('sku_modification_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::SKU_MODIFICATION_MODE_TEMPLATE')) {
                return value.match(/%value%/g);
            }

            return true;
        });

        Validation.add('M2ePro-validate-sku-modification-custom-value-max-length', M2ePro.text.sku_modification_custom_value_max_length_error, function(value) {

            if ($('sku_modification_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::SKU_MODIFICATION_MODE_NONE')) {
                return true;
            }

            if ($('sku_modification_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::SKU_MODIFICATION_MODE_TEMPLATE')) {
                value = value.replace('%value%', '');
            }

            return value.length < M2ePro.php.constant('Ess_M2ePro_Helper_Component::SKU_MAX_LENGTH');
        });
    },

    // ---------------------------------------

    getAvailableConstantsForImages: function () {
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

    sku_mode_change: function () {
        var self = AmazonListingCreateSellingObj;

        $('sku_custom_attribute').value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::SKU_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('sku_custom_attribute'));
        }
    },

    // ---------------------------------------

    sku_modification_mode_change: function () {
        if ($('sku_modification_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::SKU_MODIFICATION_MODE_NONE')) {
            $('sku_modification_custom_value').up('span').hide();
        } else {
            $('sku_modification_custom_value').up('span').show();
        }
    },

    // ---------------------------------------

    condition_mode_change: function () {
        var self = AmazonListingCreateSellingObj,
            attributeCode = this.options[this.selectedIndex].getAttribute('attribute_code'),
            conditionValue = $('condition_value'),
            conditionCustomAttribute = $('condition_custom_attribute');

        $('magento_block_amazon_listing_add_images').hide();
        $('magento_block_amazon_listing_add_images').previous('.entry-edit-head').hide();
        $('condition_note_mode').up('span').show();
        $('condition_note_value').up('span').show();

        conditionValue.value = '';
        conditionCustomAttribute.value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::CONDITION_MODE_DEFAULT')) {
            self.updateHiddenValue(this, conditionValue);

            if (attributeCode == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::CONDITION_NEW')) {
                $('condition_note_mode').up('span').hide();
                $('condition_note_value').up('span').hide();
            } else {
                self.condition_note_mode_change();
            }

            if (self.getAvailableConstantsForImages().indexOf(attributeCode) == -1) {
                $('image_main_attribute').value = '';
                $('image_main_mode').selectedIndex = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::IMAGE_MAIN_MODE_NONE');
                $('image_main_mode').simulate('change');
            } else {
                $('magento_block_amazon_listing_add_images').show();
                $('magento_block_amazon_listing_add_images').previous('.entry-edit-head').show();
            }
        } else {
            self.updateHiddenValue(this, conditionCustomAttribute);
            $('condition_note_mode').up('span').show();
            self.condition_note_mode_change();

            $('magento_block_amazon_listing_add_images').show();
            $('magento_block_amazon_listing_add_images').previous('.entry-edit-head').show();
        }
    },

    // ---------------------------------------

    image_main_mode_change: function () {
        var self = AmazonListingCreateSellingObj;

        $('gallery_images_mode').up('span').show();

        $('image_main_attribute').value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::IMAGE_MAIN_MODE_NONE')) {
            $('gallery_images_mode').up('span').hide();
            $('gallery_images_limit').value = '';
            $('gallery_images_attribute').value = '';
            $('gallery_images_mode').selectedIndex = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::GALLERY_IMAGES_MODE_NONE');
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::IMAGE_MAIN_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('image_main_attribute'));
        }
    },

    gallery_images_mode_change: function () {
        var self = AmazonListingCreateSellingObj;

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

    gift_wrap_mode_change: function () {
        var self = AmazonListingCreateSellingObj;

        $('gift_wrap_attribute').value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::GIFT_WRAP_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('gift_wrap_attribute'));
        }
    },

    gift_message_mode_change: function () {
        var self = AmazonListingCreateSellingObj;

        $('gift_message_attribute').value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::GIFT_MESSAGE_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('gift_message_attribute'));
        }
    },

    // ---------------------------------------

    condition_note_mode_change: function () {
        var self = AmazonListingCreateSellingObj;

        if ($('condition_note_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::CONDITION_NOTE_MODE_CUSTOM_VALUE')) {
            $('condition_note_value').up('span').show();
        } else {
            $('condition_note_value').up('span').hide();
        }
    },

    handling_time_mode_change: function () {
        var self = AmazonListingCreateSellingObj;

        $('handling_time_custom_attribute').value = '';
        $('handling_time_value').value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::HANDLING_TIME_MODE_RECOMMENDED')) {
            self.updateHiddenValue(this, $('handling_time_value'));
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('handling_time_custom_attribute'));
        }
    },

    restock_date_mode_change: function () {
        var self = AmazonListingCreateSellingObj;

        $('restock_date_custom_attribute').value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::RESTOCK_DATE_MODE_CUSTOM_VALUE')) {
            $('restock_date_value').up('span').show();
            Calendar.setup({
                inputField: "restock_date_value",
                ifFormat: "%Y-%m-%d %H:%M:%S",
                showsTime: true,
                button: "restock_date",
                align: "Bl",
                singleClick : true
            });
        } else {
            $('restock_date_value').value = '';
            $('restock_date_value').up('span').hide();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('restock_date_custom_attribute'));
        }
    },

    // ---------------------------------------

    appendToText: function (ddId, targetId) {
        if ($(ddId).value == '') {
            return;
        }

        var attributePlaceholder = '#' + $(ddId).value + '#',
            element = $(targetId);

        if (document.selection) {
            /* IE */
            element.focus();
            document.selection.createRange().text = attributePlaceholder;
            element.focus();
        } else if (element.selectionStart || element.selectionStart == '0') {
            /* Webkit */
            var startPos = element.selectionStart,
                endPos = element.selectionEnd,
                scrollTop = element.scrollTop,
                tempValue;

            tempValue = element.value.substring(0, startPos);
            tempValue += attributePlaceholder;
            tempValue += element.value.substring(endPos, element.value.length);
            element.value = tempValue;

            element.focus();
            element.selectionStart = startPos + attributePlaceholder.length;
            element.selectionEnd = startPos + attributePlaceholder.length;
            element.scrollTop = scrollTop;
        } else {
            element.value += attributePlaceholder;
            element.focus();
        }
    }

    // ---------------------------------------
});