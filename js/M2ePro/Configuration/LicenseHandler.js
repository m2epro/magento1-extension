ConfigurationLicenseHandler = Class.create();
ConfigurationLicenseHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    initialize: function() {},

    // ---------------------------------------

    changeLicenseKey: function()
    {
        $('license_text_key_container').hide();
        $('license_input_key_container').show();
        $('change_license_key_container').hide();
        $('confirm_license_key_container').show();
    },

    // ---------------------------------------

    confirmLicenseKey: function()
    {
        configEditForm.submit(M2ePro.url.get('adminhtml_configuration_license/confirmKey'));
    },

    // ---------------------------------------

    completeStep: function()
    {
        var self = this;
        var checkResult = false;

        new Ajax.Request(M2ePro.url.get('adminhtml_configuration_license/checkLicense'), {
            method: 'get',
            asynchronous: true,
            onSuccess: function(transport) {
                checkResult = transport.responseText.evalJSON()['ok'];
                if (checkResult) {
                    window.opener.completeStep = 1;
                    window.close();
                } else {
                    MagentoMessageObj.addError(M2ePro.translator.translate('You must get valid License Key.'));
                }
            }
        });
    }

    // ---------------------------------------
});