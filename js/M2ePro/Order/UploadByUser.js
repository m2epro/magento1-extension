window.UploadByUser = Class.create(Common, {

    component: null,
    gridId: null,

    messageManager: null,

    //---------------------------------------

    initialize: function(component, gridId)
    {
        this.component = component;
        this.gridId = gridId;

        this.messageManager = new Message();
        this.messageManager.setContainer('uploadByUser_messages');
    },

    //---------------------------------------

    openPopup: function()
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_order_uploadByUser/getPopupHtml'), {
            method: 'post',
            parameters: {
                component: this.component
            },
            onSuccess: function(transport) {

                var popup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Order Reimport'),
                    top: 50,
                    width: 950,
                    height: 320,
                    zIndex: 100,
                    border: false,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });
                popup.options.destroyOnClose = true;

                $('modal_dialog_message').update(transport.responseText);
                this.autoHeightFix();

            }.bind(this)
        });
    },

    closePopup: function()
    {
        Windows.getFocusedWindow().close();
    },

    //----------------------------------------

    reloadGrid: function()
    {
        window[this.gridId + 'JsObject'].reload();
    },

    //----------------------------------------

    resetUpload: function(accountId)
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_order_uploadByUser/reset'), {
            method: 'post',
            parameters: {
                component  : this.component,
                account_id : accountId
            },
            onSuccess: function(transport) {
                var json = this.processJsonResponse(transport.responseText);
                if (json === false) {
                    return;
                }

                this.reloadGrid();

                if (json.result) {
                    this.messageManager.addSuccess(M2ePro.translator.translate('Order importing is canceled.'));
                }
            }.bind(this)
        });
    },

    configureUpload: function(accountId)
    {
        var fromDate = $(accountId + '_from_date'),
            toDate   = $(accountId + '_to_date');

        if (!Validation.validate(fromDate) || !Validation.validate(toDate)) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_order_uploadByUser/configure'), {
            method: 'post',
            parameters: {
                component  : this.component,
                account_id : accountId,
                from_date  : fromDate.value,
                to_date    : toDate.value
            },
            onSuccess: function(transport) {
                var json = this.processJsonResponse(transport.responseText);
                if (json === false) {
                    return;
                }

                this.reloadGrid();

                if (json.result) {
                    this.messageManager.addSuccess(M2ePro.translator.translate('Order importing in progress.'));
                }
            }.bind(this)
        });
    },

    // ---------------------------------------

    processJsonResponse: function(responseText)
    {
        if (!responseText.isJSON()) {
            alert(responseText);
            return false;
        }

        var response = responseText.evalJSON();
        if (typeof response.result === 'undefined') {
            alert('Invalid response.');
            return false;
        }

        this.messageManager.clearAll();
        if (typeof response.messages !== 'undefined') {
            response.messages.each(function(msg) {
                this.messageManager['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
            }.bind(this));
        }

        return response;
    }

    // ---------------------------------------
});