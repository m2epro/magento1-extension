window.ControlPanelInspection = Class.create(Common, {

    // ---------------------------------------

    showMetaData: function(element) {
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
    },

    removeRow: function(element) {
        var form = element.up('form'),
            url = form.getAttribute('action'),
            data = Form.serialize(form);

        form.querySelectorAll("input:checked").forEach(function(element) {
            element.up('tr').remove();
        });

        new Ajax.Request(url, {
            method: 'post',
            asynchronous: true,
            parameters: data
        });
    },

    checkAction: function() {
        var tr = event.target.closest("tr"),
            id = tr.querySelector(".id").textContent,
            details = tr.querySelector(".details");

        new Ajax.Request(M2ePro.url.get('checkInspection'), {
            method: 'post',
            parameters: {
                name: id.trim()
            },
            asynchronous: true,
            onSuccess: function(transport) {
                if (!transport.responseText.isJSON()) {
                    details.innerHTML = "<span style='color: red; font-weight: bold;'>Internal Error occured" +
                        " check system log</span>";
                }

                var response = transport.responseText.evalJSON();

                if (response) {
                    if (response['result'] === true) {
                        details.innerHTML = "<span style='color: green; font-weight: bold;'>" + response['message'] + "</span>";
                    } else {
                        details.innerHTML = "<span style='color: red; font-weight: bold;'>" + response['message'] + "</span>";
                        details.innerHTML += '&nbsp;&nbsp;\n' +
                            '<a href="javascript://"' +
                            ' onclick="ControlPanelInspectionObj.showMetaData(this);">details</a>\n' +
                            '<div class="no-display"><div>' + response['metadata'] + '</div></div>';
                    }
                }
            }
        });
    }
    // ---------------------------------------
});
