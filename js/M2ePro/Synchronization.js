window.Synchronization = Class.create(Common, {

    // ---------------------------------------

    saveSettings: function()
    {
        MessageObj.clearAll();
        CommonObj.scroll_page_to_top();

        new Ajax.Request(M2ePro.url.get('formSubmit', $('edit_form').serialize(true)), {
            method: 'get',
            asynchronous: true,
            onSuccess: function(transport) {
                MessageObj.addSuccess(M2ePro.translator.translate('Synchronization Settings have been saved.'));
            }
        });
    }

    // ---------------------------------------
});
