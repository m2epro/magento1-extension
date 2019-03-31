ConfigurationSettingsHandler = Class.create();
ConfigurationSettingsHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    templateEditHandlerObj: null,

    // ---------------------------------------

    initialize: function()
    {
        this.templateEditHandlerObj = new TemplateEditHandler();
    },

    // ---------------------------------------

    changeForceQtyMode: function()
    {
        if($('force_qty_mode').value == 1) {
            $('force_qty_value_tr').show();
        } else {
            $('force_qty_value_tr').hide();
        }
    },

    restoreAllHelpsAndRememberedChoices: function ()
    {
        var self = this;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_configuration_settings/restoreBlockNotices'), {
            method: 'post',
            asynchronous: false,
            onSuccess: function(transport) {

                ModuleNoticeObj.deleteAllHashedStorage();
                MagentoBlockObj.deleteAllHashedStorage();
                self.templateEditHandlerObj.forgetSkipSaveConfirmation();

                MagentoMessageObj.addSuccess(
                    M2ePro.translator.translate('Help Blocks have been successfully restored.')
                );
            }
        });
    }

    // ---------------------------------------
});