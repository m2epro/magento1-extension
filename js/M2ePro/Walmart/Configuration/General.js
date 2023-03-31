window.WalmartConfigurationGeneral = Class.create(Common, {

    // ---------------------------------------

    initialize: function()
    {
        var self = this;

        Validation.add('M2ePro-walmart-required-identifier-setting', M2ePro.translator.translate('Required identifier'), function(value,el) {
            if ($('product_id_override_mode').value == self.PRODUCT_ID_OVERRIDE_MODE_ALL) {
                return true;
            }

            return $('product_id_mode').value > 0;
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

        if ($('sku_modification_mode').value != self.SKU_MODIFICATION_MODE_TEMPLATE) {
            $('sku_modification_custom_value').value = '';
        }

        if ($('sku_modification_mode').value == self.SKU_MODIFICATION_MODE_NONE) {
            $('sku_modification_custom_value_tr').hide();
        } else {
            $('sku_modification_custom_value_tr').show();
        }
    },

    // ---------------------------------------

    product_id_mode_change: function()
    {
        var self = WalmartConfigurationGeneralObj;

        $('product_id_custom_attribute').value = '';
        if (this.value == self.PRODUCT_ID_MODE_CUSTOM_ATTRIBUTE) {
            self.updateHiddenValue(this, $('product_id_custom_attribute'));
        }
    },

    // ---------------------------------------
});