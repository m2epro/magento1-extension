WizardBuyMarketplaceHandler = Class.create();
WizardBuyMarketplaceHandler.prototype = Object.extend(new MarketplaceHandler(), {

    // ---------------------------------------

    proceedAction: function(step)
    {
        MagentoMessageObj.clearAll();

        this.runAllSynchronization();

        var waitingForEnd = setInterval(function() {
            if (this.completeStatus === true) {
                WizardHandlerObj.skipStep(step);
                clearInterval(waitingForEnd);
            }
        }.bind(this), 300);
    },

    // ---------------------------------------

    runNextMarketplaceNow: function()
    {
        var self = this;

        if (self.marketplacesForUpdateCurrentIndex > 0) {

            var tempEndFlag = 0;
            if (self.marketplacesForUpdateCurrentIndex >= self.marketplacesForUpdate.length) {
                tempEndFlag = 1;
            }

            new Ajax.Request(M2ePro.url.get('adminhtml_general/synchGetLastResult'), {
                method:'get',
                asynchronous: true,
                onSuccess: function(transport) {

                    if (transport.responseText == self.synchProgressObj.resultTypeError) {
                        self.synchErrors++;
                    } else if (transport.responseText == self.synchProgressObj.resultTypeWarning) {
                        self.synchWarnings++;
                    } else {
                        self.synchSuccess++;
                    }

                    if (tempEndFlag == 1) {
                        if (self.synchErrors > 0) {
                            self.synchProgressObj.printFinalMessage(self.synchProgressObj.resultTypeError);
                        } else if (self.synchWarnings > 0) {
                            self.synchProgressObj.printFinalMessage(self.synchProgressObj.resultTypeWarning);
                        } else {
                            self.synchProgressObj.printFinalMessage(self.synchProgressObj.resultTypeSuccess);
                        }
                        self.synchErrors = 0;
                        self.synchWarnings = 0;
                        self.synchSuccess = 0;
                    }
                }
            });
        }

        if (self.marketplacesForUpdateCurrentIndex >= self.marketplacesForUpdate.length) {

            self.marketplacesForUpdate = new Array();
            self.marketplacesForUpdateCurrentIndex = 0;
            self.marketplacesUpdateFinished = true;

            self.synchProgressObj.end();
            self.completeStatus = true;

            return;
        }

        var marketplaceId = self.marketplacesForUpdate[self.marketplacesForUpdateCurrentIndex];
        self.marketplacesForUpdateCurrentIndex++;

        var storedStatusObj = self.getStoredStatusObjByMarketplaceId(marketplaceId);
        var titleProgressBar = storedStatusObj ? storedStatusObj.marketplace_title : '';
        var marketplaceComponentName = storedStatusObj ? storedStatusObj.component_title : '';

        if (marketplaceComponentName != '') {
            titleProgressBar = marketplaceComponentName + ' ' + titleProgressBar;
        }

        self.synchProgressObj.runTask(
            titleProgressBar,
            M2ePro.url.get('runSynchNow', {'marketplace_id': marketplaceId}),
            '', 'WizardBuyMarketplaceHandlerObj.runNextMarketplaceNow();'
        );

        return true;
    },

    getStoredStatusObjByMarketplaceId: function(id)
    {
        if (id == '') {
            return;
        }

        for (var i = 0; i < this.storedStatuses.length; i++) {
            if (this.storedStatuses[i].marketplace_id == id) {
                return this.storedStatuses[i];
            }
        }
    },

    // ---------------------------------------

    changeStatusInfo: function()
    {}

    // ---------------------------------------
});