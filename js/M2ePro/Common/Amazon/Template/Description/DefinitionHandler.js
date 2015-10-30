CommonAmazonTemplateDescriptionDefinitionHandler = Class.create();
CommonAmazonTemplateDescriptionDefinitionHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    initialize: function() { },

    // ---------------------------------------

    title_mode_change: function()
    {
        var customTitle = $('custom_title_tr');
        this.value == 1 ? customTitle.show() : customTitle.hide();
    },

    brand_mode_change: function()
    {
        var customAttribute = $('brand_custom_attribute'),
            customValueTr   = $('brand_custom_value_tr');

        customValueTr.hide();

        customAttribute.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::BRAND_MODE_CUSTOM_VALUE')) {
            customValueTr.show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::BRAND_MODE_CUSTOM_ATTRIBUTE')) {
            AmazonTemplateDescriptionDefinitionHandlerObj.updateHiddenValue(this, customAttribute);
        }
    },

    manufacturer_mode_change: function()
    {
        var customAttribute = $('manufacturer_custom_attribute'),
            customValueTr   = $('manufacturer_custom_value_tr');

        customValueTr.hide();

        customAttribute.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::MANUFACTURER_MODE_CUSTOM_VALUE')) {
            customValueTr.show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::MANUFACTURER_MODE_CUSTOM_ATTRIBUTE')) {
            AmazonTemplateDescriptionDefinitionHandlerObj.updateHiddenValue(this, customAttribute);
        }
    },

    manufacturer_part_number_mode_change: function()
    {
        var customAttribute = $('manufacturer_part_number_custom_attribute'),
            customValueTr   = $('manufacturer_part_number_custom_value_tr');

        customValueTr.hide();

        customAttribute.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::MANUFACTURER_PART_NUMBER_MODE_CUSTOM_VALUE')) {
            customValueTr.show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::MANUFACTURER_PART_NUMBER_MODE_CUSTOM_ATTRIBUTE')) {
            AmazonTemplateDescriptionDefinitionHandlerObj.updateHiddenValue(this, customAttribute);
        }
    },

    onChangeItemPackageQuantityMode: function()
    {
        var targetCustomValue     = $('item_package_quantity_custom_value_tr'),
            targetCustomAttribute = $('item_package_quantity_custom_attribute');

        targetCustomValue.hide();

        targetCustomAttribute.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::ITEM_PACKAGE_QUANTITY_MODE_CUSTOM_VALUE')) {
            targetCustomValue.show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::ITEM_PACKAGE_QUANTITY_MODE_CUSTOM_ATTRIBUTE')) {
            AmazonTemplateDescriptionHandlerObj.updateHiddenValue(this, targetCustomAttribute);
        }
    },

    onChangeNumberOfItemsMode: function()
    {
        var targetCustomValue     = $('number_of_items_custom_value_tr'),
           targetCustomAttribute = $('number_of_items_custom_attribute');

        targetCustomValue.hide();

        targetCustomAttribute.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::NUMBER_OF_ITEMS_MODE_CUSTOM_VALUE')) {
            targetCustomValue.show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::NUMBER_OF_ITEMS_MODE_CUSTOM_ATTRIBUTE')) {
            AmazonTemplateDescriptionHandlerObj.updateHiddenValue(this, targetCustomAttribute);
        }
    },

    // ---------------------------------------

    volumeModeChange: function(customValueTr, customAttributeTr, weightUnitTr, weightUnitMode, value)
    {
        customValueTr.hide();
        customAttributeTr.hide();
        weightUnitTr.hide();

        if (value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::DIMENSION_VOLUME_MODE_NONE')) {
            weightUnitMode.value = '';
        }

        if (value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::DIMENSION_VOLUME_MODE_CUSTOM_VALUE')) {
            customValueTr.show();
            weightUnitTr.show();
        }

        if (value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::DIMENSION_VOLUME_MODE_CUSTOM_ATTRIBUTE')) {
            customAttributeTr.show();
            weightUnitTr.show();
        }

        weightUnitMode.simulate('change');
    },

    weightModeChange: function(customValueTr, customAttribute, weightUnitTr, weightUnitMode, value)
    {
        customValueTr.hide();
        weightUnitTr.hide();

        customAttribute.value = '';
        if (value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::DIMENSION_VOLUME_MODE_NONE')) {
            weightUnitMode.value = '';
        }

        if (value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::DIMENSION_VOLUME_MODE_CUSTOM_VALUE')) {
            customValueTr.show();
            weightUnitTr.show();
        }

        if (value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::DIMENSION_VOLUME_MODE_CUSTOM_ATTRIBUTE')) {
            AmazonTemplateDescriptionDefinitionHandlerObj.updateHiddenValue(this, customAttribute);
            weightUnitTr.show();
        }

        weightUnitMode.simulate('change');
    },

    weightUnitOfMeasureChangeAll: function(customValue, customAttribute, value)
    {
        var self = AmazonTemplateDescriptionDefinitionHandlerObj;

        customValue.value = '';
        customAttribute.value = '';
        if (value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_VALUE')) {
            self.updateHiddenValue(this, customValue);
        }

        if (value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, customAttribute);
        }
    },

    // ---------------------------------------

    item_dimensions_volume_mode_change: function()
    {
        var self = AmazonTemplateDescriptionDefinitionHandlerObj;

        self.volumeModeChange(
            $('item_dimensions_volume_custom_value_tr'), $('item_dimensions_volume_custom_attribute_tr'),
            $('item_dimensions_volume_unit_of_measure_mode_tr'), $('item_dimensions_volume_unit_of_measure_mode'),
            this.value
        );
    },

    item_dimensions_volume_unit_of_measure_mode_change: function()
    {
        var self = AmazonTemplateDescriptionDefinitionHandlerObj;
        self.weightUnitOfMeasureChangeAll.call(
            this,
            $('item_dimensions_volume_unit_of_measure_custom_value'),
            $('item_dimensions_volume_unit_of_measure_custom_attribute'),
            this.value
        );
    },

    item_dimensions_weight_mode_change: function()
    {
        var self = AmazonTemplateDescriptionDefinitionHandlerObj;
        self.weightModeChange.call(
            this,
            $('item_dimensions_weight_custom_value_tr'),
            $('item_dimensions_weight_custom_attribute'),
            $('item_dimensions_weight_unit_of_measure_mode_tr'),
            $('item_dimensions_weight_unit_of_measure_mode'),
            this.value
        );
    },

    item_dimensions_weight_unit_of_measure_mode_change: function()
    {
        var self = AmazonTemplateDescriptionDefinitionHandlerObj;
        self.weightUnitOfMeasureChangeAll.call(
            this,
            $('item_dimensions_weight_unit_of_measure_custom_value'),
            $('item_dimensions_weight_unit_of_measure_custom_attribute'),
            this.value
        );
    },

    // ---------------------------------------

    package_dimensions_volume_mode_change: function()
    {
        var self = AmazonTemplateDescriptionDefinitionHandlerObj;

        self.volumeModeChange(
            $('package_dimensions_volume_custom_value_tr'), $('package_dimensions_volume_custom_attribute_tr'),
            $('package_dimensions_volume_unit_of_measure_mode_tr'), $('package_dimensions_volume_unit_of_measure_mode'),
            this.value
        );
    },

    package_dimensions_volume_unit_of_measure_mode_change: function()
    {
        var self = AmazonTemplateDescriptionDefinitionHandlerObj;

        self.weightUnitOfMeasureChangeAll.call(
            this,
            $('package_dimensions_volume_unit_of_measure_custom_value'),
            $('package_dimensions_volume_unit_of_measure_custom_attribute'),
            this.value
        );
    },

    // ---------------------------------------

    package_weight_mode_change: function()
    {
        var self = AmazonTemplateDescriptionDefinitionHandlerObj;

        self.weightModeChange.call(
            this,
            $('package_weight_custom_value_tr'),
            $('package_weight_custom_attribute'),
            $('package_weight_unit_of_measure_mode_tr'),
            $('package_weight_unit_of_measure_mode'),
            this.value
        );
    },

    package_weight_unit_of_measure_mode_change: function()
    {
        var self = AmazonTemplateDescriptionDefinitionHandlerObj;

        self.weightUnitOfMeasureChangeAll.call(
            this,
            $('package_weight_unit_of_measure_custom_value'),
            $('package_weight_unit_of_measure_custom_attribute'),
            this.value
        );
    },

    shipping_weight_mode_change: function()
    {
        var self = AmazonTemplateDescriptionDefinitionHandlerObj;

        self.weightModeChange.call(
            this,
            $('shipping_weight_custom_value_tr'),
            $('shipping_weight_custom_attribute'),
            $('shipping_weight_unit_of_measure_mode_tr'),
            $('shipping_weight_unit_of_measure_mode'),
            this.value
        );
    },

    shipping_weight_unit_of_measure_mode_change: function()
    {
        var self = AmazonTemplateDescriptionDefinitionHandlerObj;

        self.weightUnitOfMeasureChangeAll.call(
            this,
            $('shipping_weight_unit_of_measure_custom_value'),
            $('shipping_weight_unit_of_measure_custom_attribute'),
            this.value
        );
    },

    // ---------------------------------------

    multi_element_mode_change: function(type, max)
    {
        var self = AmazonTemplateDescriptionDefinitionHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::' + type.toUpperCase() + '_MODE_NONE')) {

            $$('.' + type + '_tr').invoke('hide');
            $$('input[name="definition[' + type + '][]"]').each(function(obj) {
                obj.value = '';
            });
            $(type + '_actions_tr').hide();

        } else {

            var visibleElementsCounter = 0,
                emptyVisibleElements   = 0;

            $$('.' + type + '_tr').each(function(obj) {
                if (visibleElementsCounter == 0 || $(obj).select('input[name="definition[' + type + '][]"]')[0].value != '') {
                    $(obj).show();
                    visibleElementsCounter++;
                }
            });

            $(type + '_actions_tr').show();

            if (visibleElementsCounter > 1) {
                $('hide_' + type + '_action').removeClassName('action-disabled');
            }

            visibleElementsCounter < max ? $('show_' + type + '_action').removeClassName('action-disabled')
                                         : $('show_' + type + '_action').addClassName('action-disabled');

            if (visibleElementsCounter == 1 && $(type + '_0').value == '') {
                $('show_' + type + '_action').addClassName('action-disabled');
            }
        }
    },

    multi_element_keyup: function(type,element)
    {
        if (!element.value) {
            return $('show_' + type + '_action').addClassName('action-disabled');
        }

        var hiddenElements = $$('.' + type + '_tr').findAll(function(obj) {
            return !$(obj).visible();
        });

        if (hiddenElements.size() != 0) {
            $('show_' + type + '_action').removeClassName('action-disabled');
        }
    },

    description_mode_change: function()
    {
        this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::DESCRIPTION_MODE_CUSTOM')
            ? $$('.c-custom_description_tr').invoke('show')
            : $$('.c-custom_description_tr').invoke('hide');
    },

    image_main_mode_change: function()
    {
        var self = AmazonTemplateDescriptionDefinitionHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::IMAGE_MAIN_MODE_NONE')) {
            $('gallery_images_mode_tr').hide();
            $('gallery_images_mode').value = 0;
            $('gallery_images_mode').simulate('change');
        } else {
            $('gallery_images_mode_tr').show();
        }

        $('image_main_attribute').value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::IMAGE_MAIN_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('image_main_attribute'));
        }
    },

    image_variation_difference_mode_change: function()
    {
        var self = AmazonTemplateDescriptionDefinitionHandlerObj;

        $('image_variation_difference_attribute').value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::IMAGE_VARIATION_DIFFERENCE_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('image_variation_difference_attribute'));
        }
    },

    gallery_images_mode_change: function()
    {
        $('gallery_images_limit').value = '';
        $('gallery_images_attribute').value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::GALLERY_IMAGES_MODE_PRODUCT')) {
            AmazonTemplateDescriptionDefinitionHandlerObj.updateHiddenValue(this, $('gallery_images_limit'));
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::GALLERY_IMAGES_MODE_ATTRIBUTE')) {
            AmazonTemplateDescriptionDefinitionHandlerObj.updateHiddenValue(this, $('gallery_images_attribute'));
        }
    },

    // ---------------------------------------

    showElement: function(type)
    {
        var emptyVisibleElementsExist = $$('.' + type + '_tr').any(function(obj) {
            return $(obj).visible() && $(obj).select('input[name="definition[' + type + '][]"]')[0].value == '';
        });

        if (emptyVisibleElementsExist) {
            return;
        }

        var hiddenElements = $$('.' + type + '_tr').findAll(function(obj) {
            return !$(obj).visible();
        });

        if (hiddenElements.size() == 0) {
            return;
        }

        hiddenElements.shift().show();

        $('hide_' + type + '_action').removeClassName('action-disabled');
        $('show_' + type + '_action').addClassName('action-disabled');
    },

    hideElement: function(type, force)
    {
        force = force || false;

        var visibleElements = [];
        $$('.' + type + '_tr').each(function(el) {
            if(el.visible()) visibleElements.push(el);
        });

        if (visibleElements.length <= 0 || (!force && visibleElements[visibleElements.length - 1].getAttribute('undeletable'))) {
            return;
        }

        if (visibleElements.length == 1) {
            var elementMode = $(type + '_mode');
            elementMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::' + type.toUpperCase() + '_MODE_NONE');
            elementMode.simulate('change');
        }

        if (visibleElements.size() > 1) {

            var lastVisibleElement = visibleElements.pop();
            lastVisibleElement.select('input[name="definition[' + type + '][]"]')[0].value = '';
            lastVisibleElement.hide();

            var nextVisibleElement = visibleElements.pop();
            if(!force && nextVisibleElement.getAttribute('undeletable')) {
                $('hide_' + type + '_action').addClassName('action-disabled');
            }
        }

        $('show_' + type + '_action').removeClassName('action-disabled');
    },

    forceFillUpElement: function(type, value)
    {
        // check if already rendered. render if need.
        var neededTr = null;
        $$('.' + type + '_tr').each(function(obj) {
            if ($(obj).visible() && $(obj).select('input[name="definition[' + type + '][]"]')[0].value == value) {
                neededTr = obj;
                return false;
            }
        });

        if (!neededTr) {

            this.showElement(type);

            var emptyVisibleTrs = $$('.' + type + '_tr').findAll(function(obj) {
                return $(obj).visible() && $(obj).select('input[name="definition[' + type + '][]"]')[0].value == '';
            });

            neededTr = emptyVisibleTrs.pop();
        }
        // ---------------------------------------

        // ---------------------------------------
        var input = neededTr.down('input[type="text"]');

        input.setAttribute('disabled', 'disabled');
        input.value = value;

        neededTr.down('td.value').appendChild(new Element('input', {
            name  : input.name,
            type  : 'hidden',
            value : value
        }));

        neededTr.setAttribute('undeletable', '1');
        neededTr.down('td.attributes-container-td').hide();
        // ---------------------------------------

        $('hide_' + type + '_action').addClassName('action-disabled');
        this.multi_element_keyup(type, {value:' '});
    },

    forceClearElements: function(type)
    {
        var self = this;

        var visibleTrs = $$('.' + type + '_tr').findAll(function(obj) {
            return $(obj).visible();
        });

        visibleTrs.each(function(el) {

            el.removeAttribute('undeletable');
            el.down('input[type="text"]').removeAttribute('disabled');
            el.down('td.attributes-container-td').show();

            var hiddenInput = el.down('input[type="hidden"]');
            hiddenInput && hiddenInput.remove();

            self.hideElement(type, true);
        });

        $('hide_' + type + '_action').removeClassName('action-disabled');
    }

    // ---------------------------------------
});