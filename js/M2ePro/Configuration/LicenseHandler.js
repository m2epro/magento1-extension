ConfigurationLicenseHandler = Class.create();
ConfigurationLicenseHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    changeLicenseKeyPopup: function()
    {
        changeLicensePopup = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Extension Key'),
            top: 150,
            width: 450,
            height: 250,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        changeLicensePopup.options.destroyOnClose = true;
        $('modal_dialog_message').insert($('change_license_popup').innerHTML);
        ModuleNoticeObj.observeModulePrepareStart($('modal_dialog_message').down('#block_notice_change_license'));

        var self = this;
        $('block_notice_change_license').observe('click', function(e) {
            setTimeout(function() {
                self.autoHeightFix();
            }.bind(this), 1000)
        });
        self.autoHeightFix();
    },

    // ---------------------------------------

    confirmLicenseKey: function()
    {
        var newLicenseKey = $('new_license_key').value.trim(),
            oldLicenseKey = $('license_text_key_container').innerHTML.trim();

        var licenseForm = new varienForm('popup_change_license_form');
        if (!licenseForm.validate()) {
            return;
        }

        if (oldLicenseKey == newLicenseKey) {
            changeLicensePopup.close();
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_configuration_license/confirmKey'), {
            method: 'post',
            asynchronous: false,
            parameters: {
                key: newLicenseKey
            },
            onSuccess: function(transport) {}
        });

        changeLicensePopup.close();
        location.reload();
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
                    MagentoMessageObj.addError(M2ePro.translator.translate('You must get valid Trial or Live Extension Key.'));
                }
            }
        });
    }

    // ---------------------------------------
});