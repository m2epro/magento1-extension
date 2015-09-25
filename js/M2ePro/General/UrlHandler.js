var UrlHandler = Class.create({

    //----------------------------------

    urls: {},

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    set: function(route, url)
    {
        this.urls[route] = url;
    },

    add: function(urls)
    {
        this.urls = Object.extend(this.urls,urls);
        return this;
    },

    get: function(route,params)
    {
        params = params || {};

        if (!this.urls[route]) {
            return alert('Route "' + route +'" not found');
        }

        var returnUrl = this.urls[route];

        for (var key in params) {
            if (!params.hasOwnProperty(key)) {
                continue
            }
            returnUrl += key + '/' + params[key] + '/';
        }

        return returnUrl;
    }

    //----------------------------------
});