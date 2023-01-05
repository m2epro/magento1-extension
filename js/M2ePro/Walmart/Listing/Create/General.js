window.WalmartListingCreateGeneral = Class.create(Common, {

    marketplaceSynchProgressObj: null,
    accounts: null,
    selectedAccountId: null,

    // ---------------------------------------

    initialize: function() {
        var self = this;

        self.marketplaceSynchProgressObj = new WalmartListingCreateGeneralMarketplaceSynchProgress(
            new ProgressBar('progress_bar'),
            new AreaWrapper('content_container')
        );

        CommonObj.setValidationCheckRepetitionValue(
            'M2ePro-listing-title',
            M2ePro.translator.translate('The specified Title is already used for other Listing. Listing Title must be unique.'),
            'Listing', 'title', 'id', null, M2ePro.php.constant('Ess_M2ePro_Helper_Component::NICK')
        );

        self.initAccount();
    },

    initObservers: function() {
        $('store_id').observe('change', WalmartListingCreateGeneralObj.store_id_change);
        $('store_id').simulate('change');
    },

    // ---------------------------------------

    initAccount: function() {
        var self = this;

        $('account_id').observe('change', function() {
            self.selectedAccountId = this.value;

            if (this.value == '') {
                $('marketplace_info').hide();
                return;
            }

            if (self.accounts !== null) {
                for (var i = 0; i < self.accounts.length; i++) {
                    if (self.accounts[i].id == this.value) {
                        $('marketplace_info').show();
                        $('marketplace_id').value = self.accounts[i].marketplace_id;
                        $('marketplace_title').innerHTML = self.accounts[i].marketplace_title;
                        $('marketplace_url').innerHTML = self.accounts[i].marketplace_url;

                        break;
                    }
                }
            }

            WalmartListingSettingsObj.checkSellingFormatMessages();
        });

        self.renderAccounts();

        $('add_account_button').observe('click', function() {
            var win = window.open(M2ePro.url.get('adminhtml_walmart_account/new'));

            var intervalId = setInterval(function() {

                if (!win.closed) {
                    return;
                }

                clearInterval(intervalId);

                self.renderAccounts();

            }, 1000);
        });
    },

    save_and_next: function() {
        var self = this;

        if (self.marketplaceSynchProgressObj.runningNow) {
            alert({
                content: M2ePro.translator.translate('Please wait while Synchronization is finished.')
            });
            return;
        }

        var editForm = new varienForm('edit_form');
        if (editForm.validate()) {
            self.synchronizeMarketplace($('marketplace_id').value);
        }
    },

    renderAccounts: function(callback) {
        var self = this;

        var account_add_btn = $('add_account_button');
        var account_label_el = $('account_label');
        var account_select_el = $('account_id');
        var marketplace_info = $('marketplace_info');

        //firefox can't simulate events for disabled elements
        if (account_select_el.disabled) {
            account_select_el.enable();
            self.accountDisabled = true;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_general/getAccounts'), {
            method: 'get',
            onSuccess: function(transport) {
                var accounts = transport.responseText.evalJSON();

                if (self.accounts === null) {
                    self.accounts = accounts;
                }

                if (self.selectedAccountId === null) {
                    self.selectedAccountId = account_select_el.value;
                }

                var isAccountsChanged = !self.isAccountsEqual(accounts);

                if (isAccountsChanged) {
                    self.accounts = accounts;
                }

                if (accounts.length === 0) {
                    account_add_btn.down('span').update(M2ePro.translator.translate('Add'));
                    account_label_el.update(M2ePro.translator.translate('Account not found, please create it.'));
                    account_label_el.show();
                    account_select_el.hide();
                    marketplace_info.hide();
                    return;
                }

                marketplace_info.show();

                account_select_el.update();
                account_select_el.appendChild(new Element('option', {style: 'display: none'}));
                accounts.each(function(account) {
                    account_select_el.appendChild(new Element('option', {value: account.id})).insert(account.title);
                });

                account_add_btn.down('span').update(M2ePro.translator.translate('Add Another'));

                if (accounts.length === 1) {
                    var account = accounts[0];

                    self.selectedAccountId = account.id;

                    var accountElement;

                    if (M2ePro.formData.wizard) {
                        accountElement = new Element('span').update(account.title);
                    } else {
                        var accountLink = M2ePro.url.get('adminhtml_walmart_account/edit', {
                            'id': account.id,
                            close_on_save: 1
                        });
                        accountElement = new Element('a', {
                            'href': accountLink,
                            'target': '_blank'
                        }).update(account.title);
                    }

                    account_label_el.update(accountElement);

                    account_label_el.show();
                    account_select_el.hide();
                } else if (isAccountsChanged) {
                    self.selectedAccountId = accounts.pop().id;

                    account_label_el.hide();
                    account_select_el.show();
                }

                account_select_el.setValue(self.selectedAccountId);

                account_select_el.simulate('change');

                //firefox can't simulate events for disabled elements
                if (self.accountDisabled) {
                    account_select_el.disable();
                }

                callback && callback();
            }
        });
    },

    synchronizeMarketplace: function(marketplaceId) {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_general/isMarketplaceEnabled'), {
            method: 'get',
            parameters: {marketplace_id: marketplaceId},
            onSuccess: function(transport) {

                var result = transport.responseText.evalJSON();
                if (result.status) {
                    return self.marketplaceSynchProgressObj.end();
                }

                var params = {};
                params['status_' + marketplaceId] = 1;

                new Ajax.Request(M2ePro.url.get('adminhtml_walmart_marketplace/save'), {
                    method: 'post',
                    parameters: params,
                    onSuccess: function() {

                        var title = 'Walmart ' + $('marketplace_title').innerHTML;

                        self.marketplaceSynchProgressObj.runTask(
                            title,
                            M2ePro.url.get('adminhtml_walmart_marketplace/runSynchNow', {'marketplace_id': marketplaceId}),
                            M2ePro.url.get('adminhtml_walmart_marketplace/synchGetExecutingInfo'),
                            'WalmartListingCreateGeneralObj.marketplaceSynchProgressObj.end()'
                        );
                    }
                });
            }
        });
    },

    isAccountsEqual: function(newAccounts) {
        if (!newAccounts.length && !this.accounts.length) {
            return true;
        }

        if (newAccounts.length !== this.accounts.length) {
            return false;
        }

        for (var i = 0; i < this.accounts.length; i++) {
            if (this.accounts[i].length <= 0) {
                return false;
            }
        }

        return true;
    },

    // ---------------------------------------

    store_id_change: function() {
        WalmartListingSettingsObj.checkSellingFormatMessages();
    }

    // ---------------------------------------
});
