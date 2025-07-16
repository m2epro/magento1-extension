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

        var params = $('edit_form').serialize(true);
        params.marketplace_id = $('marketplace_id').value;

        new Ajax.Request(M2ePro.url.get('adminhtml_wizard_installationWalmart/accountContinue'), {
            method       : 'post',
            parameters   : params,
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
        $('edit_form').hide();
        $('account_us_connect').hide();
        if (marketplaceId == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_CA')) {
            $('edit_form').show();
            $('continue').show();
        }

        if (marketplaceId == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_US')) {
            $('account_us_connect').show();
            $('continue').hide();
        }
    }

    // ---------------------------------------
});