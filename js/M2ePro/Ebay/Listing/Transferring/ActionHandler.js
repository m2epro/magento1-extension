EbayListingTransferringActionHandler = Class.create();
EbayListingTransferringActionHandler.prototype = {

    // ---------------------------------------

    callStepStack: [],

    source: {
        products_ids      : [],
        custom_settings   : null,
        account_id        : null,
        marketplace_id    : null,
        listing_title     : null
    },

    new_listing_id : null,
    templates  : {},

    productsInPart  : 100,
    successProducts : [],
    errorsCount     : 0,

    allFailedTranslationProducts : false,

    needToSetCatalogPolicy : false,

    loadedData : {
        listings     : {},
        translation  : {},
        policy       : {},
        marketplaces : {}
    },

    progressBarObj : null,
    wrapperObj     : null,

    // ---------------------------------------

    initialize: function() {},

    // ---------------------------------------

    clear: function()
    {
        this.callStepStack = [];

        this.source = {
            products_ids      : [],
            custom_settings   : null,
            account_id        : null,
            marketplace_id    : null,
            listing_title     : null
        };

        this.new_listing_id = null;
        this.templates  = {};

        this.successProducts = [];
        this.errorsCount = 0;

        this.needToSetCatalogPolicy = false;

        this.loadedData = {
            listings     : {},
            translation  : {},
            policy       : {},
            marketplaces : {}
        };
    },

    // ---------------------------------------

    ajaxError: function()
    {
        this.clear();
        MagentoMessageObj.addError(M2ePro.translator.translate('Migration error.'));
        Windows.getFocusedWindow().close();
        $('loading-mask').setStyle({visibility: 'visible'});
    },

    // ---------------------------------------

    pushStep: function(step)
    {
        if (step && this.callStepStack.indexOf(step) == -1) {
            this.callStepStack.push(step);
        }
    },

    popStep: function()
    {
        return this.callStepStack.pop();
    },

    getItemStep: function()
    {
        return this.callStepStack.last();
    },

    isBackAllowed: function()
    {
        return this.callStepStack.length > 1;
    },

    // ---------------------------------------

    loadSourceData: function(accountId, marketplaceId, marketplaces, listingTitle, customSettings)
    {
        this.source.account_id       = accountId      != undefined ? accountId : null;
        this.source.marketplace_id   = marketplaceId  != undefined ? marketplaceId : null;
        this.loadedData.marketplaces = marketplaces   != undefined ? marketplaces : {};
        this.source.listing_title    = listingTitle   != undefined ? listingTitle : null;
        this.source.custom_settings  = customSettings != undefined ? !!customSettings : null;
    },

    // ---------------------------------------

    setProductsIds: function(productsIds)
    {
        this.source.products_ids = productsIds || null;
    },

    getProductsIds: function()
    {
        return this.source.products_ids;
    },

    // ---------------------------------------

    setShownTutorial: function(callback)
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_transferring/shownTutorial'), {
            method: 'post',
            asynchronous: true,
            parameters: {},
            onSuccess: function(transport) {callback && callback();}.bind(this)
        });
    },

    // ---------------------------------------

    getTargetAccount: function()
    {
        return $('transferring_account_id') && $('transferring_account_id').value;
    },

    isDifferentAccount: function(accountId)
    {
        return this.source.account_id != (accountId || this.getTargetAccount());
    },

    // ---------------------------------------

    getTargetMarketplace: function()
    {
        return $('transferring_marketplace_id') && $('transferring_marketplace_id').value;
    },

    getTargetMarketplaceTitle: function()
    {
        var marketplaceSelector = $('transferring_marketplace_id');
        var marketplaceTitle = marketplaceSelector
            ? marketplaceSelector.options[marketplaceSelector.selectedIndex].text
            : '';
        return marketplaceTitle;
    },

    hasTargetMarketplace: function()
    {
        return $('transferring_marketplace_id') && !!$('transferring_marketplace_id').value;
    },

    isDifferentMarketplace: function(marketplaceId)
    {
        return this.source.marketplace_id != (marketplaceId || this.getTargetMarketplace());
    },

    getMarketplaceUrl: function(marketplaceId)
    {
        return marketplaceId &&
               this.loadedData.marketplaces[marketplaceId] != undefined &&
               this.loadedData.marketplaces[marketplaceId].url;
    },

    // ---------------------------------------

    getTargetStore: function()
    {
        return $('transferring_store_id') && $('transferring_store_id').value;
    },

    // ---------------------------------------

    getSourceListingTitle: function()
    {
        return this.source.listing_title;
    },

    // ---------------------------------------

    loadTargetListings: function(accountId, marketplaceId, storeId, callback)
    {
        accountId     = accountId     || this.getTargetAccount();
        marketplaceId = marketplaceId || this.getTargetMarketplace();
        storeId       = storeId       || this.getTargetStore();

        if (!accountId || !marketplaceId || storeId == null) {
            return;
        }

        var key = accountId + '_' + marketplaceId + '_' + storeId;

        if (this.loadedData.listings[key] != undefined && this.loadedData.listings[key] != null) {
            callback && callback();
        } else {
            new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_transferring/getListings'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    account_id     : accountId,
                    marketplace_id : marketplaceId,
                    store_id       : storeId
                },
                onSuccess: function(transport) {

                    var result = transport.responseText.evalJSON();

                    this.loadedData.listings[key] = result['listings'];

                    callback && callback();

                }.bind(this)
            });
        }
    },

    getTargetListings: function(accountId, marketplaceId, storeId)
    {
        accountId     = accountId     || this.getTargetAccount();
        marketplaceId = marketplaceId || this.getTargetMarketplace();
        storeId       = storeId       || this.getTargetStore();

        return this.loadedData.listings[accountId + '_' + marketplaceId + '_' + storeId];
    },

    getNewListingTitle: function()
    {
        return this.getSourceListingTitle() + ' - ' + this.getTargetMarketplaceTitle();
    },

    getTargetListing: function()
    {
        if (this.isNeedCreateListing()) {
            return $('transferring_new_listing_id') && $('transferring_new_listing_id').value;
        } else {
            return $('transferring_existing_listing') && $('transferring_existing_listing').value;
        }
    },

    getTargetListingTitle: function()
    {
        return $('transferring_new_listing_title') && $('transferring_new_listing_title').value;
    },

    hasTargetListing: function()
    {
        if (this.isNeedCreateListing()) {
            return $('transferring_new_listing_id') && !!$('transferring_new_listing_id').value;
        } else {
            return $('transferring_existing_listing') && !!$('transferring_existing_listing').value;
        }

        return false;
    },

    isNeedCreateListing: function()
    {
        return $('transferring_new_listing_block') && $('transferring_new_listing_block').visible();
    },

    // ---------------------------------------

    loadDataStepPolicy: function(accountId, marketplaceId, storeId, callback)
    {
        accountId     = accountId     || this.getTargetAccount();
        marketplaceId = marketplaceId || this.getTargetMarketplace();
        storeId       = storeId       || this.getTargetStore();
        var productsIds = this.getProductsIds();

        if (!accountId || !marketplaceId || storeId == null) {
            return;
        }

        var key = accountId + '_' + marketplaceId + '_' + storeId;

        if (this.loadedData.policy[key] != undefined && this.loadedData.policy[key] != null) {
            return callback && callback();
        } else {
            new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_transferring/stepPolicy'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    account_id     : accountId,
                    marketplace_id : marketplaceId,
                    store_id       : storeId,
                    products_ids   : [productsIds]
                },
                onSuccess: function(transport) {
                    this.loadedData.policy[accountId+'_'+marketplaceId+'_'+storeId] = transport.responseText;
                    callback && callback();
                }.bind(this)
            });
        }
    },

    getDataStepPolicy: function(accountId, marketplaceId, storeId)
    {
        accountId     = accountId     || this.getTargetAccount();
        marketplaceId = marketplaceId || this.getTargetMarketplace();
        storeId       = storeId       || this.getTargetStore();

        return this.loadedData.policy[accountId + '_' + marketplaceId + '_' + storeId];
    },

    // ---------------------------------------

    isShowBreadcrumb: function()
    {
        return !!this.getTargetAccount() && this.hasTargetMarketplace() && this.getTargetStore() != null;
    },

    isNeedManagePolicy: function()
    {
        return this.isNeedCreateListing() && this.hasTargetMarketplace() && this.isDifferentMarketplace();
    },

    isNeedManageCategories: function()
    {
        return this.hasTargetMarketplace() && this.isDifferentMarketplace();
    },

    // ---------------------------------------

    isNeedCreateTemplates: function()
    {
        return !this.hasTargetListing()     &&
               !this.hasTargetTemplates()   &&
                this.hasTargetMarketplace() &&
                this.isDifferentMarketplace();
    },

    createTemplates: function(callback)
    {
        var self = this;

        EbayListingTemplateSwitcherHandlerObj.saveSwitchers(function(params) {

            $H(params).each(function(i) {self.templates[i.key] = i.value;});

            callback && callback();

        });
    },

    getTargetTemplates: function()
    {
        return this.templates;
    },

    hasTargetTemplates: function()
    {
        return !!Object.keys(this.getTargetTemplates()).length;
    },

    // ---------------------------------------

    addSuccessProducts: function(successProducts)
    {
        if (successProducts != undefined) {
            this.successProducts = this.successProducts.concat(successProducts);
        }
    },

    getSuccessProducts: function()
    {
        return this.successProducts;
    },

    hasSuccessProducts: function()
    {
        return this.successProducts.length > 0;
    },

    updateErrorsCount: function(errorsCount)
    {
        if (errorsCount) {
            this.errorsCount = this.errorsCount + errorsCount;
        }
    },

    getErrorsCount: function()
    {
        return this.errorsCount;
    },

    // ---------------------------------------

    setNeedToSetCatalogPolicy: function(flag)
    {
        this.needToSetCatalogPolicy = !!flag;
    },

    isNeedToSetCatalogPolicy: function()
    {
        return this.needToSetCatalogPolicy;
    },

    redirectToCategorySettings: function()
    {
        setLocation(M2ePro.url.get('adminhtml_ebay_listing_categorySettings/index',
            {listing_id: this.getTargetListing(), without_back: true}));
    },

    // ---------------------------------------

    isShowCustomSettingsWarning: function()
    {
        return this.hasTargetMarketplace()   &&
               this.isDifferentMarketplace() &&
               !this.isNeedManagePolicy()    &&
               !!this.source.custom_settings;

    },

    // ---------------------------------------

    getCurTranslationType: function(el)
    {
        return el.options[el.selectedIndex].value;
    },

    getTotalCredits: function()
    {
        if (!$('translation_account_ebay_total_credit_value'))
            return 0;
        var totalCreditsElem = $('translation_account_ebay_total_credit_value');
        return isNaN(parseInt(totalCreditsElem.innerHTML)) ? 0 : parseInt(totalCreditsElem.innerHTML);
    },

    getRemainingAmount: function(el)
    {
        var prepaid = $('translation_account_balance') && parseFloat($('translation_account_balance').innerHTML);
        if (isNaN(prepaid)) {
            prepaid = 0;
        }
        var remainingAmount = this.getEstimatedAmount(el) - parseFloat(prepaid);

        return remainingAmount.toFixed(2);
    },

    getEstimatedAmount: function(el)
    {
        var selectedIndex = el.selectedIndex;
        var avgCost = el.options[selectedIndex].getAttribute('data');
        avgCost = isNaN(parseFloat(avgCost)) ? 0 : parseFloat(avgCost);
        var productsAmount = this.getProductsIds().length;

        if (this.getCurTranslationType(el) === 'silver') {
            productsAmount -= this.getTotalCredits();
        }

        if (productsAmount <= 0)
            return 0;

        return (parseFloat(avgCost) * productsAmount).toFixed(2);
    },

    isShowPaymentWarningMessage: function(el)
    {
        return (this.getRemainingAmount(el) > 0);
    },

    // ---------------------------------------

    confirm: function(callback, progressBarObj, wrapperObj, init)
    {
        if (progressBarObj) {this.progressBarObj = progressBarObj;}
        if (wrapperObj)     {this.wrapperObj = wrapperObj;}

        if (init) {
            this.progressBarObj.reset();
            this.progressBarObj.setTitle(M2ePro.translator.translate('Data migration.'));
            this.progressBarObj.show();

            this.wrapperObj.lock();
        }

        var self = this;

        if (this.isNeedCreateTemplates()) {
            this.progressBarObj.setStatus(M2ePro.translator.translate('Creating Policies in process. Please wait...'));
            this.createTemplates(function() {
                self.progressBarObj.setPercents(self.progressBarObj.getPercents() + 10, 1);
                self.confirm(callback);
            });

        } else if (!this.hasTargetListing()) {
            this.progressBarObj.setStatus(M2ePro.translator.translate('Creating Listing in process. Please wait...'));
            this.createListing(function() {
                self.progressBarObj.setPercents(self.progressBarObj.getPercents() + 10, 1);
                self.confirm(callback);
            });

        } else {

            this.addProducts(function() {
                callback && callback();
            });

        }
    },

    // ---------------------------------------

    createListing: function(callback)
    {
        var parameters                = this.getTargetTemplates();
            parameters.account_id     = this.getTargetAccount();
            parameters.title          = this.getTargetListingTitle();
            parameters.marketplace_id = this.getTargetMarketplace();
            parameters.store_id       = this.getTargetStore();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_transferring/createListing'), {
            method: 'post',
            asynchronous: true,
            parameters: parameters,
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();
                if (response['result'] == 'success' && response['listing_id']) {

                    if ($('transferring_new_listing_id')) {
                        $('transferring_new_listing_id').value = response['listing_id'];
                    }

                    return callback && callback();
                }

                return this.ajaxError();

            }.bind(this)
        });
    },

    // ---------------------------------------

    createTranslationAccount: function(callback)
    {
        $('translation_account_error_block') && $('translation_account_error_block').hide();

        var accountId = this.getTargetAccount();
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_transferring/createTranslationAccount'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                account_id : accountId,
                email      : $('transferring_email')     && $('transferring_email').value,
                first_name : $('transferring_firstname') && $('transferring_firstname').value,
                last_name  : $('transferring_lastname')  && $('transferring_lastname').value,
                company    : $('transferring_company')   && $('transferring_company').value,
                country    : $('transferring_country')   && $('transferring_country').value
            },
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();
                if (response['result'] != 'success') {
                    $('translation_account_error_block') && $('translation_account_error_block').show();
                    return;
                }

                var accountSelector = $('transferring_account_id');
                if (accountSelector) {
                    var option = $('transferring_account_id').down('option[value='+accountId+']');
                    if (option) {
                        option.setAttribute('data', '1');

                        if ($('translation_account_ebay_id')) {
                            $('translation_account_ebay_id').innerHTML = option.innerHTML;
                        }
                    }
                }

                if ($('translation_account_balance')) {
                    $('translation_account_balance').innerHTML = response['info']['credit']['prepaid'];
                }

                if ($('translation_account_currency')) {
                    $('translation_account_currency').innerHTML = response['info']['currency'];
                }

                if ($('translation_estimated_currency')) {
                    $('translation_estimated_currency').innerHTML = response['info']['currency'];
                }

                callback && callback();

            }.bind(this)
        });
    },

    refreshTranslationAccount: function(callback)
    {
        var accountId = this.getTargetAccount();
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_transferring/refreshTranslationAccount'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                account_id: accountId
            },
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();
                if (response['result'] != 'success') {
                    return;
                }

                if ($('translation_account_balance')) {
                    $('translation_account_balance').innerHTML = parseFloat(response['info']['credit']['prepaid']).toFixed(2);
                }

                if ($('translation_account_ebay_total_credit_value')) {
                    $('translation_account_ebay_total_credit_value').innerHTML =
                    parseInt(response['info']['credit']['translation']) -
                    parseInt(response['info']['credit']['used']);
                }

                if ($('translation_account_currency')) {
                    $('translation_account_currency').innerHTML = response['info']['currency'];
                }

                if ($('translation_estimated_currency')) {
                    $('translation_estimated_currency').innerHTML = response['info']['currency'];
                }

                callback && callback();

            }.bind(this)
        });
    },

    // ---------------------------------------

    addProducts: function(callback)
    {
        var parts = this.makeProductsParts();

        this.progressBarObj.setStatus(M2ePro.translator.translate('Adding Products in process. Please wait...'));

        this.sendPartsProducts(parts, parts.length, callback);
    },

    makeProductsParts: function()
    {
        var productsArray = this.getProductsIds();
        var parts = new Array();

        if (productsArray.length < this.productsInPart) {
            parts[0] = productsArray;
            return parts;
        }

        var result = new Array();
        for (var i = 0; i < productsArray.length; i++) {
            if (result.length == 0 || result[result.length-1].length == this.productsInPart) {
                result[result.length] = new Array();
            }
            result[result.length-1][result[result.length-1].length] = productsArray[i];
        }

        return result;
    },

    sendPartsProducts: function(parts, partsCount, callback)
    {
        var self = this;

        if (parts.length == 0) {
            return;
        }

        var isLastPart  = parts.length === 1 ? 1 : 0;
        var part = parts.splice(0, 1);
        part = part[0];
        var partString = implode(',', part);

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_transferring/addProducts'), {
            method: 'post',
            parameters: {
                products         : partString,
                target_listing_id: self.getTargetListing(),
                is_last_part     : isLastPart,
                total_errors_count: this.getErrorsCount()
            },
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();

                if (response['result'] != 'success') {
                    self.clear();
                    return location.reload();
                }

                self.addSuccessProducts(response['success_products']);
                self.updateErrorsCount(response['errors_count']);

                var percents =
                    ((100 - self.progressBarObj.getPercents()) / parts.length) + self.progressBarObj.getPercents();

                if (percents >= 100) {
                    self.progressBarObj.setPercents(100, 0);
                    self.progressBarObj.setStatus('Adding has been completed.');

                    callback && callback();

                    return;

                } else {
                    self.progressBarObj.setPercents(percents, 1);
                }

                setTimeout(function() {
                    self.sendPartsProducts(parts, partsCount);
                }, 500);
            }
        });
    }

    // ---------------------------------------
};