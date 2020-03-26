SynchronizationHandler = Class.create();
SynchronizationHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    initialize: function(synchProgressObj)
    {
        this.synchProgressObj = synchProgressObj;
    },

    // ---------------------------------------

    completeStep: function()
    {
        window.opener.completeStep = 1;
        window.close();
    },

    saveSettings: function(runSynch, components)
    {
        MagentoMessageObj.clearAll();
        runSynch  = runSynch  || '';
        components = components || '';

        components = Object.isString(components)
                     ? [components]
                     : components;
        components = Object.toJSON(components);

        CommonHandlerObj.scroll_page_to_top();

        var self = this;
        new Ajax.Request(M2ePro.url.get('formSubmit', $('edit_form').serialize(true)), {
            method: 'get',
            parameters: {components: components},
            asynchronous: true,
            onSuccess: function(transport) {
                MagentoMessageObj.addSuccess(M2ePro.translator.translate('Synchronization Settings have been saved.'));
                if (runSynch != '') {
                    eval('self.'+runSynch+'(components);');
                }
            }
        });
    },

    // ---------------------------------------

    moveChildBlockContent: function(childBlockId, destinationBlockId)
    {
        if (childBlockId == '' || destinationBlockId == '') {
            return;
        }

        $(destinationBlockId).appendChild($(childBlockId));
    }

    // ---------------------------------------
});