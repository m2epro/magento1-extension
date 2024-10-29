WalmartMarketplace = Class.create(Marketplace, {
    runEnabledSynchronization: function()
    {
        var currentStatuses = this.getCurrentStatuses();
        var storedStatuses = this.getStoredStatuses();
        var changedStatuses = new Array();
        this.marketplacesForUpdate = new Array();

        var needRunNextMarketplaceNow = false;
        for (var i =0; i < storedStatuses.length; i++) {
            for (var j = 0; j < currentStatuses.length; j++) {

                if ((storedStatuses[i].marketplace_id == currentStatuses[j].marketplace_id)
                    && (storedStatuses[i].status != currentStatuses[j].status)) {

                    this.storedStatuses[i].status = currentStatuses[j].status;
                    changedStatuses.push({
                        marketplace_id: currentStatuses[j].marketplace_id,
                        status: currentStatuses[j].status
                    });

                    if (this.storedStatuses[i].is_need_sync_after_save === false) {
                        continue;
                    }

                    this.changeStatusInfo(currentStatuses[j].marketplace_id, currentStatuses[j].status);

                    if (currentStatuses[j].status) {
                        this.marketplacesForUpdate[this.marketplacesForUpdate.length] = currentStatuses[j].marketplace_id;
                        needRunNextMarketplaceNow = true;
                    }

                    break;
                }
            }
        }

        if (needRunNextMarketplaceNow) {
            this.marketplacesForUpdateCurrentIndex = 0;
            this.runNextMarketplaceNow();
        }

        return changedStatuses;
    }
});