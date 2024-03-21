window.WizardInstallationEbay = Class.create(Common, {

    // ---------------------------------------

    continueStep: function ()
    {
        if (WizardObj.steps.current.length) {
            this[WizardObj.steps.current + 'Step']();
        }
    },

    registrationStep: function ()
    {
        WizardObj.registrationStep(M2ePro.url.get('adminhtml_wizard_installationEbay/createLicense'));
    },

    accountStep: function ()
    {
        var editForm = new varienForm('edit_form');
        if (!editForm.validate()) {
            return false;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_wizard_installationEbay/beforeGetSellApiToken'), {
            method       : 'post',
            asynchronous : true,
            parameters   : $('edit_form').serialize(),
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

                return setLocation(response['url']);
            }
        });
    },

    listingTutorialStep: function ()
    {
        WizardObj.setStep(WizardObj.getNextStep(), function (){
            WizardObj.complete();
        });
    }

    // ---------------------------------------
});