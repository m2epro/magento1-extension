window.WizardInstallationWalmart = Class.create(Common, {

    // ---------------------------------------

    continueStep: function ()
    {
        if (WizardObj.steps.current.length) {
            this[WizardObj.steps.current + 'Step']();
        }
    },

    registrationStep: function ()
    {
        WizardObj.registrationStep(M2ePro.url.get('adminhtml_wizard_installationWalmart/createLicense'));
    },

    accountStep: function ()
    {
        var editForm = new varienForm('edit_form');
        if (!editForm.validate()) {
            return false;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_wizard_installationWalmart/accountContinue'), {
            method       : 'post',
            parameters   : $('edit_form').serialize(true),
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();

                MessageObj.clearAll();

                if (response && response['message']) {
                    MessageObj.addError(response['message']);
                    return CommonObj.scroll_page_to_top();
                }

                window.location.reload();
            }
        });
    },

    settingsStep: function ()
    {
        var editForm = new varienForm('edit_form');
        if (!editForm.validate()) {
            return false;
        }

        this.submitForm(M2ePro.url.get('adminhtml_wizard_installationWalmart/settingsContinue'));
    },

    listingTutorialStep: function ()
    {
        setLocation(M2ePro.url.get('adminhtml_wizard_installationWalmart/listingTutorialContinue'));
    },

    // ---------------------------------------

    changeMarketplace: function(marketplaceId)
    {
        $$('.marketplace-required-field').each(function(obj) {
            obj.up('.field-row').hide();
        });

        if (marketplaceId === '') {
            return;
        }

        $$('.marketplace-required-field-id' + marketplaceId, '.marketplace-required-field-id-not-null').each(function(obj) {
            obj.up('.field-row').show();
        });
    }

    // ---------------------------------------
});