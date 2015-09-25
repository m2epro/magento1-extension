EbayTemplatePaymentHandler = Class.create(CommonHandler, {

    //----------------------------------

    initialize: function()
    {
        Validation.add('M2ePro-validate-payment-methods', M2ePro.translator.translate('Payment method should be specified.'), function(value) {

            if ($('pay_pal_mode').checked) {
                return true;
            }

            return $$('input[name="payment[services][]"]').any(function(o) {
                return o.checked;
            });
        });
    },

    //----------------------------------

    payPalModeChange: function()
    {
        if (this.checked) {
            $('pay_pal_email_address_container').show();
            $('pay_pal_immediate_payment_container').show();
        } else {
            $('pay_pal_email_address').setValue('');
            $('pay_pal_email_address_container').hide();
            $('pay_pal_immediate_payment_container').hide();
            $('pay_pal_immediate_payment').checked = false;
            $('pay_pal_immediate_payment').simulate('change');
        }
    },

    //----------------------------------

    immediatePaymentChange: function()
    {
        if (this.checked) {
            $('magento_block_ebay_template_payment_form_data_additional_service').hide();

            $$('input.additional-payment-service').each(function(payment) {
                payment.checked = false;
            });
        } else {
            $('magento_block_ebay_template_payment_form_data_additional_service').show();
        }
    }

    //----------------------------------
});