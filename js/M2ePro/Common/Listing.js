CommonListing = Class.create();
CommonListing.prototype = {

    // ---------------------------------------

    initialize: function(tabsComponent) {
        this.tabsComponent = tabsComponent;

        this.init3rdPartyControlVisibility();
    },

    // ---------------------------------------

    init3rdPartyControlVisibility: function()
    {
        if (typeof window['change3rdPartyVisibility'] != 'function') {
            return;
        }

        this.tabsComponent.tabs.forEach(function(tab) {
            tab.observe('click', change3rdPartyVisibility);
        });

        this.tabsComponent.activeTab.simulate('click');
    },

    // ---------------------------------------

    getActiveTab: function() {
        var activeTabId = this.tabsComponent.activeTab.id;
        return activeTabId.replace(this.tabsComponent.containerId + '_', '');
    },

    createListing: function(url) {
        setLocation(url + 'component/' + this.getActiveTab());
    },

    viewLogs: function(url) {
        window.open(url + 'channel/' + this.getActiveTab())
    }
};
