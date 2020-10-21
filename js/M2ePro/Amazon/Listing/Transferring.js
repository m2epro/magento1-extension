window.AmazonListingTransferring = Class.create(Common, {

    progressBarObj: null,
    listingId: null,

    // ---------------------------------------

    initialize: function(listingId)
    {
        this.listingId = listingId;
    },

    //----------------------------------------

    getSourceAccount: function()
    {
        return $('from_account_id').value;
    },

    getTargetAccount: function()
    {
        return $('to_account_id').value;
    },

    getSourceMarketplace: function()
    {
        return $('from_marketplace_id').value;
    },

    getSourceListing: function()
    {
        return $('from_listing_id').value;
    },

    //----------------------------------------

    getTargetMarketplace: function()
    {
        return $('to_marketplace_id').value;
    },

    getTargetStore: function()
    {
        return $('to_store_id').value;
    },

    getTargetListing: function()
    {
        return $('to_listing_id').value;
    },

    //----------------------------------------

    popupShow: function(selectedProductsIds)
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_amazon_listing_transferring/index', {step: 1}), {
            method: 'post',
            asynchronous: true,
            parameters: {
                products_ids: selectedProductsIds.join(',')
            },
            onSuccess: function(transport) {

                Dialog.info(null, config = {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Sell on Another Marketplace'),
                    top: 50,
                    minWidth: 1000,
                    maxHeight: 500,
                    width: 1000,
                    zIndex: 100,
                    recenterAuto: true,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });

                $('modal_dialog_message').innerHTML = transport.responseText;
                $('modal_dialog_message').innerHTML.evalScripts();

                this.autoHeightFix();

            }.bind(this)
        });
    },

    popupContinue: function()
    {
        if (!Validation.validate($('to_account_id')) ||
            !Validation.validate($('to_marketplace_id')) ||
            !Validation.validate($('to_store_id')) ||
            !Validation.validate($('to_listing_id'))
        ) {
            return;
        }

        setLocation(
            M2ePro.url.get(
                'adminhtml_amazon_listing_transferring/index',
                {
                    step           : 2,
                    account_id     : this.getTargetAccount(),
                    marketplace_id : this.getTargetMarketplace(),
                    store_id       : this.getTargetStore(),
                    to_listing_id  : this.getTargetListing()
                }
            )
        );
    },

    // ---------------------------------------

    accountIdChange: function()
    {
        this.refreshMarketplaces();
    },

    storeIdChange: function()
    {
        this.refreshListings();
    },

    //----------------------------------------

    refreshMarketplaces: function()
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_amazon_listing_transferring/getMarketplace'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                account_id: this.getTargetAccount()
            },
            onSuccess: function(transport) {

                var marketplace = transport.responseText.evalJSON();

                $('to_marketplace_id').value = marketplace.id;
                $('to_marketplace_title').innerText = marketplace.title;

                this.refreshListings();
            }.bind(this)
        });
    },

    refreshListings: function()
    {
        if (!this.getTargetAccount() || !this.getTargetMarketplace() || !this.getTargetStore()) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_amazon_listing_transferring/getListings'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                account_id     : this.getTargetAccount(),
                marketplace_id : this.getTargetMarketplace(),
                store_id       : this.getTargetStore(),
                listing_id     : this.getSourceListing()
            },
            onSuccess: function(transport) {

                $('to_listing_id').update();

                var listings = transport.responseText.evalJSON(),
                    listingsSelector = $('to_listing_id');

                listingsSelector.appendChild(new Element('option', {value: '', class: 'empty', selected: true}));
                listings.each(function(listing) {
                    listingsSelector.appendChild(new Element('option', {value: listing.id}))
                                    .update(listing.title);
                });
                listingsSelector.appendChild(new Element('option', {value: 'create-new', style: 'color: brown;'}))
                                .update(M2ePro.translator.translate('Create new'));
            }.bind(this)
        });
    },

    //----------------------------------------

    addProducts: function(progressBatId, products, callback)
    {
        var parts = this.makeProductsParts(products, 100);

        this.progressBarObj = new ProgressBar(progressBatId);

        this.progressBarObj.reset();
        this.progressBarObj.setTitle(M2ePro.translator.translate('Sell on Another Marketplace'));
        this.progressBarObj.setStatus(M2ePro.translator.translate('Adding Products in process. Please wait...'));
        this.progressBarObj.show();

        this.sendPartsProducts(parts, parts.length, callback);
    },

    makeProductsParts: function(products, partSize)
    {
        var productsArray = products;
        var parts = new Array();

        if (productsArray.length < partSize) {
            parts[0] = productsArray;
            return parts;
        }

        var result = new Array();
        for (var i = 0; i < productsArray.length; i++) {
            if (result.length == 0 || result[result.length-1].length == partSize) {
                result[result.length] = new Array();
            }
            result[result.length-1][result[result.length-1].length] = productsArray[i];
        }

        return result;
    },

    sendPartsProducts: function(parts, partsCount, callback)
    {
        if (parts.length == 0) {
            return;
        }

        var isLastPart  = parts.length === 1 ? 1 : 0;
        var part = parts.splice(0, 1)[0];

        new Ajax.Request(M2ePro.url.get('adminhtml_amazon_listing_transferring/addProducts'), {
            method: 'post',
            parameters: {
                listing_id   : this.listingId,
                products     : implode(',', part),
                is_last_part : isLastPart
            },
            onSuccess: function(transport) {

                var percents = ((100 - this.progressBarObj.getPercents()) / parts.length) + this.progressBarObj.getPercents();
                if (percents >= 100) {
                    this.progressBarObj.setPercents(100, 0);
                    this.progressBarObj.setStatus('Adding has been completed');
                    callback();
                    return;
                } else {
                    this.progressBarObj.setPercents(percents, 1);
                }

                setTimeout(function() {
                    this.sendPartsProducts(parts, partsCount);
                }, 500);
            }.bind(this)
        });
    }

    //---------------------------------------
});
