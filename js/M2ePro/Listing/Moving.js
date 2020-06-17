window.ListingMoving = Class.create(Action, {

    // ---------------------------------------

    accountId: null,
    marketplaceId: null,

    // ---------------------------------------

    run: function()
    {
        this.getGridHtml(
            this.gridHandler.getSelectedProductsArray()
        );
    },

    // ---------------------------------------

    openPopUp: function(gridHtml,popup_title)
    {
        this.popUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: popup_title,
            top: 100,
            width: 900,
            height: 500,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        $('modal_dialog_message').insert(gridHtml).style.paddingTop = '20px';
    },

    // ---------------------------------------

    getGridHtml: function(selectedProducts)
    {
        var self = this;

        self.selectedProducts = selectedProducts;
        self.gridHandler.unselectAll();
        MessageObj.clearAll();
        $('listing_container_errors_summary').hide();

        ListingProgressBarObj.reset();
        ListingProgressBarObj.setTitle('Preparing for Product Moving');
        ListingProgressBarObj.setStatus('Products are being prepared for Moving. Please wait…');
        ListingProgressBarObj.show();
        self.scroll_page_to_top();

        $('loading-mask').setStyle({visibility: 'hidden'});
        GridWrapperObj.lock();

        var productsByParts = self.makeProductsParts();
        self.prepareData(productsByParts, productsByParts.length, 1);
    },

    makeProductsParts: function()
    {
        var self = this;

        var productsInPart = 500;
        var parts = [];

        if (self.selectedProducts.length < productsInPart) {
            var part = [];
            part[0] = self.selectedProducts;
            return parts[0] = part;
        }

        var result = [];
        for (var i = 0; i < self.selectedProducts.length; i++) {
            if (result.length === 0 || result[result.length-1].length === productsInPart) {
                result[result.length] = [];
            }
            result[result.length-1][result[result.length-1].length] = self.selectedProducts[i];
        }

        return result;
    },

    prepareData: function(parts, partsCount, isFirstPart)
    {
        var self = this;

        if (parts.length === 0) {
            return;
        }

        var isLastPart  = parts.length === 1 ? 1 : 0;
        var part = parts.splice(0, 1);
        var currentPart = part[0];

        new Ajax.Request(M2ePro.url.prepareData, {
            method: 'post',
            parameters: {
                componentMode: M2ePro.customData.componentMode,
                is_first_part: isFirstPart,
                is_last_part : isLastPart,
                products_part: implode(',', currentPart)
            },
            onSuccess: function(transport) {

                var percents = (100 / partsCount) * (partsCount - parts.length);

                if (percents <= 0) {
                    ListingProgressBarObj.setPercents(0, 0);
                } else if (percents >= 100) {
                    ListingProgressBarObj.setPercents(100, 0);
                    ListingProgressBarObj.setStatus('Products are almost prepared for Moving...');
                } else {
                    ListingProgressBarObj.setPercents(percents, 1);
                }

                var response = transport.responseText.evalJSON();
                if (!response.result) {

                    self.completeProgressBar();
                    if (typeof response.message !== 'undefined') {
                        MessageObj.addError(response.message);
                    }
                    return;
                }

                if (isLastPart) {

                    self.accountId = response.accountId;
                    self.marketplaceId = response.marketplaceId;

                    self.moveToListingGrid();
                    return;
                }

                setTimeout(function() {
                    self.prepareData(parts, partsCount, 0);
                }, 500);
            }
        });
    },

    moveToListingGrid: function()
    {
        var self = this;

        new Ajax.Request(M2ePro.url.getGridHtml, {
            method: 'get',
            parameters: {
                componentMode : M2ePro.customData.componentMode,
                accountId     : self.accountId,
                marketplaceId : self.marketplaceId,
                ignoreListings: M2ePro.customData.ignoreListings
            },
            onSuccess: function(transport) {
                self.completeProgressBar();
                self.openPopUp(transport.responseText, M2ePro.text.popup_title);
            }
        });
    },

    submit: function(listingId, onSuccess)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.moveToListing, {
            method: 'post',
            parameters: {
                componentMode: M2ePro.customData.componentMode,
                listingId: listingId
            },
            onSuccess: function(transport) {

                self.popUp.close();
                self.scroll_page_to_top();

                var response = transport.responseText.evalJSON();

                if (response.result) {
                    onSuccess.bind(self.gridHandler)(listingId);
                    return;
                }

                self.gridHandler.unselectAllAndReload();
            }
        });
    },

    // ---------------------------------------

    startListingCreation: function(url, response) {
        var self = this;
        var win = window.open(url);

        var intervalId = setInterval(function() {
            if (!win.closed) {
                return;
            }

            clearInterval(intervalId);

            listingMovingGridJsObject.reload();
        }, 1000);
    },

    // ---------------------------------------

    completeProgressBar: function () {
        ListingProgressBarObj.hide();
        ListingProgressBarObj.reset();
        GridWrapperObj.unlock();
        $('loading-mask').setStyle({visibility: 'visible'});
    }

    // ---------------------------------------
});