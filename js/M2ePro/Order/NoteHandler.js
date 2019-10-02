OrderNoteHandler = Class.create(GridHandler, {

    // ---------------------------------------

    initialize: function(gridId)
    {
        this.gridId = gridId;
    },

    // ---------------------------------------

    openAddNotePopup: function(orderId)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_order/getNotePopupHtml'), {
            method: 'post',
            parameters: {
                order_id: orderId
            },
            onSuccess: function(transport) {
                self.openPopup(transport.responseText);
            }
        });
    },

    openEditNotePopup: function(noteId)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_order/getNotePopupHtml'), {
            method: 'post',
            parameters: {
                note_id: noteId
            },
            onSuccess: function(transport) {
                self.openPopup(transport.responseText);
            }
        });
    },

    openPopup: function(responseText)
    {
        var popup = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Custom Note'),
            width: 600,
            height: 320,
            zIndex: 100,
            border: false,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        popup.options.destroyOnClose = true;

        $('modal_dialog_message').update(responseText);
        OrderNoteHandlerObj.autoHeightFix();
    },

    // ---------------------------------------

    saveNote: function ()
    {
        if (!new varienForm('order_note_popup').validate()) {
            return false;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_order/saveNote'), {
            method: 'post',
            parameters: $('order_note_popup').serialize(true),

            onSuccess: function(transport) {

                var result = transport.responseText.evalJSON()['result'];
                if (!result) {

                    OrderNoteHandlerObj.scroll_page_to_top();
                    window.location.reload();
                    return;
                }

                Windows.getFocusedWindow().close();
                OrderNoteHandlerObj.getGridObj().reload();
            }
        });

    },

    deleteNote: function (noteId)
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_order/deleteNote'), {
            method: 'post',
            parameters: {
                note_id: noteId
            },
            onSuccess: function(transport) {

                var result = transport.responseText.evalJSON()['result'];
                if (!result) {

                    OrderNoteHandlerObj.scroll_page_to_top();
                    window.location.reload();
                    return;
                }

                OrderNoteHandlerObj.getGridObj().reload();
            }
        });
    }

    // ---------------------------------------
});