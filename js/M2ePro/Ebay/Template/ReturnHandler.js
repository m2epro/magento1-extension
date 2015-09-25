EbayTemplateReturnHandler = Class.create(CommonHandler, {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    acceptedChange: function()
    {
        if (this.value == 'ReturnsAccepted') {
            $('return_option_tr')[$$('#return_option option').length ? 'show' : 'hide']();
            $('return_within_tr')[$$('#return_within option').length ? 'show' : 'hide']();
            $('return_shipping_cost_tr')[$$('#return_shipping_cost option').length ? 'show' : 'hide']();
            $('return_restocking_fee_tr')[$$('#return_restocking_fee option').length ? 'show' : 'hide']();
            $('return_description_tr').show();

            if ($('return_holiday_tr')) {
                $('return_holiday_tr').show();
            }
        } else {
            $$('.return-accepted').invoke('hide');

            $('return_holiday_mode').selectedIndex = 0;
            $('return_holiday_mode').simulate('change');
        }
    }

    //----------------------------------
});