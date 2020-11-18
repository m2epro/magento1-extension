window.ConfigurationSettings = Class.create(Common, {

    // ---------------------------------------

    templateEditObj: null,

    // ---------------------------------------

    initialize: function()
    {
        this.templateEditObj = new TemplateEdit();
    },

    // ---------------------------------------

    changeForceQtyMode: function()
    {
        if($('product_force_qty_mode').value == 1) {
            $('product_force_qty_value_tr').show();
        } else {
            $('product_force_qty_value_tr').hide();
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
                self.templateEditObj.forgetSkipSaveConfirmation();

                MessageObj.addSuccess(
                    M2ePro.translator.translate('Help Blocks have been restored.')
                );
            }
        });
    }

    // ---------------------------------------
});