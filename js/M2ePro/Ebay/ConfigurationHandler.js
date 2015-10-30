EbayConfigurationHandler = Class.create();
EbayConfigurationHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    initialize: function() {},

    // ---------------------------------------

    isMultiCurrencyPresented: function()
    {
        return Boolean(M2ePro.formData.multiCurrencyCount);
    },

    // ---------------------------------------

    viewModeChange: function()
    {
        var hidingBlocks = $$('#magento_block_ebay_configuration_general_notification',
                              '#magento_block_ebay_configuration_general_selling',
                              '#magento_block_ebay_configuration_general_motors_epids',
                              '#magento_block_ebay_configuration_general_motors_ktypes');

        hidingBlocks.invoke('hide');
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Helper_View_Ebay::MODE_ADVANCED')) {
            hidingBlocks.invoke('show');
        }
    },

    // Manage Compatibility Dictionary
    // ---------------------------------------

    manageMotorsRecords: function(motorsType)
    {
        var self = EbayConfigurationGeneralHandlerObj,
            title = motorsType == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID') ? 'Manage Custom Compatibility [ePIDs]'
                                                                                                                                    : 'Manage Custom Compatibility [kTypes]';
        // ---------------------------------------
        $('motors_type').value = motorsType;
        var helpBlock = $$('#block_notice_ebay_configuration_general_motors_manage span.title').first();
        if (helpBlock) helpBlock.innerHTML = title;
        // ---------------------------------------

        // ---------------------------------------
        var spanStatEpids  = $('database-statistic-popup-epids'),
            spanStatKtypes = $('database-statistic-popup-ktypes');

        if (motorsType == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID')) {

            spanStatEpids.show();
            spanStatKtypes.hide();

        } else {

            spanStatEpids.hide();
            spanStatKtypes.show();
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
            title: M2ePro.translator.translate(title),
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
        var self = EbayConfigurationGeneralHandlerObj;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        var url = M2ePro.url.get('adminhtml_ebay_configuration/clearAddedMotorsData');
        self.postForm(url, {motors_type: $('motors_type').value});
    }

    // ---------------------------------------
});