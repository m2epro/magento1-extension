window.AmazonProductTypeTabs = Class.create(Common, {
    tabsContainer: null,
    contentContainer: null,

    tabTemplate: '',
    contentTemplate: '',

    initialize: function () {
        this.tabsContainer = $$('#amazonProductTypeEditTabs')[0];
        this.contentContainer = $('edit_form');

        this.tabTemplate = $('amazonProductTypeEditTabs_template').up('li').outerHTML;
        this.contentTemplate = $('amazonProductTypeEditTabs_template_content').outerHTML;
    },

    insertTab: function (nick, title) {
        const tabId = 'amazonProductTypeEditTabs_' + nick;
        if ($(tabId)) {
            return;
        }

        var temp = new Element('div');
        temp.innerHTML = this.tabTemplate
            .replaceAll('template', nick)
            .replaceAll('%title%', title);
        const tab = temp.getElementsByTagName('li')[0];
        tab.style.display = 'block';

        temp = new Element('div');
        temp.innerHTML = this.contentTemplate
            .replaceAll('template', nick);
        const content = temp.getElementsByTagName('div')[0];
        content.statusBar = tab.firstElementChild;

        this.tabsContainer.appendChild(tab);
        this.contentContainer.appendChild(content);
    },

    refreshTabs: function () {
        amazonProductTypeEditTabsJsTabs.tabs = $$(
            '#' + amazonProductTypeEditTabsJsTabs.containerId + ' li a.tab-item-link'
        ).filter(tab => tab.id != 'amazonProductTypeEditTabs_template');

        amazonProductTypeEditTabsJsTabs.tabs.map(tab => {
            Event.observe(
                tab,
                'click',
                amazonProductTypeEditTabsJsTabs.tabOnClick
            );

            amazonProductTypeEditTabsJsTabs.moveTabContentInDest()
        });
    },

    resetTabs: function (tabs) {
        for (var i = 0; i < tabs.length; i++) {
            if (tabs[i] !== 'general') {
                this.removeTab(tabs[i]);
            }
        }
    },

    removeTab: function (nick) {
        const tab = $('amazonProductTypeEditTabs_' + nick);
        if (tab !== null) {
            tab.remove();
        }

        const tabContent = $('amazonProductTypeEditTabs_' + nick + '_content');
        if (tabContent !== null) {
            tabContent.remove();
        }
    },

    addTabContent: function (tabNick, element) {
        $$('#amazonProductTypeEditTabs_' + tabNick + '_content > div')[0].appendChild(element);
    }
});