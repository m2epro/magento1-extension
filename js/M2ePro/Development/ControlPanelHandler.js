DevelopmentControlPanelHandler = Class.create();
DevelopmentControlPanelHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    initialize: function()
    {
        var cmdKeys = [67, 77, 68];
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

                var queryInput = $('query');

                if (queryInput !== null) {
                    queryInput.value = '';
                    queryInput.focus();
                } else {
                    $('development_button_container').show();
                }

                $$('.development')[0].show();
                $$('.development')[0].simulate('click');

                cmdPressedKeys = [];
            }
        });
    }

    // ---------------------------------------
});