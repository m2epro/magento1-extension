window.ControlPanelInspection = Class.create(Common, {

    // ---------------------------------------

    showMetaData: function(element)
    {
        var content = '<div style="padding: 10px 10px; max-height: 450px; overflow: auto;">' +
            element.next().innerHTML +
            '</div>' +
            '<div style="text-align: right; padding-right: 10px; margin-top: 10px; margin-bottom: 5px;">' +
            '<button onclick="Windows.focusedWindow.close()">Close</button>' +
            '</div>';

        Dialog._openDialog(content, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: 'Details',
            width: 900,
            top: 100,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
    }

    // ---------------------------------------
});