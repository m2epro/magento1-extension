EbayListingTransferringPaymentHandler = Class.create(CommonHandler, {

    // ---------------------------------------

    initialize: function() {},

    // ---------------------------------------

    payNowAction: function(amount, currency, account, closeCallback)
    {
        var content =
            '<div style="padding-bottom: 20px; padding-top: 20px;">' +
                '<div id="block_notice_ebay_translation_account" class="block_notices_module" title="' + M2ePro.translator.translate('Payment for Translation Service. Help') + '" subtitle="" collapseable="no" hideblock="no" always_show="yes">' +
                    M2ePro.translator.translate('Specify a sum to be credited to an Account.') +
                '</div>' +
                '<form action="' + M2ePro.url.get('adminhtml_ebay_listing_transferring/getPaymentUrl') + '" method="post" id="edit_form">' +
                '<input id="transferring_ebay_account" type="hidden" name="account" value="' + account + '">' +
                '<input id="transferring_payment_currency" type="hidden" name="currency" value="' + currency + '">' +
                '<table class="form-list">' +
                    '<tr>' +
                        '<td class="label" style="width: 200px;">' +
                            '<label for="transferring_payment_amount">' + M2ePro.translator.translate('Amount to Pay.') + ', ' + currency + ':</label>' +
                        '</td>' +
                        '<td class="value">' +
                            '<input id="transferring_payment_amount" type="text" class="input-text required-entry validate-number validate-greater-than-zero" name="amount" value="' + amount + '">' +
                        '</td>' +
                        '<td class="value">' +
                            '<div style="display: inline-block">' +
                                '<img src="' + M2ePro.url.get('m2epro_skin_url') + '/images/tool-tip-icon.png' + '" class="tool-tip-image">' +
                                '<span class="tool-tip-message" style="display: none;">' +
                                    '<img src="' + M2ePro.url.get('m2epro_skin_url') + '/images/help.png' + '">' +
                                    '<span>' + M2ePro.translator.translate('Insert amount to be credited to an Account') + '</span>' +
                                '</span>' +
                            '</div>' +
                        '</td>' +
                    '</tr>' +
                '</table>' +
                '</form>' +
            '</div>' +
            '<div style="float: right; margin: 10px 0;">' +
                '<a href="javascript:void(0);" onclick="Windows.getFocusedWindow().close()">' + M2ePro.translator.translate('Cancel') + '</a>' +
                '&nbsp;&nbsp;&nbsp;' +
                '<button class="confirm_button" onclick="EbayListingTransferringPaymentHandlerObj.confirm();">'+M2ePro.translator.translate('Confirm')+'</button>' +
                '' +
            '</div>';
        var title = M2ePro.translator.translate('Payment for Translation Service');

        this.openPopUp(title, content, closeCallback);
        initializationMagentoBlocks();
    },

    // ---------------------------------------

    openPopUp: function(title, content, closeCallback)
    {
        var config = {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 50,
            minWidth: 570,
            maxHeight: 500,
            width: 570,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() {
                closeCallback && closeCallback();

                return true;
            }
        };

        try {
            this.popUp = Dialog.info(null, config);
            $('modal_dialog_message').innerHTML = content;
            $('modal_dialog_message').innerHTML.evalScripts();
        } catch (ignored) {}

        MagentoMessageObj.clearAll();
        setTimeout(function() {
            Windows.getFocusedWindow().content.setStyle
                .bind(Windows.getFocusedWindow().content, {height: '', maxHeight: '500px'})
                .defer();
        }, 50);
    },

    // ---------------------------------------

    validate: function()
    {
        var validationResult = [];

        if ($('edit_form')) {
            validationResult = Form.getElements('edit_form').collect(Validation.validate);
        }

        if (validationResult.indexOf(false) != -1) {
            return false;
        }

        return true;
    },

    // ---------------------------------------

    confirm: function()
    {
        if (!EbayListingTransferringPaymentHandlerObj.validate()) {
            return;
        }

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_transferring/getPaymentUrl'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                account_id: $('transferring_ebay_account').value,
                amount:     $('transferring_payment_amount').value,
                currency:  $('transferring_payment_currency').value
            },
            onSuccess: function(transport) {
                if (transport.responseText.evalJSON()['result'] != 'success') {
                    return;
                }

                var win = window.open(transport.responseText.evalJSON()['payment_url']);
                win.focus();

                var intervalId = setInterval(function() {
                    if (!win.closed) { return; }
                    clearInterval(intervalId);
                    if (typeof EbayListingTransferringHandlerObj != 'undefined') {
                        EbayListingTransferringHandlerObj.refreshTranslationAccount();
                    } else if (typeof EbayListingTransferringTranslateHandlerObj != 'undefined') {
                        EbayListingTransferringTranslateHandlerObj.refreshTranslationAccount();
                    }

                }, 1000);

                EbayListingTransferringPaymentHandlerObj.popUp.close();
            }
        });
    }

    // ---------------------------------------
});