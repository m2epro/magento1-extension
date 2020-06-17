window.EbayConfiguration = Class.create(Common, {

    // ---------------------------------------

    initialize: function() {},

    // ---------------------------------------

    // Manage Compatibility Dictionary
    // ---------------------------------------

    manageMotorsRecords: function(motorsType, title)
    {
        var self = EbayConfigurationObj;

        // ---------------------------------------
        $('motors_type').value = motorsType;
        var helpBlock = $$('#block_notice_ebay_configuration_general_motors_manage span.title').first();
        if (helpBlock) helpBlock.innerHTML = title;
        // ---------------------------------------

        // ---------------------------------------
        $$('.database-statistic-popup').each(function (el) {
            el.hide();
        });

        if (motorsType == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID_MOTOR')) {
            $('database-statistic-popup-epids-motor').show();
        } else if (motorsType == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID_UK')) {
            $('database-statistic-popup-epids-uk').show();
        } else if (motorsType == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID_DE')) {
            $('database-statistic-popup-epids-de').show();
        } else if (motorsType == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID_AU')) {
            $('database-statistic-popup-epids-au').show();
        } else {
            $('database-statistic-popup-ktypes').show();
        }
        // ---------------------------------------

        if (typeof self.popUp != 'undefined') {

            self.popUp.setTitle(title);
            self.popUp.showCenter(true, 50);
            return;
        }

        self.popUp = Dialog.info(null, {
            id: 'manage_motors_popup',
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 50,
            maxHeight: 520,
            width: 700,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            onShow: function() {
                Windows.getFocusedWindow().content.style.height = '';
                Windows.getFocusedWindow().content.style.maxHeight = '520px';
            }
        });

        var content = $('manage_motors_popup_contents');

        $('modal_dialog_message').insert(content.innerHTML);
        $('modal_dialog_message').innerHTML.evalScripts();

        content.innerHTML = '';

        self.popUp.options.destroyOnClose = false;
    },

    importMotorsRecords: function()
    {
        if (!Validation.validate($('source'))) {
            return false;
        }

        $('ebay_configuration_general_motors_manage_form').submit();
    },

    clearAddedMotorsRecords: function()
    {
        var self = EbayConfigurationObj;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        var url = M2ePro.url.get('adminhtml_ebay_configuration/clearAddedMotorsData');
        self.postForm(url, {motors_type: $('motors_type').value});
    }

    // ---------------------------------------
});