window.ListingAction = Class.create(Action, {

    // ---------------------------------------

    sendPartsResponses: [],

    // ---------------------------------------

    startActions: function(title,url,selectedProductsParts,requestParams)
    {
        MessageObj.clearAll();
        $('listing_container_errors_summary').hide();

        var self = this;

        ListingProgressBarObj.reset();
        ListingProgressBarObj.show(title);
        GridWrapperObj.lock();
        $('loading-mask').setStyle({visibility: 'hidden'});

        self.sendPartsOfProducts(selectedProductsParts,selectedProductsParts.length,url,requestParams);
    },

    sendPartsOfProducts: function(parts,totalPartsCount,url,requestParams)
    {
        var self = this;

        if (parts.length == totalPartsCount) {
            self.sendPartsResponses = new Array();
        }

        if (parts.length == 0) {

            ListingProgressBarObj.setPercents(100,0);
            ListingProgressBarObj.setStatus(M2ePro.translator.translate('Task completed. Please wait ...'));

            var combineResult = 'success';
            var actionIds = [];

            for (var i = 0; i< self.sendPartsResponses.length; i++) {

                if (self.sendPartsResponses[i].result !== 'success' &&
                    self.sendPartsResponses[i].result !== 'warning')
                {
                    combineResult = 'error';
                }
                if (self.sendPartsResponses[i].result === 'warning') {
                    combineResult = 'warning';
                }

                if (typeof self.sendPartsResponses[i].action_id !== 'undefined') {
                    actionIds.push(self.sendPartsResponses[i].action_id);
                }
            }

            for (var i = 0; i< self.sendPartsResponses.length; i++) {

                if (typeof self.sendPartsResponses[i].is_processing_items !== 'undefined' &&
                    self.sendPartsResponses[i].is_processing_items === true)
                {
                    MessageObj.addNotice(M2ePro.text.locked_obj_notice);
                    break;
                }
            }

            if (combineResult === 'error') {

                var message = M2ePro.translator.translate('"%task_title%" Task was completed with errors.');
                message = message.replace('%task_title%', ListingProgressBarObj.getTitle());
                message = message.replace('%url%', M2ePro.url.logViewUrl);

                MessageObj.addError(message);

                new Ajax.Request(M2ePro.url.getErrorsSummary + 'action_ids/' + actionIds.join(',') + '/' , {
                    method:'get',
                    onSuccess: function(transportSummary) {
                        if (transportSummary.responseText.isJSON()) {

                            var response = transportSummary.responseText.evalJSON(true);

                            $('listing_container_errors_summary').innerHTML = response.html;
                            $('listing_container_errors_summary').show();
                        }
                    }
                });

            } else if (combineResult === 'warning') {

                var message = M2ePro.translator.translate('"%task_title%" Task was completed with warnings.');
                message = message.replace('%task_title%', ListingProgressBarObj.getTitle());
                message = message.replace('%url%', M2ePro.url.logViewUrl);

                MessageObj.addWarning(message);
            } else {

                if (requestParams['is_realtime']) {
                    var message = M2ePro.translator.translate('"%task_title%" Task was completed.');
                } else {
                    var message = M2ePro.translator.translate('"%task_title%" Task was submitted to be processed.');
                }
                message = message.replace('%task_title%', ListingProgressBarObj.getTitle());

                MessageObj.addSuccess(message);
            }

            ListingProgressBarObj.hide();
            ListingProgressBarObj.reset();
            GridWrapperObj.unlock();
            $('loading-mask').setStyle({visibility: 'visible'});

            self.sendPartsResponses = new Array();

            self.gridHandler.unselectAllAndReload();

            return;
        }

        var part = parts.splice(0,1);
        part = part[0];
        var partString = implode(',',part);

        var partExecuteString = '';

        if (part.length <= 2 && self.gridHandler.gridId != 'amazonVariationProductManageGrid') {

            for (var i=0;i<part.length;i++) {

                if (i != 0) {
                    partExecuteString += ', ';
                }

                var temp = self.gridHandler.getProductNameByRowId(part[i]);

                if (temp != '') {
                    if (temp.length > 75) {
                        temp = temp.substr(0, 75) + '...';
                    }
                    partExecuteString += '"' + temp + '"';
                } else {
                    partExecuteString = part.length;
                    break;
                }
            }

        } else {
            partExecuteString = part.length;
        }

        partExecuteString += '';

        ListingProgressBarObj.setStatus(str_replace('%product_title%', partExecuteString, M2ePro.text.sending_data_message));

        if (typeof requestParams == 'undefined') {
            requestParams = {}
        }

        requestParams['selected_products'] = partString;

        new Ajax.Request(url + 'id/' + self.gridHandler.listingId, {
            method: 'post',
            parameters: requestParams,
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {

                    if (transport.responseText != '') {
                        alert(transport.responseText);
                    }

                    ListingProgressBarObj.hide();
                    ListingProgressBarObj.reset();
                    GridWrapperObj.unlock();
                    $('loading-mask').setStyle({visibility: 'visible'});

                    self.sendPartsResponses = new Array();

                    self.gridHandler.unselectAllAndReload();

                    return;
                }

                var response = transport.responseText.evalJSON(true);

                if (response.error) {
                    ListingProgressBarObj.hide();
                    ListingProgressBarObj.reset();
                    GridWrapperObj.unlock();
                    $('loading-mask').setStyle({visibility: 'visible'});

                    self.sendPartsResponses = new Array();

                    alert(response.message);

                    return;
                }

                self.sendPartsResponses[self.sendPartsResponses.length] = response;

                var percents = (100/totalPartsCount)*(totalPartsCount-parts.length);

                if (percents <= 0) {
                    ListingProgressBarObj.setPercents(0,0);
                } else if (percents >= 100) {
                    ListingProgressBarObj.setPercents(100,0);
                } else {
                    ListingProgressBarObj.setPercents(percents,1);
                }

                setTimeout(function() {
                    self.sendPartsOfProducts(parts,totalPartsCount,url,requestParams);
                },500);
            }
        });

        return;
    },

    // ---------------------------------------

    listAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(
            M2ePro.text.listing_selected_items_message,
            M2ePro.url.runListProducts,
            selectedProductsParts
        );
    },

    relistAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(
            M2ePro.text.relisting_selected_items_message,
            M2ePro.url.runRelistProducts,
            selectedProductsParts
        );
    },

    reviseAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(
            M2ePro.text.revising_selected_items_message,
            M2ePro.url.runReviseProducts,
            selectedProductsParts
        );
    },

    stopAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(
            M2ePro.text.stopping_selected_items_message,
            M2ePro.url.runStopProducts,
            selectedProductsParts
        );
    },

    stopAndRemoveAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(
            M2ePro.text.stopping_and_removing_selected_items_message,
            M2ePro.url.runStopAndRemoveProducts,
            selectedProductsParts
        );
    },

    previewItemsAction: function()
    {
        var orderedSelectedProductsArray = this.gridHandler.getOrderedSelectedProductsArray();
        if (orderedSelectedProductsArray.length == 0) {
            return;
        }

        this.openWindow(
            M2ePro.url.previewItems + 'productIds/' + implode(',', orderedSelectedProductsArray)
                                    + '/currentProductId/' + orderedSelectedProductsArray[0]
        );
    }

    // ---------------------------------------
});
