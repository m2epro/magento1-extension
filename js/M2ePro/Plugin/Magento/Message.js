MagentoMessage = Class.create();
MagentoMessage.prototype = {

    // --------------------------------

    initialize: function() {},

    // --------------------------------

    show: function()
    {
        $('messages').show();
    },

    hide: function()
    {
        $('messages').hide();
    },

    // --------------------------------

    add: function(message, type, id)
    {
        type = this.prepareType(type);
        id = id || Math.floor(Math.random()*1000);

        id = 'mage-' + type + '-message-' + id;

        if ($$('div#messages ul.messages').length == 0) {
            $('messages').innerHTML = '<ul class="messages"></ul>';
        }

        var cssClass = this.getClassForLiByType(type);

        if ($$('div#messages ul.messages li.' + cssClass).length == 0) {
            $$('div#messages ul.messages')[0].innerHTML += '<li class="' + cssClass + '"><ul></ul></li>';
        }

        var container = $$('div#messages ul.messages li.' + cssClass + ' ul')[0];

        container.innerHTML += '<li id="' + id + '">' + message + '</li>';
    },

    addSuccess: function(message, id)
    {
        this.add(message,'success',id);
    },

    addNotice: function(message, id)
    {
        this.add(message,'notice',id);
    },

    addWarning: function(message, id)
    {
        this.add(message,'warning',id);
    },

    addError: function(message, id)
    {
        this.add(message,'error',id);
    },

    // --------------------------------

    remove: function(type, id)
    {
        type = this.prepareType(type);
        id = id || '';

        if (id == '') {
            return false;
        }

        id = 'mage-' + type + '-message-' + id;

        var cssClass = this.getClassForLiByType(type);

        if ($$('div#messages ul.messages li.'+cssClass+' ul li#'+id).length == 0) {
            return false;
        }

        $$('div#messages ul.messages li.'+cssClass+' ul li#'+id)[0].remove();

        if ($$('div#messages ul.messages li.'+cssClass+' ul li').length == 0) {
            $$('div#messages ul.messages li.'+cssClass)[0].remove();
        }

        if ($$('div#messages ul.messages li').length == 0) {
            $$('div#messages ul.messages')[0].remove();
        }

        return true;
    },

    removeSuccess: function(id)
    {
        this.remove('success',id);
    },

    removeWarning: function(id)
    {
        this.remove('warning',id);
    },

    removeError: function(id)
    {
        this.remove('error',id);
    },

    // --------------------------------

    clear: function(type)
    {
        type = this.prepareType(type);
        var cssClass = this.getClassForLiByType(type);

        if ($$('div#messages ul.messages li.'+cssClass).length == 0) {
            return false;
        }

        $$('div#messages ul.messages li.'+cssClass)[0].remove();

        if ($$('div#messages ul.messages li').length == 0) {
            $$('div#messages ul.messages')[0].remove();
        }

        return true;
    },

    clearSuccess: function()
    {
        this.clear('success');
    },

    clearWarning: function()
    {
        this.clear('warning');
    },

    clearError: function()
    {
        this.clear('error');
    },

    clearAll: function()
    {
        this.clear('error');
        this.clear('warning');
        this.clear('notice');
        this.clear('success');
    },

    // --------------------------------

    prepareType: function(type)
    {
        type = type || 'success';
        if (type == 'error' || type == 'warning' || type == 'success' || type == 'notice') {
            return type;
        }

        return 'success';
    },

    getClassForLiByType: function(type)
    {
        switch (this.prepareType(type))
        {
            case 'error': return 'error-msg';
            case 'warning': return 'warning-msg';
            case 'notice': return 'notice-msg';
            case 'success': return 'success-msg';
        }
    }

    // --------------------------------
}