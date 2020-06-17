window.AmazonListingCreateGeneralMarketplaceSynchProgress = Class.create(SynchProgress, {

        // ---------------------------------------

        end: function ($super)
        {
            $super();

            var self = this;
            if (self.result == self.resultTypeError) {
                self.printFinalMessage();
                CommonObj.scroll_page_to_top();
                return;
            }

            this.save_click(M2ePro.url.get('adminhtml_amazon_listing_create/index'), true)
        }

        // ---------------------------------------
});
