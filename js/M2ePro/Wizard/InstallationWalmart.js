WizardInstallationWalmart = Class.create(CommonHandler, {

    // ---------------------------------------

    popupObj : null,

    licenseForm      : null,
    popupLicenseForm : null,
    settingsForm     : null,

    userInfoEditMode : false,

    // ---------------------------------------

    initLicense: function()
    {
        this.licenseForm      = new varienForm('license_form');
        this.popupLicenseForm = new varienForm('popup_license_form');
    },

    // ---------------------------------------

    openPopupAction: function()
    {
        this.licensePopUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Register Your M2E Pro Extension'),
            width: 640,
            height: 350,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        this.copyValuesToPopup();
        this.licensePopUp.options.destroyOnClose = false;

        $('modal_dialog_message').insert($('license_popup_content').show());
    },

    closePopupAction: function()
    {
        this.licensePopUp.close();
    },

    confirmPopupAction: function()
    {
        if (!this.popupLicenseForm.validate()) {
            return;
        }

        this.copyValuesFromPopup();
        this.closePopupAction();

        if (this.userInfoEditMode) {
            var formData = {};

            Form.getElements($(this.popupLicenseForm.formId)).each(function(element) {
                var attribute = element.readAttribute('name');
                element.value && (formData[attribute] = element.value);
            });

            MagentoMessageObj.clearAll();

            new Ajax.Request(M2ePro.url.get('adminhtml_wizard_installationWalmart/updateLicenseUserInfo'), {
                method       : 'post',
                asynchronous : true,
                parameters   : formData,
                onSuccess: function(transport) {

                    var response = transport.responseText.evalJSON();

                    if (response && response['message']) {
                        MagentoMessageObj.addError(response['message']);
                        return CommonHandlerObj.scroll_page_to_top();
                    }

                    if (!response['result']) {
                        MagentoMessageObj.addError(M2ePro.translator.translate('Error update License.'));
                        return CommonHandlerObj.scroll_page_to_top();
                    }

                    $('edit_license').hide();
                }
            });
        }

        this.licenseForm.validate();
    },

    // ---------------------------------------

    copyValuesFromPopup: function()
    {
        Form.getElements($(this.popupLicenseForm.formId)).each(function(element) {
            var td = $(element.readAttribute('name'));
            td.down('span').update(element.value);
            td.down('input').value = element.value;
        });
    },

    copyValuesToPopup: function()
    {
        Form.getElements($(this.popupLicenseForm.formId)).each(function(element) {
            var tempValue = $(element.name).down('input').value.trim();
            element.setValue(tempValue);
        });
    },

    // ---------------------------------------

    proceedLicenseStep: function()
    {
        if (!this.licenseForm.validate()) {
            return false;
        }

        this.createLicense();
    },

    createLicense: function()
    {
        var self     = this,
            formData = {};

        Form.getElements($(this.popupLicenseForm.formId)).each(function(element) {
            var attribute = element.readAttribute('name');
            element.value && (formData[attribute] = element.value);
        });

        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_wizard_installationWalmart/createLicense'), {
            method       : 'post',
            asynchronous : true,
            parameters   : formData,
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();

                if (response && response['message']) {
                    MagentoMessageObj.addError(response['message']);
                    return CommonHandlerObj.scroll_page_to_top();
                }

                if (!response['result']) {
                    MagentoMessageObj.addError(M2ePro.translator.translate('Error create License.'));
                    return CommonHandlerObj.scroll_page_to_top();
                }

                $('block_private_policy_agreement').hide();
                $('edit_license').hide();
                self.doStep('license');
            }
        });
    },

    // ---------------------------------------

    doStep: function(currentStep)
    {
        var nextStep = WizardHandlerObj.getNextStepByNick(currentStep);

        if (nextStep) {
            return WizardHandlerObj.setStep(nextStep, function() {
                WizardHandlerObj.renderStep(currentStep);
            });
        }

        WizardHandlerObj.setStatus(M2ePro.php.constant('Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED'), function() {
            WizardHandlerObj.renderStep(currentStep);
            WizardHandlerObj.setStep(null);
            window.location = M2ePro.url.get('adminhtml_wizard_installationWalmart/congratulation');
        })
    },

    // ---------------------------------------

    checkFormFilling: function()
    {
        this.copyValuesToPopup();

        if (this.licenseForm.validate()) {
            return true;
        }

        $('edit_license').show();

        $('edit_license').simulate('click');
        $('license_popup_confirm_button').click();

        return false;
    },

    // ---------------------------------------

    saveSettingsStep: function()
    {
        if (wizardSettings.validator.validate()) {

            this.doStep('settings');

            wizardSettings.submit();
        }
    }

    // ---------------------------------------
});