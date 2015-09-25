EbayListingTransferringTranslateHandler = Class.create(CommonHandler, {

    //----------------------------------

    initialize: function()
    {
        this.actionHandler = new EbayListingTransferringActionHandler();
    },

    // --------------------------------

    loadActionHtml: function(selectedProductsIds, confirmCallback, closeCallback)
    {
        this.confirmCallback = confirmCallback;
        this.closeCallback   = closeCallback;

        this.actionHandler.setProductsIds(selectedProductsIds);
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing/getTranslationHtml'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                products_ids: [this.actionHandler.getProductsIds()]
            },
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();
                if (response['result'] != 'success') {
                    response['message'] && MagentoMessageObj.addError(response['message']);
                    return;
                }

                var content = response['content'];
                var title = M2ePro.translator.translate('Translation Service');

                this.openPopUp(title, content);

            }.bind(this)
        });
    },

    //----------------------------------

    openPopUp: function(title, content)
    {
        var config = {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 50,
            minWidth: 820,
            maxHeight: 500,
            width: 820,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() {

                EbayListingTransferringTranslateHandlerObj.actionHandler.clear();
                EbayListingTransferringTranslateHandlerObj.closeCallback &&
                    EbayListingTransferringTranslateHandlerObj.closeCallback();

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

    //----------------------------------

    translationServiceChange: function(el)
    {
        var estimatedAmountElement = $('translation_estimated_amount');

        if (estimatedAmountElement) {
            estimatedAmount = this.actionHandler.getEstimatedAmount(el);
            if (estimatedAmount) {
                $('translation_estimated_row') && $('translation_estimated_row').show();
            } else {
                $('translation_estimated_row') && $('translation_estimated_row').hide();
            }
            estimatedAmountElement.innerHTML = estimatedAmount;
        }

        if (this.actionHandler.getCurTranslationType(el) === 'silver' && this.actionHandler.getTotalCredits() > 0) {
            $('translation_total_credit_row') && $('translation_total_credit_row').show();
        } else {
            $('translation_total_credit_row') && $('translation_total_credit_row').hide();
        }

        if (this.actionHandler.isShowPaymentWarningMessage(el)) {
            $('translation_enough_money_tip')     && $('translation_enough_money_tip').setStyle({display: 'none'});
            $('translation_not_enough_money_tip') && $('translation_not_enough_money_tip').setStyle({display: 'inline-block'});
            estimatedAmountElement                && estimatedAmountElement.setStyle({color: '#df280a'});
            $('translation_estimated_currency')   && $('translation_estimated_currency').setStyle({color: '#df280a'});
        } else {
            $('translation_not_enough_money_tip') && $('translation_not_enough_money_tip').setStyle({display: 'none'});
            $('translation_enough_money_tip')     && $('translation_enough_money_tip').setStyle({display: 'inline-block'});
            estimatedAmountElement                && estimatedAmountElement.setStyle({color: '#333'});
            $('translation_estimated_currency')   && $('translation_estimated_currency').setStyle({color: '#333'});
        }
    },

    showPayNowPopup: function()
    {
        if (!$('transferring_translation_service')) {
            return;
        }

        var remainingAmount = this.actionHandler.getRemainingAmount($('transferring_translation_service'));
        if (remainingAmount <= 0) {
            remainingAmount = '100.00';
        }

        var currency        = $('translation_account_currency') && $('translation_account_currency').innerHTML || 'USD';
        var account         = this.actionHandler.getTargetAccount() || '';

        EbayListingTransferringPaymentHandlerObj.payNowAction(remainingAmount, currency, account);
    },

    refreshTranslationAccount: function()
    {
        this.actionHandler.refreshTranslationAccount(function() {
            $('transferring_translation_service') &&
                EbayListingTransferringTranslateHandlerObj.translationServiceChange($('transferring_translation_service'));
        });

    },

    //----------------------------------

    confirm: function()
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_transferring/updateTranslationService'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                products_ids: [EbayListingTransferringTranslateHandlerObj.actionHandler.getProductsIds()],
                translation_service: $('transferring_translation_service') &&
                                            $('transferring_translation_service').value
            },
            onSuccess: function(transport) {

                if (transport.responseText.evalJSON()['result'] == 'success') {
                    EbayListingTransferringTranslateHandlerObj.confirmCallback &&
                        EbayListingTransferringTranslateHandlerObj.confirmCallback();
                }

                EbayListingTransferringTranslateHandlerObj.close();

            }.bind(this)
        });
    },

    //----------------------------------

    close: function() {
        this.popUp.close();
    }

    //----------------------------------
});