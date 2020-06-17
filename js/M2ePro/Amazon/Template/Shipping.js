window.AmazonTemplateShipping = Class.create(AmazonTemplateEdit, {

    rulesIndex: 0,

    // ---------------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-shipping-tpl-title',
                                                M2ePro.translator.translate('The specified Title is already used for other Policy. Policy Title must be unique.'),
                                                'Amazon_Template_Shipping', 'title', 'id',
                                                M2ePro.formData.id);
    },

    // ---------------------------------------

    duplicate_click: function($headId)
    {
        this.setValidationCheckRepetitionValue('M2ePro-shipping-tpl-title',
                                                M2ePro.translator.translate('The specified Title is already used for other Policy. Policy Title must be unique.'),
                                                'Amazon_Template_Shipping', 'title', 'id', '');

        CommonObj.duplicate_click($headId, M2ePro.translator.translate('Add Shipping Policy'));
    },

    // ---------------------------------------

    templateNameModeChange: function()
    {
        $('template_name_custom_value_tr').hide();
        $('template_name_attribute').value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Shipping::TEMPLATE_NAME_VALUE')) {
            $('template_name_custom_value_tr').show();
        } else if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Shipping::TEMPLATE_NAME_ATTRIBUTE')) {
            AmazonTemplateShippingObj.updateHiddenValue(this, $('template_name_attribute'));
        }
    }

    // ---------------------------------------

});