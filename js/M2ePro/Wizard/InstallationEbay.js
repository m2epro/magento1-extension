WizardInstallationEbay = Class.create(CommonHandler, {

    // ---------------------------------------

    licenseForm      : null,
    popupLicenseForm : null,

    // License
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
            height: 360,
            zIndex: 100,
            recenterAuto: false,
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

        this.licenseForm.validate();
    },

    // ---------------------------------------

    copyValuesFromPopup: function()
    {
        Form.getElements($(this.popupLicenseForm.formId)).each(function(element) {
            var td = $(element.readAttribute('name'));
            td.down('span').update(element.value.escapeHTML());
            td.down('input').value = element.value.escapeHTML();
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

    createLicenseAndGetToken: function(accountMode)
    {
        if (!this.licenseForm.validate()) {
            return false;
        }

        var formData = {};

        Form.getElements($(this.popupLicenseForm.formId)).each(function(element) {
            var attribute = element.readAttribute('name');
            element.value && (formData[attribute] = element.value);
        });

        formData = Object.extend({account_mode: accountMode}, formData);

        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_wizard_installationEbay/beforeToken'), {
            method       : 'post',
            asynchronous : true,
            parameters   : formData,
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();

                if (!response['url'] && response['message']) {
                    MagentoMessageObj.addError(response['message']);
                    return CommonHandlerObj.scroll_page_to_top();
                }

                if (!response['url']) {
                    MagentoMessageObj.addError(M2ePro.translator.translate('An error during of Extension Key creation occurred.'));
                    return CommonHandlerObj.scroll_page_to_top();
                }

                return setLocation(response['url']);
            }
        });
    },

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

    // Account
    // ---------------------------------------

    initAccountSettings: function()
    {
        new Ajax.Request( M2ePro.url.get('*/*/getAccountSettings'), {
            method: 'get',
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();

                if (response.result == 'error') {
                    MagentoMessageObj.addError(response.message);
                    return CommonHandlerObj.scroll_page_to_top();
                }

                $H(response.text).each(function(item) {
                    $(item.key).update(item.value);
                });
            }
        });
    },

    openEditAccountPage: function()
    {
        var self = this;
        var win = window.open( M2ePro.url.get('adminhtml_ebay_account/edit') );

        var intervalId = setInterval(function() {

            if (!win.closed) {
                return;
            }

            clearInterval(intervalId);
            self.initAccountSettings();

        }, 1000);
    }

    // ---------------------------------------
});