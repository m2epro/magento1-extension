EbayListingTransferringHandler = Class.create(CommonHandler, {

    // ---------------------------------------

    initialize: function()
    {
        this.actionHandler = new EbayListingTransferringActionHandler();
        this.breadcrumbHandler = new EbayListingTransferringBreadcrumbHandler();
        this.marketplaceProgressHandlerObj = null;
    },

    // ---------------------------------------

    loadActionHtml: function(selectedProductsIds, callback, productName)
    {
        this.actionHandler.setProductsIds(selectedProductsIds);
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_transferring/index'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                products_ids: [this.actionHandler.getProductsIds()]
            },
            onSuccess: function(transport) {

                var content = transport.responseText;
                var title = M2ePro.translator.translate('Sell on Another Marketplace');

                if (productName) {
                    title += '&nbsp;' + M2ePro.translator.translate('Product') + '&nbsp;"' + productName + '"';
                }

                this.openPopUp(title, content);

                callback && callback();

            }.bind(this)
        });
    },

    // ---------------------------------------

    openPopUp: function(title, content, clearMessages)
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
                EbayListingTransferringHandlerObj.actionHandler.clear();
                EbayListingTransferringHandlerObj.marketplaceProgressHandlerObj = null;

                $('excludeListPopup') && Windows.getWindow('excludeListPopup').destroy();

                return true;
            }
        };

        try {
            this.popUp = Windows.getFocusedWindow() || Dialog.info(null, config);
            $('modal_dialog_message').innerHTML = content;
            $('modal_dialog_message').innerHTML.evalScripts();
        } catch (ignored) {}

        clearMessages || MagentoMessageObj.clearAll();
        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '500px';
        }, 50);
    },

    // ---------------------------------------

    showStep: function(refreshBreadcrumb, refreshButtons)
    {
        // need for correct render helper-blocks
        initializationMagentoBlocks();

        var itemStep = this.actionHandler.getItemStep();
        ['tutorial', 'destination', 'policy', 'translation'].forEach(function(el) {
            if ($('data_container_step_' + el)) {
                if (el == itemStep) {
                    $('data_container_step_' + el).show();
                } else {
                    $('data_container_step_' + el).hide();
                }
            }
        });

        (!!refreshBreadcrumb || true) && this.refreshBreadcrumb();
        (!!refreshButtons    || true) && this.refreshButtons();
    },

    back: function()
    {
        if (!this.actionHandler.isBackAllowed()) {
            return;
        }

        this.actionHandler.popStep();
        this.showStep(true, false);
    },

    // ---------------------------------------

    go: function(callback)
    {
        callback = callback || this.renderStepDestination.bind(this);
        this.actionHandler.setShownTutorial(callback);
    },

    // ---------------------------------------

    renderStepTutorial: function()
    {
        this.actionHandler.pushStep('tutorial');
        this.showStep(true, true);
    },

    // ---------------------------------------

    renderStepDestination: function()
    {
        this.actionHandler.pushStep('destination');
        this.showStep(true, true);
        this.refreshMarketplaces();
    },

    refreshMarketplaces: function()
    {
        if (!$('transferring_marketplace_id')) {
            return;
        }

        var isSourceAccount = !this.actionHandler.isDifferentAccount();
        var marketplaceSelector = $('transferring_marketplace_id');

        var length = marketplaceSelector.length;
        for (var i = 0; i < length; i++) {
            var option = marketplaceSelector.options[i];
            if (isSourceAccount && !this.actionHandler.isDifferentMarketplace(option.value)) {
                option.hide();
                if (option.selected) {
                    $('transferring_marketplace_url_note') && $('transferring_marketplace_url_note').hide();
                    marketplaceSelector.insertBefore(
                        new Element('option', {value: '', text: '', selected: true}),
                        marketplaceSelector.firstChild
                    );
                }
            } else {
                option.show();
            }
        }
        this.refreshBreadcrumb();
        this.refreshButtons();
    },

    refreshAccounts: function(callback)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_transferring/getAccounts'), {
            method: 'get',
            onSuccess: function(transport) {

                var accountSelector = $('transferring_account_id');

                if (accountSelector) {
                    var accounts = transport.responseText.evalJSON();

                    accountSelector.update();
                    accounts.each(function(account) {
                        var attributes = {
                            value: account.id,
                            data:  account.translation_hash
                        };

                        if (!self.actionHandler.isDifferentAccount(account.id)) {
                            attributes.selected = true;
                        }

                        accountSelector.appendChild(new Element('option', attributes)).insert(account.title);
                    });
                }

                self.refreshListings();

                self.refreshBreadcrumb();
                self.refreshButtons();

                callback && callback();
            }
        });
    },

    refreshStores: function(callback)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_transferring/getStores'), {
            method: 'get',
            onSuccess: function(transport) {
                $('transferring_store_id') && $('transferring_store_id').update(transport.responseText);

                callback && callback();
            }
        });
    },

    refreshListings: function()
    {
        var accountId     = this.actionHandler.getTargetAccount();
        var marketplaceId = this.actionHandler.getTargetMarketplace();
        var storeId       = this.actionHandler.getTargetStore();

        var self = this;

        this.actionHandler.loadTargetListings(accountId, marketplaceId, storeId, function() {
            var listingsSelector = $('transferring_existing_listing');

            if (listingsSelector) {
                listingsSelector.update();

                var listings = self.actionHandler.getTargetListings(accountId, marketplaceId, storeId);

                if (listings.length) {
                    listings.each(function(listing) {
                        listingsSelector.appendChild(new Element('option', {value: listing.id})).insert(listing.title);
                    });

                    listingsSelector.options.add(new Element('option', {value: '', text: '', selected: true}), 0);

                    $('transferring_new_listing_block')       && $('transferring_new_listing_block').hide();
                    $('transferring_existing_listing_choose') && $('transferring_existing_listing_choose').show();
                    $('transferring_existing_listing_block')  && $('transferring_existing_listing_block').show();
                } else {
                    $('transferring_existing_listing_choose') && $('transferring_existing_listing_choose').hide();
                    $('transferring_existing_listing_block')  && $('transferring_existing_listing_block').hide();
                    $('transferring_new_listing_block')       && $('transferring_new_listing_block').show();
                }

                $('transferring_listing_row') && $('transferring_listing_row').show();
            }

            self.refreshAutoActionWarning();
            self.refreshStoreNote();

            self.refreshBreadcrumb();
            self.refreshButtons();
        });
    },

    // ---------------------------------------

    renderStepPolicy: function()
    {
        var accountId     = this.actionHandler.getTargetAccount();
        var marketplaceId = this.actionHandler.getTargetMarketplace();
        var storeId       = this.actionHandler.getTargetStore();

        var self = this;

        this.actionHandler.loadDataStepPolicy(accountId, marketplaceId, storeId, function() {

            self.actionHandler.pushStep('policy');

            $('data_container_step_policy') && $('data_container_step_policy')
                .update(self.actionHandler.getDataStepPolicy(accountId, marketplaceId, storeId));

            self.showStep(true, true);
        });
    },

    // ---------------------------------------

    refreshBreadcrumb: function()
    {
        if ($('transferring_use_custom_settings')) {
            if (this.actionHandler.isShowCustomSettingsWarning()) {
                $('transferring_use_custom_settings').show();
            } else {
                $('transferring_use_custom_settings').hide();
            }
        }

        if (this.actionHandler.isShowBreadcrumb()) {
            var itemStep = this.actionHandler.getItemStep();
            var breadcrumbs = ['destination'];

            (itemStep == 'policy' || this.actionHandler.isNeedManagePolicy()) && breadcrumbs.push('policy');

            if (breadcrumbs.length > 1) {
                this.breadcrumbHandler.showSteps(breadcrumbs);
                this.breadcrumbHandler.highlightStep(itemStep);
            } else {
                this.breadcrumbHandler.hideAll();
            }
        } else {
            this.breadcrumbHandler.hideAll();
        }
    },

    // ---------------------------------------

    refreshButtons: function()
    {
        var itemStep = this.actionHandler.getItemStep();
        var nextStepAllowed = false;

        if ($('back_button_' + itemStep)) {
            if (this.actionHandler.isBackAllowed()) {
                $('back_button_' + itemStep).show();
            } else {
                $('back_button_' + itemStep).hide();
            }
        }

        var self = this;

        if (itemStep == 'destination' && $('continue_button_destination')) {

            if (this.actionHandler.isNeedManagePolicy()) {
                nextStepAllowed = true;
                $('continue_button_destination')
                    .stopObserving('click')
                    .observe('click', function() {
                        self.validate() && self.synchronizeMarketplace('EbayListingTransferringHandlerObj.renderStepPolicy();');
                    });
            } else if (this.actionHandler.isNeedManageCategories() &&
                !$('transferring_existing_listing').hasChildNodes()) {

                nextStepAllowed = true;
                $('continue_button_destination')
                    .stopObserving('click')
                    .observe('click', function() {
                        self.validate() && self.synchronizeMarketplace(self.confirm.bind(self, true));
                    });
            }

        } else if (itemStep == 'policy' && $('continue_button_policy')) {

            if (this.actionHandler.isNeedManageCategories()) {
                nextStepAllowed = true;
                $('continue_button_policy')
                    .stopObserving('click')
                    .observe('click', function () {
                        self.validate() && self.actionHandler.createTemplates(self.confirm.bind(self, true))
                    });
            }
        }

        if (nextStepAllowed) {
            $('confirm_button_' + itemStep)  && $('confirm_button_' + itemStep).hide();
            $('continue_button_' + itemStep) && $('continue_button_' + itemStep).show();
        } else {
            $('continue_button_' + itemStep) && $('continue_button_' + itemStep).hide();
            $('confirm_button_' + itemStep)  && $('confirm_button_' + itemStep).show();
        }
    },

    refreshAutoActionWarning: function()
    {
        if ($('transferring_auto_categories_warning')) {
            if (this.actionHandler.isDifferentMarketplace() && this.actionHandler.isNeedCreateListing()) {
                $('transferring_auto_categories_warning').show();
            } else {
                $('transferring_auto_categories_warning').hide();
            }
        }
    },

    refreshStoreNote: function()
    {
        var marketplaceSelect = $('transferring_marketplace_id');
        var storeNote         = $('transferring_store_note');

        if (!storeNote || !marketplaceSelect) {
            return;
        }

        var selectedIndex = marketplaceSelect.selectedIndex;
        storeNote.show();
    },

    synchronizeMarketplace: function(callback)
    {
        var marketplaceSelect = $('transferring_marketplace_id');

        if (!marketplaceSelect) {
            return;
        }

        if (!this.marketplaceProgressHandlerObj) {
            var ProgressBarObj = new ProgressBar('data_container_progress');
            var WrapperObj = new AreaWrapper('data_container');
            this.marketplaceProgressHandlerObj = new EbayMarketplaceSynchProgressHandler(ProgressBarObj, WrapperObj);
        }

        var selectedIndex = marketplaceSelect.selectedIndex;
        var marketplaceData = marketplaceSelect.options[selectedIndex].getAttribute('data');

        if (parseInt(marketplaceData) || !marketplaceSelect.value) {
            return callback && eval(callback);
        }

        var marketplaceId = this.actionHandler.getTargetMarketplace();

        var params = {};
        params['status_' + marketplaceId] = 1;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_marketplace/save'), {
            method: 'post',
            parameters: params,
            onSuccess: function() {

                var option = $('transferring_marketplace_id').down('option[value='+marketplaceId+']');
                var title = 'eBay ' + option.innerHTML;

                this.marketplaceProgressHandlerObj.runTask(
                    title,
                    M2ePro.url.get('runSynchNow', {'marketplace_id': marketplaceId}),
                    '', 'self.end();' + callback
                );

            }.bind(this)
        });
    },

    // ---------------------------------------

    validate: function()
    {
        var validationResult = [];

        if (this.actionHandler.getItemStep() == 'destination') {
            $('transferring_account_id') &&
                validationResult.push(Validation.validate($('transferring_account_id')));
            $('transferring_marketplace_id') &&
                validationResult.push(Validation.validate($('transferring_marketplace_id')));

            if (this.actionHandler.isNeedCreateListing()) {
                $('transferring_new_listing_title') &&
                    validationResult.push(Validation.validate($('transferring_new_listing_title')));
            } else {
                $('transferring_existing_listing') &&
                    validationResult.push(Validation.validate($('transferring_existing_listing')));
            }
        }

        if (validationResult.indexOf(false) != -1) {
            return false;
        }

        return true;
    },

    // ---------------------------------------

    confirm: function(needToSetCategoryPolicies)
    {
        if (!this.validate()) {
            return;
        }

        $('data_container') && $('data_container').hide();

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '160px';
            Windows.getFocusedWindow().content.style.maxHeight = '200px';
        }, 50);

        var progressBarObj = new ProgressBar('data_container_progress');
        var wrapperObj = new AreaWrapper('data_container');

        $('loading-mask').setStyle({visibility: 'hidden'});

        this.actionHandler.setNeedToSetCatalogPolicy(needToSetCategoryPolicies);
        var callback = function() {

            if (EbayListingTransferringHandlerObj.actionHandler.hasSuccessProducts()) {
                if (EbayListingTransferringHandlerObj.actionHandler.isNeedToSetCatalogPolicy()) {
                    EbayListingTransferringHandlerObj.actionHandler.redirectToCategorySettings();
                } else if (EbayListingTransferringHandlerObj.actionHandler.hasTargetListing()) {

                    window.open(
                        M2ePro.url.get(
                            'adminhtml_ebay_listing/getTransferringUrl',
                            {
                                id: EbayListingTransferringHandlerObj.actionHandler.getTargetListing(),
                                view_mode: 'ebay'
                            }
                        )
                    );
                    EbayListingTransferringHandlerObj.popUp.close();
                }
            } else {
                EbayListingTransferringHandlerObj.popUp.close();
            }

            $('loading-mask').setStyle({visibility: 'visible'});

            if (window.location.href.charAt(window.location.href.length-1) == '#') {
                setLocation(location.href);
            } else {
                setLocation(location.href + '#');
            }

        }.bind(this);

        this.actionHandler.confirm(callback, progressBarObj, wrapperObj, true);
    },

    // ---------------------------------------

    addAccountClick: function()
    {
        var win = window.open(M2ePro.url.get('adminhtml_ebay_account/new', {close_on_save: true, wizard: false}));

        var intervalId = setInterval(function() {
            if (!win.closed) { return; }
            clearInterval(intervalId);

            if ($('transferring_account_id')) {
                var targetAccount = $('transferring_account_id').value;
                var countAccounts = $('transferring_account_id').options.length;
            }

            EbayListingTransferringHandlerObj.refreshAccounts(function() {
                if ($('transferring_account_id')) {
                    var selectAccount = $('transferring_account_id');
                    if (selectAccount.options.length != countAccounts) {
                        var maxValue = 0;
                        $A(selectAccount.options).each(function(el) {
                            if (parseInt(el.value) > maxValue) { maxValue = parseInt(el.value); el.selected = true; }
                        });
                    } else {
                        $A(selectAccount.options).each(function(el) {
                            if (el.value == targetAccount) { el.selected = true; }
                        });
                    }
                    EbayListingTransferringHandlerObj.accountIdChange();
                }
            });
        }, 1000);
    },

    accountIdChange: function(el)
    {
        this.refreshMarketplaces();
        this.refreshListings();
    },

    marketplaceIdChange: function(el)
    {
        if (!el.value) { return; }

        $A(el.options).each(function(el) {
            if (!el.value) { el.remove(); }
        });
        var marketplaceUrl = this.actionHandler.getMarketplaceUrl(el.value);
        $('transferring_marketplace_url_note') && $('transferring_marketplace_url_note').update(marketplaceUrl).show();

        if ($('transferring_new_listing_title')) {
            $('transferring_new_listing_title').value = this.actionHandler.getNewListingTitle();
        }

        this.refreshListings();
    },

    addStoreClick: function()
    {
        var win = window.open(M2ePro.url.get('adminhtml_system_store/index', {}));

        if ($('transferring_store_id')) {
            var targetStore = $('transferring_store_id').value;
            var countStores = $('transferring_store_id').options.length;
        }

        var intervalId = setInterval(function() {
            if (!win.closed) { return; }
            clearInterval(intervalId);
            EbayListingTransferringHandlerObj.refreshStores(function() {
                if ($('transferring_store_id')) {
                    var selectStore = $('transferring_store_id');
                    if (selectStore.options.length != countStores) {
                        var maxValue = 0;
                        $A(selectStore.options).each(function(el) {
                        if (parseInt(el.value) > maxValue) { maxValue = parseInt(el.value); el.selected = true; }
                    });
                    } else {
                        $A(selectStore.options).each(function(el) {
                            if (el.value == targetStore) { el.selected = true; }
                        });
                    }
                }

                EbayListingTransferringHandlerObj.refreshListings();

                EbayListingTransferringHandlerObj.refreshBreadcrumb();
                EbayListingTransferringHandlerObj.refreshButtons();

            });
        }, 1000);
    },

    existingListingChange: function(el)
    {
        if (!el.value) { return; }
        el.childElements().each(function(el) {
            if (!el.value) { el.remove(); }
        });
        this.refreshBreadcrumb();
        this.refreshButtons();
    },

    existingListingLinkClick: function()
    {
        $('transferring_new_listing_title')      && Validation.reset($('transferring_new_listing_title'));
        $('transferring_new_listing_block')      && $('transferring_new_listing_block').hide();
        $('transferring_existing_listing_block') && $('transferring_existing_listing_block').show();

        EbayListingTransferringHandlerObj.refreshAutoActionWarning();

        EbayListingTransferringHandlerObj.refreshBreadcrumb();
        EbayListingTransferringHandlerObj.refreshButtons();
    },

    newListingLinkClick: function()
    {
        $('transferring_existing_listing')       && Validation.reset($('transferring_existing_listing'));
        $('transferring_existing_listing_block') && $('transferring_existing_listing_block').hide();
        $('transferring_new_listing_block')      && $('transferring_new_listing_block').show();

        EbayListingTransferringHandlerObj.refreshAutoActionWarning();

        EbayListingTransferringHandlerObj.refreshBreadcrumb();
        EbayListingTransferringHandlerObj.refreshButtons();
    }

    // ---------------------------------------
});