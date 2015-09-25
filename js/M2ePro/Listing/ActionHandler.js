ListingActionHandler = Class.create(ActionHandler, {

    //----------------------------------

    sendPartsResponses: [],

    //----------------------------------

    options: {},

    setOptions: function(options)
    {
        this.options = Object.extend(this.options,options);
        return this;
    },

    //----------------------------------

    startActions: function(title,url,selectedProductsParts)
    {
        MagentoMessageObj.clearAll();
        $('listing_container_errors_summary').hide();

        var self = this;

        ListingProgressBarObj.reset();
        ListingProgressBarObj.show(title);
        GridWrapperObj.lock();
        $('loading-mask').setStyle({visibility: 'hidden'});

        self.sendPartsOfProducts(selectedProductsParts,selectedProductsParts.length,url);
    },

    sendPartsOfProducts: function(parts,totalPartsCount,url)
    {
        var self = this;

        if (parts.length == totalPartsCount) {
            self.sendPartsResponses = new Array();
        }

        if (parts.length == 0) {

            ListingProgressBarObj.setPercents(100,0);
            ListingProgressBarObj.setStatus(self.options.text.task_completed_message);

            var combineResult = 'success';
            for (var i=0;i<self.sendPartsResponses.length;i++) {
                if (self.sendPartsResponses[i].result != 'success' && self.sendPartsResponses[i].result != 'warning') {
                    combineResult = 'error';
                    break;
                }
                if (self.sendPartsResponses[i].result == 'warning') {
                    combineResult = 'warning';
                }
            }

            for (var i=0;i<self.sendPartsResponses.length;i++) {
                if (typeof self.sendPartsResponses[i].is_processing_items != 'undefined' &&
                    self.sendPartsResponses[i].is_processing_items == true) {
                    MagentoMessageObj.addNotice(self.options.text.locked_obj_notice);
                    break;
                }
            }

            if (combineResult == 'error') {

                var message = self.options.text.task_completed_error_message;
                message = message.replace('%task_title%', ListingProgressBarObj.getTitle());
                message = message.replace('%url%', self.options.url.logViewUrl);

                MagentoMessageObj.addError(message);

                var actionIds = '';
                for (var i=0;i<self.sendPartsResponses.length;i++) {
                    if (actionIds != '') {
                        actionIds += ',';
                    }
                    actionIds += self.sendPartsResponses[i].action_id;
                }

                new Ajax.Request(self.options.url.getErrorsSummary + 'action_ids/' + actionIds + '/' , {
                    method:'get',
                    onSuccess: function(transportSummary) {
                        $('listing_container_errors_summary').innerHTML = transportSummary.responseText;
                        $('listing_container_errors_summary').show();
                    }
                });

            } else if (combineResult == 'warning') {
                var message = self.options.text.task_completed_warning_message;
                message = message.replace('%task_title%', ListingProgressBarObj.getTitle());
                message = message.replace('%url%', self.options.url.logViewUrl);

                MagentoMessageObj.addWarning(message);
            } else {
                var message = self.options.text.task_completed_success_message;
                message = message.replace('%task_title%', ListingProgressBarObj.getTitle());

                MagentoMessageObj.addSuccess(message);
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

        ListingProgressBarObj.setStatus(str_replace('%product_title%', partExecuteString, self.options.text.sending_data_message));

        new Ajax.Request(url + 'id/' + self.gridHandler.listingId, {
            method: 'post',
            parameters: {
                selected_products: partString
            },
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
                    self.sendPartsOfProducts(parts,totalPartsCount,url);
                },500);
            }
        });

        return;
    },

    //----------------------------------

    listAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(
            this.options.text.listing_selected_items_message,
            this.options.url.runListProducts,selectedProductsParts
        );
    },

    relistAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(
            this.options.text.relisting_selected_items_message,
            this.options.url.runRelistProducts,selectedProductsParts
        );
    },

    reviseAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(
            this.options.text.revising_selected_items_message,
            this.options.url.runReviseProducts,selectedProductsParts
        );
    },

    stopAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(
            this.options.text.stopping_selected_items_message,
            this.options.url.runStopProducts,selectedProductsParts
        );
    },

    stopAndRemoveAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(
            this.options.text.stopping_and_removing_selected_items_message,
            this.options.url.runStopAndRemoveProducts,selectedProductsParts
        );
    },

    startTranslateAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts(100);
        if (selectedProductsParts.length == 0) {
            return;
        }

        var self = this;
        EbayListingTransferringTranslateHandlerObj.loadActionHtml(
            self.gridHandler.getSelectedProductsArray(),
            function() {
                self.startActions(
                    self.options.text.start_translate_selected_items_message,
                    self.options.url.runStartTranslateProducts,selectedProductsParts
                );
            }, function() {
                self.gridHandler.unselectAll();
        });
    },

    stopTranslateAction: function()
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        var selectedProductsParts = this.gridHandler.getSelectedItemsParts(100);
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(
            this.options.text.stop_translate_selected_items_message,
            this.options.url.runStopTranslateProducts,selectedProductsParts
        );
    }

    //----------------------------------
});