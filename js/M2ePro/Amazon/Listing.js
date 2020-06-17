window.AmazonListing = Class.create({

    // ---------------------------------------

    initialize: function() {
        this.init3rdPartyControlVisibility();
    },

    // ---------------------------------------

    init3rdPartyControlVisibility: function()
    {
        if (typeof window['change3rdPartyVisibility'] != 'function') {
            return;
        }
    },

    // ---------------------------------------

    createListing: function(url) {
        setLocation(url + 'component/amazon');
    },

    viewLogs: function(url) {
        window.open(url)
    }
});
