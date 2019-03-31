WalmartTemplateProductTaxCodeHandler = Class.create();
WalmartTemplateProductTaxCodeHandler.prototype = Object.extend(new WalmartTemplateEditHandler(), {

    rulesIndex: 0,

    // ---------------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-tpl-title',
                                                M2ePro.translator.translate('The specified Title is already used for other Policy. Policy Title must be unique.'),
                                                'Walmart_Template_ProductTaxCode', 'title', 'id',
                                                M2ePro.formData.id);
    },

    // ---------------------------------------

    duplicate_click: function($headId)
    {
        this.setValidationCheckRepetitionValue('M2ePro-tpl-title',
                                                M2ePro.translator.translate('The specified Title is already used for other Policy. Policy Title must be unique.'),
                                                'Walmart_Template_ProductTaxCode', 'title', 'id', '');

        CommonHandlerObj.duplicate_click($headId, M2ePro.translator.translate('Add Product Tax Code Policy'));
    },

    // ---------------------------------------

    productTaxCodeModeChange: function()
    {
        $('product_tax_code_custom_value_tr').hide();
        $('product_tax_code_attribute').value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Template_ProductTaxCode::PRODUCT_TAX_CODE_MODE_VALUE')) {
            $('product_tax_code_custom_value_tr').show();
        } else if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Template_ProductTaxCode::PRODUCT_TAX_CODE_MODE_ATTRIBUTE')) {
            WalmartTemplateProductTaxCodeHandlerObj.updateHiddenValue(this, $('product_tax_code_attribute'));
        }
    }

    // ---------------------------------------

});