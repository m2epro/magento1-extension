ControlPanelHandler = Class.create(CommonHandler, {

    // ---------------------------------------

    initialize: function()
    {
        var cmdKeys = [67, 79, 78, 84, 82, 79, 76, 80, 65, 78, 69, 76];
        var cmdPressedKeys = [];

        document.observe('keyup', function(event) {

            if (cmdPressedKeys.length < cmdKeys.length) {
                if (cmdKeys[cmdPressedKeys.length] == event.keyCode) {
                    cmdPressedKeys.push(event.keyCode);
                } else {
                    cmdPressedKeys = [];
                }
            }

            if (cmdPressedKeys.length == cmdKeys.length) {

                window.open(M2ePro.url.get('m2epro_control_panel'));
                cmdPressedKeys = [];
            }
        });
    }

    // ---------------------------------------
});