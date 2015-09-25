EbayOrderMigrationToV611Handler = Class.create();
EbayOrderMigrationToV611Handler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    orderCountPerAjaxRequest: 0,
    notMigratedOrdersCount: 0,
    currentRequest: 1,
    progressBarObj: null,

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    setProgressBarObj: function(progressBarObj)
    {
        this.progressBarObj = progressBarObj;
    },

    setOrdersCountPerAjaxRequest: function(ordersCount)
    {
        this.orderCountPerAjaxRequest = ordersCount;
    },

    setNotMigratedOrdersCount: function(ordersCount)
    {
        this.notMigratedOrdersCount = ordersCount;
    },

    //----------------------------------

    runMigration: function()
    {
        var self = EbayOrderMigrationToV611HandlerObj;

        $('run_migration_button').hide();

        var requestsCount = parseInt(self.notMigratedOrdersCount / self.orderCountPerAjaxRequest);
        if (this.notMigratedOrdersCount != requestsCount * self.orderCountPerAjaxRequest) {
            requestsCount += 1;
        }

        self.progressBarObj.reset();
        self.progressBarObj.setTitle(M2ePro.translator.translate('Orders migration'));
        self.progressBarObj.setStatus(M2ePro.translator.translate('Starting orders migration...'));
        self.progressBarObj.show();

        $('loading-mask').setStyle({visibility: 'hidden'});

        self.sendAjaxRequest(requestsCount, 1);
    },

    sendAjaxRequest: function(requestsCount, currentRequest)
    {
        var self = EbayOrderMigrationToV611HandlerObj;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_order/migrateOrdersPackToV611'), {
            parameters: {
                orders_count: self.orderCountPerAjaxRequest
            },
            onSuccess: function(transport) {

                var percents = (100/requestsCount)*currentRequest;

                if (percents <= 0) {
                    self.progressBarObj.setPercents(0,0);
                } else if (percents >= 100) {
                    self.progressBarObj.setPercents(100,0);
                    self.progressBarObj.setStatus(M2ePro.translator.translate('Orders migration is finished'));

                    setTimeout(function() {
                        setLocation(M2ePro.url.get('adminhtml_ebay_order/index'));
                    }, 2000);

                    return;
                } else {
                    self.progressBarObj.setStatus(M2ePro.translator.translate('Orders migration is in process...'));
                    self.progressBarObj.setPercents(percents,1);
                }

                self.sendAjaxRequest(requestsCount, currentRequest + 1);
            }
        });
    }

    //----------------------------------
});