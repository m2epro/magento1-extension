window.AmazonMarketplaceSync = Class.create(Common, {

    urlForGetMarketplaces: null,
    urlForUpdateMarketplacesDetails: null,
    urlForGetProductTypes: null,
    urlForUpdateProductType: null,
    progressBar:null,
    totalItems: 0,
    processedItems: 0,
    isWaiterActive: false,

    initialize: function(progressBar) {
        this.urlForUpdateMarketplacesDetails = M2ePro.url.get('urlForUpdateMarketplacesDetails');
        this.urlForGetMarketplaces = M2ePro.url.get('urlForGetMarketplaces');
        this.urlForGetProductTypes = M2ePro.url.get('urlForGetProductTypes');
        this.urlForUpdateProductType = M2ePro.url.get('urlForUpdateProductType');
        this.progressBar = progressBar;
    },
    async start() {
       // this.waiterStart();

        this.progressBarPrepare();

        try {

            const response = await this.getMarketplaces();
            const marketplaces = response.list.map(
                marketplace => {
                    return {
                        'id': marketplace.id,
                        'title': marketplace.title,
                    };
                },
            );

            this.progressBarChangeStatus(M2ePro.translator.translate('Update Marketplace details. Please wait...'), marketplaces.size());

            // ----------------------------------------

            const productTypes = [];
            for (const marketplace of marketplaces) {
                await this.updateMarketplaceDetails(marketplace.id);

                const response = await this.getProductTypesForMarketplace(marketplace.id);
                for (const productType of response.list) {
                    productTypes.push({
                        'id': productType.id, 'title': productType.title,
                    });
                }

                this.progressBarTik();
            }

            this.progressBarChangeStatus(M2ePro.translator.translate('Update Product Types. Please wait...'), productTypes.size());

            for (const productType of productTypes) {
                await this.updateProductType(productType.id);
                this.progressBarTik();
            }

            // ----------------------------------------
        } catch (e) {
            this.complete();
            throw e;
        }

        this.complete();

        window.location.reload();
    },

    async getMarketplaces() {
        return new Promise((resolve, reject) => {
            new Ajax.Request(this.urlForGetMarketplaces, {
                method: 'get',
                onSuccess: function (transport)    {
                    let response = transport.responseText.evalJSON();
                    resolve(response)
                }
            });
        })
    },

    async updateMarketplaceDetails(marketplaceId) {
        return new Promise((resolve, reject) => {
            new Ajax.Request(this.urlForUpdateMarketplacesDetails, {
                method: 'post',
                contentType: 'application/x-www-form-urlencoded',
                parameters: {form_key: FORM_KEY, marketplace_id: marketplaceId},
                onSuccess: function (transport)    {
                    resolve()
                }
            });
        })
    },

    async getProductTypesForMarketplace(marketplaceId) {
        return new Promise((resolve, reject) => {
            new Ajax.Request(this.urlForGetProductTypes + `marketplace_id/${marketplaceId}`, {
                method: 'get',
                onSuccess: function (transport)    {
                    let response = transport.responseText.evalJSON();
                    resolve(response)
                }
            });
        })

    },

    async updateProductType(productTypeId) {
        return new Promise((resolve, reject) => {
            new Ajax.Request(this.urlForUpdateProductType, {
                method: 'post',
                contentType: 'application/x-www-form-urlencoded',
                parameters: {form_key: FORM_KEY, id: productTypeId},
                onSuccess: function (transport)    {
                    resolve()
                }
            });
        })
    },

    // ----------------------------------------

    waiterStart: function() {
        if (this.isWaiterActive) {
            return;
        }

        $('body').trigger('processStart');
        this.isWaiterActive = true;
    },

    waiterStop: function() {
        if (!this.isWaiterActive) {
            return;
        }

        $('body').trigger('processStop');
        this.isWaiterActive = false;
    },

    complete: function() {
        this.progressBar.hide();
    //    this.waiterStop();
    },

    // ----------------------------------------

    progressBarPrepare: function() {
        this.progressBar.reset();
        this.progressBar.setTitle(M2ePro.translator.translate('Update Amazon Data'));
        this.progressBar.show();

        this.progressBarUpdate();
    },

    progressBarChangeStatus: function(title, totalItems) {
        this.progressBar.setStatus(title);
        this.totalItems = totalItems;
        this.processedItems = 0;

        this.progressBarUpdate();
    },

    progressBarTik: function() {
        this.processedItems++;
        this.progressBarUpdate();
    },

    progressBarUpdate: function() {
        this.progressBar.setPercents(this.getProcessPercent(), 0);
    },

    getProcessPercent: function() {
        return (this.processedItems / this.totalItems) * 100;
    }
});