MigrationNewAmazonHandler = Class.create(WizardAmazonCustomHandler, {

    // ---------------------------------------

    synchronizeMarketplaces: function()
    {
        if (this.index > this.marketplacesLastIndex) {
            $('custom-progressbar').hide();

            var stepIndex = WizardHandlerObj.steps.nicks.indexOf('marketplacesSynchronization');
            var nextStepNick = WizardHandlerObj.steps.nicks[stepIndex + 1];

            this.setNextStep(nextStepNick);
            return;
        }

        var self = this,
            marketplaces = this.getMarketplacesData(),
            marketplaceId = marketplaces[self.index] != undefined ?
                                    marketplaces[self.index].id : 0,
            current = $$('.code-'+ marketplaces[self.index].code)[0];

        (marketplaceId <= 0) && this.setNextStep(this.nextStep);

        ++this.index;

        var startPercent = self.percent;
        self.percent += Math.round(100 / marketplaces.length);
        self.marketplaceSynchProcess(current);

        new Ajax.Request(M2ePro.url.get('marketplacesSynchronization'), {
            method: 'get',
            parameters: {
                id: marketplaceId
            },
            asynchronous: true,
            onSuccess: (function(transport) {

                if (transport.responseText == 'success') {
                    self.progressBarStartLoad(
                        startPercent + 1, self.percent,
                        function() {
                            self.marketplaceSynchComplete(current);
                            self.synchronizeMarketplaces();
                        }
                    );
                }

                return flase;
            }).bind(this)
        })
    }

    // ---------------------------------------
});