window.WalmartConfigurationGeneral = Class.create(Common, {

    // ---------------------------------------

    initialize: function()
    {
        var self = this;

        Validation.add('M2ePro-walmart-required-identifier-setting', M2ePro.translator.translate('Required at least one identifier'), function(value,el) {

            var result = false;

            if ($('product_id_override_mode').value == self.PRODUCT_ID_OVERRIDE_MODE_ALL) {
                return true;
            }

            $$('.M2ePro-walmart-required-identifier-setting').each(function(obj) {
                if (obj.value > 0) {
                    result = true;
                    return;
                }
            });

            return result;
        });
    },

    // ---------------------------------------

    sku_mode_change: function()
    {
        var self = WalmartConfigurationGeneralObj;

        $('sku_custom_attribute').value = '';
        if (this.value == self.SKU_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('sku_custom_attribute'));
        }
    },
    // ---------------------------------------

    sku_modification_mode_change: function()
    {
        var self = WalmartConfigurationGeneralObj;

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

    upc_mode_change: function()
    {
        var self = WalmartConfigurationGeneralObj;

        $('upc_custom_attribute').value = '';
        if (this.value == self.UPC_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('upc_custom_attribute'));
        }
    },

    // ---------------------------------------

    ean_mode_change: function()
    {
        var self = WalmartConfigurationGeneralObj;

        $('ean_custom_attribute').value = '';
        if (this.value == self.EAN_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('ean_custom_attribute'));
        }
    },

    // ---------------------------------------

    gtin_mode_change: function()
    {
        var self = WalmartConfigurationGeneralObj;

        $('gtin_custom_attribute').value = '';
        if (this.value == self.GTIN_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('gtin_custom_attribute'));
        }
    },

    // ---------------------------------------

    isbn_mode_change: function()
    {
        var self = WalmartConfigurationGeneralObj;

        $('isbn_custom_attribute').value = '';
        if (this.value == self.ISBN_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('isbn_custom_attribute'));
        }
    },

    // ---------------------------------------
});