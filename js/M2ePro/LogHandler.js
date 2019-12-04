LogHandler = Class.create(GridHandler, {

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
        var content = '<div style="padding: 0 10px; max-height: 300px;">' +
                          element.next().innerHTML +
                      '</div>' +
                      '<div style="text-align: right; padding-right: 10px; margin-bottom: 5px;">' +
                          '<button onclick="Windows.focusedWindow.close()">' + M2ePro.translator.translate('Close') + '</button>' +
                      '</div>';

        var title = M2ePro.translator.translate('Description');

        Dialog._openDialog(content, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            width: 500,
            top: 200,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
    }

    // ---------------------------------------
});