ConfigurationSettingsHandler = Class.create();
ConfigurationSettingsHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    changeForceQtyMode: function()
    {
        if($('force_qty_mode').value == 1) {
            $('force_qty_value_tr').show();
        } else {
            $('force_qty_value_tr').hide();
        }
    },

    changeBlockNoticesShow: function()
    {
        if ($('block_notices_show').value == 1) {
            $('restore_block_notices_tr').show();
        } else {
            $('restore_block_notices_tr').hide();
        }
    }

    //----------------------------------
});