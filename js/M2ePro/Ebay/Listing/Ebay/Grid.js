window.EbayListingEbayGrid = Class.create(EbayListingViewGrid, {

    // ---------------------------------------

    afterInitPage: function($super)
    {
        $super();

        $(this.gridId+'_massaction-select').observe('change', function() {
            if (!$('get-estimated-fee')) {
                return;
            }

            if (this.value == 'list') {
                $('get-estimated-fee').show();
            } else {
                $('get-estimated-fee').hide();
            }
        });
    },

    // ---------------------------------------

    getMaxProductsInPart: function()
    {
        return 10;
    },

    // ---------------------------------------

    getLogViewUrl: function(rowId)
    {
        return M2ePro.url.get('adminhtml_ebay_log/listingProduct', {
            listing_product_id: rowId
        });
    },

    // ---------------------------------------

    openFeePopUp: function(content)
    {
        Dialog.info(content, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Estimated Fee Details'),
            width: 400,
            zIndex: 100,
            recenterAuto: true
        });

        Windows.getFocusedWindow().content.style.height = '';
        Windows.getFocusedWindow().content.style.maxHeight = '550px';
    },

    getEstimatedFees: function(listingProductId)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing/getEstimatedFees'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                listing_product_id: listingProductId
            },
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();

                if (response.error) {
                    alert('Unable to receive estimated fee.');
                    return;
                }

                self.openFeePopUp(response.html);
            }
        });
    },

    // ---------------------------------------

    openItemDuplicatePopUp: function(listingProductId)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing/getItemDuplicatePopUp'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                listing_product_id: listingProductId
            },
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();

                if (response.error) {
                    alert(response.error);
                    return;
                }

                Dialog.info(response.html, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Ebay Item Duplicate'),
                    width: 500,
                    maxHeight: 500,
                    zIndex: 100,
                    recenterAuto: true
                });

                setTimeout(function() {
                    Windows.getFocusedWindow().content.style.height = '';
                    Windows.getFocusedWindow().content.style.maxHeight = '500px';
                }, 50);
            }
        });
    },

    closeItemDuplicatePopUp: function(reloadGrid)
    {
        reloadGrid = reloadGrid || false;
        reloadGrid && this.getGridObj().reload();

        Windows.getFocusedWindow().close();
    },

    solveItemDuplicateAction: function(listingProductId, isNeedStop, isNeedList)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing/solveEbayItemDuplicate'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                listing_product_id:   listingProductId,
                stop_duplicated_item: Number(isNeedStop),
                list_current_item:    Number(isNeedList)
            },
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON(),
                    messagesBlock = $('ebay_listing_view_ebay_item_duplicate_messages_block');

                messagesBlock.hide();
                messagesBlock.down('.error_message').update();

                if (response.message) {
                    messagesBlock.show();
                    messagesBlock.down('.error_message').update(response.message);
                }

                if (response.result) {
                    self.closeItemDuplicatePopUp(true);
                }
            }
        });
    }

    // ---------------------------------------
});