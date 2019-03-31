LocalStorage = Class.create();
LocalStorage.prototype = {

    // ---------------------------------------

    data: {},
    M2EPRO_STORAGE_KEY: 'm2epro_data',

    // ---------------------------------------

    initialize: function()
    {
        var data = localStorage.getItem(this.M2EPRO_STORAGE_KEY);

        if (data === null) {
            localStorage.setItem(this.M2EPRO_STORAGE_KEY, JSON.stringify({}));
            return;
        }

        try {
            this.data = JSON.parse(data);
        } catch (exception) {
            localStorage.setItem(this.M2EPRO_STORAGE_KEY, JSON.stringify({}));
        }
    },

    // ---------------------------------------

    set: function(key, value)
    {
        var self = this;

        self.data[key] = value;
        return localStorage.setItem(self.M2EPRO_STORAGE_KEY, JSON.stringify(self.data));
    },

    get: function (key)
    {
        var self = this;

        if (typeof self.data[key] === 'undefined') {
            return null;
        }

        return self.data[key];
    },

    remove: function(key)
    {
        var self = this;

        if (typeof self.data[key] === 'undefined') {
            return false;
        }

        delete self.data[key];
        localStorage.setItem(self.M2EPRO_STORAGE_KEY, JSON.stringify(self.data));
        return true;
    },

    removeAllByPrefix: function(prefix)
    {
        var self = this;

        $H(self.data).each(function(item) {
            if (item.key.indexOf(prefix) === -1) {
                return;
            }

            delete self.data[item.key];
        });

        localStorage.setItem(self.M2EPRO_STORAGE_KEY, JSON.stringify(self.data));
    },

    removeAllByPostfix: function(postfix)
    {
        var self = this;

        $H(self.data).each(function(item) {
            if (item.key.indexOf(postfix) === -1) {
                return;
            }

            delete self.data[item.key];
        });

        localStorage.setItem(self.M2EPRO_STORAGE_KEY, JSON.stringify(self.data));
    },

    removeAll: function ()
    {
        var self = this;

        localStorage.setItem(self.M2EPRO_STORAGE_KEY, JSON.stringify({}));
        return true;
    }

    // ---------------------------------------
};