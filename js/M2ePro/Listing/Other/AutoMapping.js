window.ListingOtherAutoMapping = Class.create(Action, {

    // ---------------------------------------

    run: function()
    {
        this.mapProductsAuto(
            this.gridHandler.getSelectedProductsString()
        );
    },

    // ---------------------------------------

    mapProductsAuto: function(product_ids)
    {
        var self = this;

        var selectedProductsString = product_ids;
        var selectedProductsArray = selectedProductsString.split(",");

        if (selectedProductsString == '' || selectedProductsArray.length == 0) {
            return;
        }

        var maxProductsInPart = 10;

        var result = new Array();
        for (var i=0;i<selectedProductsArray.length;i++) {
            if (result.length == 0 || result[result.length-1].length == maxProductsInPart) {
                result[result.length] = new Array();
            }
            result[result.length-1][result[result.length-1].length] = selectedProductsArray[i];
        }

        var selectedProductsParts = result;

        ListingProgressBarObj.reset();
        ListingProgressBarObj.show(M2ePro.text.automap_progress_title);
        GridWrapperObj.lock();
        $('loading-mask').setStyle({visibility: 'hidden'});

        self.sendPartsOfProducts(selectedProductsParts,selectedProductsParts.length,0);
    },

    sendPartsOfProducts: function(parts,totalPartsCount,isFailed)
    {
        var self = this;

        if (parts.length == 0) {
            MessageObj.clearAll();

            if (isFailed == 1) {
                MessageObj.addError(M2ePro.text.failed_mapped);
            } else {
                MessageObj.addSuccess(M2ePro.translator.translate('Product was Linked.'));
            }

            ListingProgressBarObj.setStatus(M2ePro.translator.translate('Task completed. Please wait ...'));
            ListingProgressBarObj.hide();
            ListingProgressBarObj.reset();
            GridWrapperObj.unlock();
            $('loading-mask').setStyle({visibility: 'visible'});

            self.gridHandler.unselectAllAndReload();

            return;
        }

        var part = parts.splice(0,1);
        part = part[0];
        var partString = implode(',',part);

        var partExecuteString = part.length;
        partExecuteString += '';

        ListingProgressBarObj.setStatus(str_replace('%product_title%', partExecuteString, M2ePro.text.processing_data_message));

        new Ajax.Request(M2ePro.url.mapAutoToProduct, {
            method: 'post',
            parameters: {
                componentMode: M2ePro.customData.componentMode,
                product_ids: partString
            },
            onSuccess: function(transport) {

                var percents = (100/totalPartsCount)*(totalPartsCount-parts.length);

                if (percents <= 0) {
                    ListingProgressBarObj.setPercents(0,0);
                } else if (percents >= 100) {
                    ListingProgressBarObj.setPercents(100,0);
                } else {
                    ListingProgressBarObj.setPercents(percents,1);
                }

                if (transport.responseText == 1) {
                    isFailed = 1;
                }

                setTimeout(function() {
                    self.sendPartsOfProducts(parts,totalPartsCount,isFailed);
                },500);
            }
        });
    }

    // ---------------------------------------
});
