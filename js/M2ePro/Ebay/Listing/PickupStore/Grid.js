window.EbayListingPickupStoreGrid = Class.create(ListingGrid, {

    // ---------------------------------------

    pickupStoreStepProducts: function(listingId, callback)
    {
        var self = this;
        this.getGridMassActionObj().unselectAll();

        new Ajax.Request(M2ePro.url.get('*/productsStep'), {
            method: 'post',
            parameters: {
                id: listingId
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                self.openPopUp('Assign Products to Stores', response.data);

                callback && callback();
                MessageObj.clearAll();
                response.messages.each(function(msg) {
                    MessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                });

                $('cancel_button').observe('click', function() { Windows.getFocusedWindow().close(); });

                $('done_button').observe('click', function() {
                    var checkedIds = EbayListingPickupStoreStepProductsGridObj.getCheckedValues();
                    if (checkedIds == '') {
                        alert(M2ePro.translator.translate('Please select Items.'));
                        return;
                    }

                    self.pickupStoreStepStores(listingId);
                });

                setTimeout(function() {
                    Windows.getFocusedWindow().content.style.height = '';
                    Windows.getFocusedWindow().content.style.maxHeight = '600px';
                }, 50);
            }
        });
    },

    pickupStoreStepStores: function(listingId)
    {
        var self = this;
        new Ajax.Request(M2ePro.url.get('*/storesStep'), {
            method: 'post',
            parameters: {
                id: listingId
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                self.openPopUp('Assign Products to Stores', response.data);

                MessageObj.clearAll();
                response.messages.each(function(msg) {
                    MessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                });

                $('back_button').observe('click', function(e) {
                    e.preventDefault();
                    var checked = EbayListingPickupStoreStepProductsGridObj.getCheckedValues();
                    self.pickupStoreStepProducts(listingId, function() {
                        var gridMassAcction = EbayListingPickupStoreStepProductsGridObj.getGridMassActionObj();

                        gridMassAcction.setCheckedValues(checked);
                        gridMassAcction.checkCheckboxes();
                    });
                });

                $('save_button').observe('click', function(e) {
                    e.preventDefault();
                    var productsIds = EbayListingPickupStoreStepProductsGridObj.getCheckedValues(),
                        storesIds = EbayListingPickupStoreStepStoresGridObj.getCheckedValues();

                    if (productsIds == '' || storesIds == '') {
                        alert('Please select Stores.');
                        return;
                    }

                    self.completeStep(productsIds, storesIds);
                });

                setTimeout(function() {
                    Windows.getFocusedWindow().content.style.height = '';
                    Windows.getFocusedWindow().content.style.maxHeight = '600px';
                }, 50);
            }
        });
    },

    completeStep: function (productsIds, storesIds)
    {
        var self = this;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('*/assign'), {
            method: 'post',
            parameters: {
                products_ids: productsIds,
                stores_ids: storesIds
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                self.getGridMassActionObj().unselectAll();
                self.getGridObj().reload(M2ePro.url.get('*/pickupStoreGrid'));

                var response = transport.responseText.evalJSON();

                MessageObj.clearAll();
                response.messages.each(function(msg) {
                    MessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                });
            }
        });

        Windows.getFocusedWindow().close();
    },

    // ---------------------------------------

    confirm: function()
    {
        return true;
    },

    // ---------------------------------------

    openPopUp: function(title, content, params)
    {
        var self = this;
        params = params || {};

        var config = Object.extend({
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            top: 40,
            height: 600,
            width: 920,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() {
                self.selectedProductsIds = [];
                $('excludeListPopup') && Windows.getWindow('excludeListPopup').destroy();
                return true;
            }
        }, params);

        try {
            if (!Windows.getFocusedWindow() || !$('modal_dialog_message')) {
                Dialog.info(null, config);
            }
            Windows.getFocusedWindow().setTitle(M2ePro.translator.translate(title));

            var wrapper = new Element('div', {
                style: 'overflow-y: auto; max-height: 520px;',
                id: 'pickupStore-popup-wrapper'
            });
            wrapper.innerHTML = content;

            var buttonArea = wrapper.down('#button-template'),
                modal = $('modal_dialog_message');

            modal.innerHTML = '';
            modal.insert(wrapper);
            if (buttonArea) {
                modal.insert({bottom: buttonArea.innerHTML});
            }
            modal.innerHTML.evalScripts();
        } catch (ignored) {}
    },

    // ---------------------------------------

    openVariationPopUp: function(productId, title, pickupStoreId, filter)
    {
        var self = this;

        MessageObj.clearAll();
        M2ePro.customData.variationPopup = {
            product_id: productId,
            title: title,
            pickup_store_id: pickupStoreId,
            filter: filter
        };

        new Ajax.Request(M2ePro.url.get('variationProduct'), {
            method: 'post',
            parameters: {
                product_id: productId,
                pickup_store_id: pickupStoreId,
                filter: filter
            },
            onSuccess: function (transport) {

                variationProductManagePopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: title.escapeHTML(),
                    top: 40,
                    height: 500,
                    width: 800,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });
                variationProductManagePopup.options.destroyOnClose = true;

                variationProductManagePopup.productId = productId;

                $('modal_dialog_message').update(transport.responseText);
            }
        });
    },

    closeVariationPopUp: function()
    {
        variationProductManagePopup.close();
    },

    loadVariationsGrid: function(showMask)
    {
        var self = this;
        showMask && $('loading-mask').show();

        var gridIframe = $('ebayPickupStoreVariationsGridIframe');

        if(gridIframe) {
            gridIframe.remove();
        }

        var iframe = new Element('iframe', {
            id: 'ebayPickupStoreVariationsGridIframe',
            src: $('ebayPickupStoreVariationsGridIframeUrl').value,
            width: '100%',
            height: '100%',
            style: 'border: none;'
        });

        $('ebayPickupStoreVariationsGrid').insert(iframe);

        Event.observe($('ebayPickupStoreVariationsGridIframe'), 'load', function() {
            $('loading-mask').hide();
        });
    },

    // ---------------------------------------

    viewItemHelp: function(rowId, data, hideViewLog)
    {
        $('grid_help_icon_open_'+rowId).hide();
        $('grid_help_icon_close_'+rowId).show();

        if ($('grid_help_content_'+rowId) != null) {
            $('grid_help_content_'+rowId).show();
            return;
        }

        var html = this.createHelpTitleHtml(rowId);

        var synchNote = $('synch_template_list_rules_note_'+rowId);
        if (synchNote) {
            html += this.createSynchNoteHtml(synchNote.innerHTML)
        }

        data = eval(base64_decode(data));
        for (var i=0;i<data.length;i++) {
            html += this.createHelpActionHtml(data[i]);
        }

        if (!hideViewLog) {
            html += this.createHelpViewAllLogHtml(rowId);
        }

        var rows = this.getGridObj().rows;
        for(var i=0;i<rows.length;i++) {
            var row = rows[i];
            var cels = $(row).childElements();

            var checkbox = $(cels[0]).childElements();
            checkbox = checkbox[0];

            if (checkbox.value == rowId) {
                row.insert({
                    after: '<tr id="grid_help_content_'+rowId+'"><td class="help_line" colspan="'+($(row).childElements().length)+'">'+html+'</td></tr>'
                });
            } else {
                var lastCell = cels[cels.length-1],
                    hiddenElement = $(lastCell).down('#product_row_order_'+rowId);

                if (hiddenElement && hiddenElement.value == rowId) {
                    row.insert({
                        after: '<tr id="grid_help_content_'+rowId+'"><td class="help_line" colspan="'+($(row).childElements().length)+'">'+html+'</td></tr>'
                    });
                }
            }
        }
        var self = this;
        $('hide_item_help_' + rowId).observe('click', function() {
            self.hideItemHelp(rowId);
        });
    },

    createHelpTitleHtml: function(rowId)
    {
        var closeHtml = '<a href="javascript:void(0);" id="hide_item_help_' + rowId + '" title="'+M2ePro.translator.translate('Close')+'"><span class="hl_close">&times;</span></a>';
        return '<div class="hl_header"><span class="hl_title">&nbsp;</span>'+closeHtml+'</div>';
    },

    createHelpViewAllLogHtml: function(rowId)
    {
        var id = $('product_row_order_'+rowId).getAttribute('listing-product-pickup-store-state');
        return '<div class="hl_footer">' +
               '<a href="#" onclick="EbayListingPickupStoreGridObj.getLogGrid('+id+');">'+
                    M2ePro.translator.translate('View All Product Log')+
               '</a></div>';
    },

    // ---------------------------------------

    getLogGrid: function(rowId, onPopupCloseCallback)
    {
        var self = EbayListingPickupStoreGridObj;

        var isFrame = window.top.document.getElementById('ebayPickupStoreVariationsGridIframe');
        if (isFrame) {
            setTimeout(function() {
                var topObject = window.top.EbayListingPickupStoreGridObj;
                topObject.closeVariationPopUp();
                topObject.getLogGrid(rowId, function() {
                    if (!M2ePro.customData.variationPopup) {
                        return;
                    }

                    var params = M2ePro.customData.variationPopup;
                    EbayListingPickupStoreGridObj.openVariationPopUp(
                        params.product_id,
                        params.title,
                        params.pickup_store_id,
                        params.filter
                    );
                });
            }, 0);
            return;
        }

        new Ajax.Request(M2ePro.url.get('*/logGrid'), {
            method: 'post',
            parameters: {
                listing_product_pickup_store_state: rowId
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                self.openPopUp('Log For Sku', response.data, {
                    onClose: function() {
                        onPopupCloseCallback && eval('('+onPopupCloseCallback.toString()+')();')
                    }
                });

                MessageObj.clearAll();
                response.messages.each(function(msg) {
                    MessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                });

                setTimeout(function() {
                    Windows.getFocusedWindow().content.style.height = '';
                    Windows.getFocusedWindow().content.style.maxHeight = '600px';
                }, 50);
            }
        });
    }

    // ---------------------------------------
});
