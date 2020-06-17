window.Log = Class.create(Grid, {

    // ---------------------------------------

    prepareActions: function() {},

    // ---------------------------------------

    afterInitPage: function($super)
    {
        $super();
        this.randomBorderColor();
    },

    randomBorderColor: function ()
    {
        $$('.random_border_color').each(function(el) {
            $(el).style.borderLeftWidth = '2px';
            $(el).style.borderTopWidth = '0';
            $(el).style.borderLeftColor = 'hsla(' + (Math.random() * 360) + ', 100%, 65%, 1)';
        });
    },

    // ---------------------------------------

    showFullText: function(element)
    {
        var content = '<div style="padding: 0 10px; max-height: 450px; overflow: auto;">' +
                          element.next().innerHTML +
                      '</div>' +
                      '<div style="text-align: right; padding-right: 10px; margin-top: 10px; margin-bottom: 5px;">' +
                          '<button onclick="Windows.focusedWindow.close()">' + M2ePro.translator.translate('Close') + '</button>' +
                      '</div>';

        Dialog._openDialog(content, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Description'),
            width: 900,
            top: 100,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
    }

    // ---------------------------------------
});
