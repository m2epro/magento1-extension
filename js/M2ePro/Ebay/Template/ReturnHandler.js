EbayTemplateReturnHandler = Class.create(CommonHandler, {

    // ---------------------------------------

    initialize: function() {},

    // ---------------------------------------

    acceptedChange: function()
    {
        var descriptionTr = $('magento_block_ebay_template_return_form_data_policy_general');

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Return::RETURNS_ACCEPTED')) {
            $('return_option_tr')[$$('#return_option option').length ? 'show' : 'hide']();
            $('return_within_tr')[$$('#return_within option').length ? 'show' : 'hide']();
            $('return_shipping_cost_tr')[$$('#return_shipping_cost option').length ? 'show' : 'hide']();

            $('magento_block_ebay_template_return_form_data_policy_international').show();
            descriptionTr && descriptionTr.show();
        } else {
            $$('.return-accepted').invoke('hide');
        }

        $('return_international_accepted').simulate('change');
    },

    internationalAcceptedChange: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Return::RETURNS_ACCEPTED')) {
            $('return_international_option_tr')[$$('#return_international_option option').length ? 'show' : 'hide']();
            $('return_international_within_tr')[$$('#return_international_within option').length ? 'show' : 'hide']();
            $('return_international_shipping_cost_tr')[$$('#return_international_shipping_cost option').length ? 'show' : 'hide']();
        } else {
            $$('.return-international-accepted').invoke('hide');
        }
    }

    // ---------------------------------------
});