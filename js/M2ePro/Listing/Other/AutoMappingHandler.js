ListingOtherAutoMappingHandler = Class.create(ActionHandler, {

    //----------------------------------

    options: {},

    setOptions: function(options)
    {
        this.options = Object.extend(this.options,options);
        return this;
    },

    //----------------------------------

    run: function()
    {
        this.mapProductsAuto(
            this.gridHandler.getSelectedProductsString()
        );
    },

    //----------------------------------

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
        ListingProgressBarObj.show(self.options.text.automap_progress_title);
        GridWrapperObj.lock();
        $('loading-mask').setStyle({visibility: 'hidden'});

        self.sendPartsOfProducts(selectedProductsParts,selectedProductsParts.length,0);
    },

    sendPartsOfProducts: function(parts,totalPartsCount,isFailed)
    {
        var self = this;

        if (parts.length == 0) {
            MagentoMessageObj.clearAll();

            if (isFailed == 1) {
                MagentoMessageObj.addError(self.options.text.failed_mapped);
            } else {
                MagentoMessageObj.addSuccess(self.options.text.successfully_mapped);
            }

            ListingProgressBarObj.setStatus(self.options.text.task_completed_message);
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

        ListingProgressBarObj.setStatus(str_replace('%product_title%', partExecuteString, self.options.text.processing_data_message));

        new Ajax.Request(self.options.url.mapAutoToProduct, {
            method: 'post',
            parameters: {
                componentMode: self.options.customData.componentMode,
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

    //----------------------------------
});