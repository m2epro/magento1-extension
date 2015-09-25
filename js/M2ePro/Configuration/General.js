var cmdKeys = [67, 77, 68];
var developmentKeys = [68, 69, 86];

var cmdPressedKeys = [];
var developmentPressedKeys = [];

document.observe('keyup', function(event) {

    if (cmdPressedKeys.length < cmdKeys.length) {
        if (cmdKeys[cmdPressedKeys.length] == event.keyCode) {
            cmdPressedKeys.push(event.keyCode);
        } else {
            cmdPressedKeys = [];
        }
    }
    if (developmentPressedKeys.length < developmentKeys.length) {
        if (developmentKeys[developmentPressedKeys.length] == event.keyCode) {
            developmentPressedKeys.push(event.keyCode);
        } else {
            developmentPressedKeys = [];
        }
    }

    if (cmdPressedKeys.length == cmdKeys.length ||
        developmentPressedKeys.length == developmentKeys.length) {

        var queryInput = $('query');
        if (queryInput !== null) {
            queryInput.value = '';
            queryInput.focus();
        } else {
            $('development_button_container').show();
        }

        $$('.development')[0].show();

        if (cmdPressedKeys.length == cmdKeys.length) {
            $$('.development')[0].simulate('click');
        }

        cmdPressedKeys = [];
        developmentPressedKeys = [];
    }
});