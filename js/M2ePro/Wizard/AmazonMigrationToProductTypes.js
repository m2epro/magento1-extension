window.WizardAmazonMigrationToProductTypes = Class.create(Common, {

    initialize: function(proceedLink)
    {
        this.proceedLink = proceedLink;
    },

    proceed: function ()
    {
        new Ajax.Request(this.proceedLink, {
            method: 'post',
            asynchronous: true,
            parameters: [],
            onSuccess: function(transport) {
                MessageObj.clear();
                var response = transport.responseText.evalJSON();

                if (response && !response['success'] && response['message']) {
                    MessageObj.addError(response['message']);

                    return CommonObj.scroll_page_to_top();
                }

                if (!response['url']) {
                    MessageObj.addError(
                        M2ePro.translator.translate('An error during of marketplace synchronization.')
                    );

                    return CommonObj.scroll_page_to_top();
                }

                return setLocation(response['url']);
            }
        });
    }
});