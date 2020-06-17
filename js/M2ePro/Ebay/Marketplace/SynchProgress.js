window.EbayMarketplaceSynchProgress = Class.create(SynchProgress, {

    // ---------------------------------------

    printFinalMessage: function($super)
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_marketplace/isExistDeletedCategories'), {
            method: 'post',
            asynchronous: true,
            onSuccess: function(transport) {

                if (transport.responseText == 1) {
                    MessageObj.addWarning(str_replace(
                        '%url%',
                        M2ePro.url.get('adminhtml_ebay_category/index', {filter: base64_encode('state=0')}),
                        M2ePro.translator.translate('Some eBay Categories were deleted from eBay. Click <a target="_blank" href="%url%">here</a> to check.')
                    ));
                }

                $super();
            }
        });
    }

    // ---------------------------------------
});
