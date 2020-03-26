SynchProgressHandler = Class.create();
SynchProgressHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    initialize: function(progressBarObj, wrapperObj)
    {
        this.stateExecuting = 'executing';
        this.stateInactive = 'inactive';

        this.resultTypeError = 'error';
        this.resultTypeWarning = 'warning';
        this.resultTypeSuccess = 'success';

        this.progressBarObj = progressBarObj;
        this.wrapperObj = wrapperObj;
        this.loadingMask = $('loading-mask');
    },

    // ---------------------------------------

    start: function(title, status)
    {
        title = title || '';
        status = status || '';

        var self = this;

        self.progressBarObj.reset();

        if (title != '') {
            self.progressBarObj.setTitle(title);
        }
        if (status != '') {
            self.progressBarObj.setStatus(status);
        }

        self.progressBarObj.show();

        self.wrapperObj.lock();
        self.loadingMask.setStyle({visibility: 'hidden'});
    },

    end: function()
    {
        var self = this;

        self.progressBarObj.reset();
        self.progressBarObj.hide();

        self.wrapperObj.unlock();
        self.loadingMask.setStyle({visibility: 'visible'});
    },

    // ---------------------------------------

    runTask: function(title, url, components, callBackWhenEnd)
    {
        title = title || '';
        url = url || '';
        components = components || '';
        callBackWhenEnd = callBackWhenEnd || '';

        if (url == '') {
            return;
        }

        var self = this;

        self.start(title, M2ePro.translator.translate('Preparing to start. Please wait ...'));

        new Ajax.Request(url, {
            parameters: {components: components},
            method: 'get',
            asynchronous: true
        });

        setTimeout(function() {
            self.startGetExecutingInfo(callBackWhenEnd);
        },2000);
    },

    // ---------------------------------------

    printFinalMessage: function(resultType)
    {
        var self = this;
        if (resultType == self.resultTypeError) {
            MagentoMessageObj.addError(str_replace(
                '%url%',
                M2ePro.url.get('logViewUrl'),
                M2ePro.translator.translate('Synchronization ended with errors. <a target="_blank" href="%url%">View Log</a> for details.')
            ));
        } else if (resultType == self.resultTypeWarning) {
            MagentoMessageObj.addWarning(str_replace(
                '%url%',
                M2ePro.url.get('logViewUrl'),
                M2ePro.translator.translate('Synchronization ended with warnings. <a target="_blank" href="%url%">View Log</a> for details.')
            ));
        } else {
            MagentoMessageObj.addSuccess(M2ePro.translator.translate('Synchronization has successfully ended.'));
        }
    },

    // ---------------------------------------
});