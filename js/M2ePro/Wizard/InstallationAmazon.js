window.WizardInstallationAmazon = Class.create(Common, {

    // ---------------------------------------

    continueStep: function ()
    {
        if (WizardObj.steps.current.length) {
            this[WizardObj.steps.current + 'Step']();
        }
    },

    registrationStep: function ()
    {
        WizardObj.registrationStep(M2ePro.url.get('adminhtml_wizard_installationAmazon/createLicense'));
    },

    accountStep: function ()
    {
        MessageObj.clearAll();

        var marketplaceId = $('marketplace_id').value;

        if (!marketplaceId) {
            MessageObj.addError(M2ePro.translator.translate('Please select Marketplace first.'));
            return CommonObj.scroll_page_to_top();
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_wizard_installationAmazon/beforeToken'), {
            method       : 'post',
            asynchronous : true,
            parameters   : {marketplace_id: marketplaceId},
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();

                if (response && response['message']) {
                    MessageObj.addError(response['message']);
                    return CommonObj.scroll_page_to_top();
                }

                if (!response['url']) {
                    MessageObj.addError(M2ePro.translator.translate('An error during of account creation.'));
                    return CommonObj.scroll_page_to_top();
                }

                window.location.href = response['url'];
            }
        });
    },

    settingsStep: function ()
    {
        this.submitForm(M2ePro.url.get('adminhtml_wizard_installationAmazon/settingsContinue'));
    },

    listingTutorialStep: function ()
    {
        WizardObj.setStep(WizardObj.getNextStep(), setLocation.bind(window, location.href));
    }
});
