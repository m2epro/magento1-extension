EbayConfigurationHandler = Class.create();
EbayConfigurationHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    isMultiCurrencyPresented: function()
    {
        return Boolean(M2ePro.formData.multiCurrencyCount);
    },

    //----------------------------------

    viewModeChange: function()
    {
        var hidingBlocks = $$('#magento_block_ebay_configuration_general_notification',
                              '#magento_block_ebay_configuration_general_selling',
                              '#magento_block_ebay_configuration_general_parts_compatibility_epids',
                              '#magento_block_ebay_configuration_general_parts_compatibility_ktypes');

        hidingBlocks.invoke('hide');
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Helper_View_Ebay::MODE_ADVANCED')) {
            hidingBlocks.invoke('show');
        }
    },

    //-- Manage Compatibility Dictionary
    //----------------------------------

    manageCompatibilityRecords: function(compatibilityType)
    {
        var self = EbayConfigurationGeneralHandlerObj,
            title = compatibilityType == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_SPECIFIC') ? 'Manage Custom Compatibility [ePIDs]'
                                                                                                                                    : 'Manage Custom Compatibility [kTypes]';
        // --
        $('compatibility_type').value = compatibilityType;
        var helpBlock = $$('#block_notice_ebay_configuration_general_parts_compatibility_manage span.title').first();
        if (helpBlock) helpBlock.innerHTML = title;
        // --

        // --
        var spanStatEpids  = $('database-statistic-popup-epids'),
            spanStatKtypes = $('database-statistic-popup-ktypes');

        if (compatibilityType == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_SPECIFIC')) {

            spanStatEpids.show();
            spanStatKtypes.hide();

        } else {

            spanStatEpids.hide();
            spanStatKtypes.show();
        }
        // --

        if (typeof self.popUp != 'undefined') {

            self.popUp.setTitle(title);
            self.popUp.showCenter(true, 50);
            return;
        }

        self.popUp = Dialog.info(null, {
            id: 'manage_compatibility_popup',
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

        var content = $('manage_compatibility_popup_contents');

        $('modal_dialog_message').insert(content.innerHTML);
        $('modal_dialog_message').innerHTML.evalScripts();

        content.innerHTML = '';

        self.popUp.options.destroyOnClose = false;
    },

    importCompatibilityRecords: function()
    {
        if (!Validation.validate($('source'))) {
            return false;
        }

        $('ebay_configuration_general_parts_compatibility_manage_form').submit();
    },

    clearAddedCompatibilityRecords: function()
    {
        var self = EbayConfigurationGeneralHandlerObj;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        var url = M2ePro.url.get('adminhtml_ebay_configuration/clearAddedPartsCompatibilityData');
        self.postForm(url, {compatibility_type: $('compatibility_type').value});
    }

    //----------------------------------
});