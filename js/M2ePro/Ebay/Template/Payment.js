window.EbayTemplatePayment = Class.create(Common, {

    // ---------------------------------------

    initialize: function()
    {
        Validation.add('M2ePro-validate-payment-methods', M2ePro.translator.translate('Payment method should be specified.'), function(value) {

            if ($('managed_payments_mode') && $('managed_payments_mode').checked) {
                return true;
            }

            if ($('pay_pal_mode').checked) {
                return true;
            }

            return $$('input[name="payment[services][]"]').any(function(o) {
                return o.checked;
            });
        });
    },

    // ---------------------------------------

    managedPaymentsModeChange: function()
    {
        if (this.checked) {
            $('pay_pal_mode').checked = false;
            $('pay_pal_mode').simulate('change');
            $('pay_pal_mode').setAttribute('disabled', 'disabled');

            $('pay_pal_immediate_payment').setAttribute('disabled', 'disabled');

            $$('input[name="payment[services][]"]').each(function(payment) {
                payment.checked = false;
                payment.setAttribute('disabled', 'disabled');
            });
        } else {
            $('pay_pal_mode').removeAttribute('disabled');
            $('pay_pal_immediate_payment').removeAttribute('disabled');

            $$('input[name="payment[services][]"]').each(function(payment) {
                payment.removeAttribute('disabled');
            });
        }
    },

    // ---------------------------------------

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

    // ---------------------------------------

    immediatePaymentChange: function()
    {
        if (this.checked) {
            $('magento_block_ebay_template_payment_form_data_additional_service').hide();

            $$('input[name="payment[services][]"]').each(function(payment) {
                payment.checked = false;
            });
        } else {
            $('magento_block_ebay_template_payment_form_data_additional_service').show();
        }
    }

    // ---------------------------------------
});