LogHandler = Class.create();
LogHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

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

    //----------------------------------
});