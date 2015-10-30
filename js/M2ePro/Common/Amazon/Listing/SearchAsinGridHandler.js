CommonAmazonListingSearchAsinGridHandler = Class.create(CommonListingGridHandler, {

    // ---------------------------------------

    getComponent: function()
    {
        return 'amazon';
    },

    // ---------------------------------------

    getMaxProductsInPart: function()
    {
        return 1000;
    },

    // ---------------------------------------

    prepareActions: function($super)
    {
        $super();
        this.actionHandler = new CommonAmazonListingActionHandler(this);
        this.productSearchHandler = new CommonAmazonListingProductSearchHandler(this);

        this.actions = Object.extend(this.actions, {

            assignGeneralIdAction: (function() { this.productSearchHandler.searchGeneralIdAuto(this.getSelectedProductsString())}).bind(this),
            unassignGeneralIdAction: (function() { this.productSearchHandler.unmapFromGeneralId(this.getSelectedProductsString())}).bind(this)

        });

        this.productSearchHandler.clearSearchResultsAndOpenSearchMenu = function() {
            var self = this;

            if (confirm(self.options.text.confirm)) {
                popUp.close();
                self.unmapFromGeneralId(self.params.productId);
            }
        };
    },

    // ---------------------------------------

    parseResponse: function(response)
    {
        if (!response.responseText.isJSON()) {
            return;
        }

        return response.responseText.evalJSON();
    },

    // ---------------------------------------

    afterInitPage: function($super)
    {
        $super();
    },

    editSearchSettings: function(title, listingId)
    {
        var self = this;

        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.viewSearchSettings, {
            method: 'post',
            parameters: {
                id: listingId
            },
            onSuccess: function(transport) {
                searchSettnigsPopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: title,
                    top: 30,
                    height: 630,
                    width: 800,
                    zIndex: 100,
                    recenterAuto: true,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });
                searchSettnigsPopup.options.destroyOnClose = true;

                searchSettnigsPopup.listingId = listingId;

                $('modal_dialog_message').setStyle({
                    padding: '10px'
                });

                $('modal_dialog_message').insert(transport.responseText);

                self.searchSettingsForm = new varienForm('search_settings_form', '');

                setTimeout(function() {
                    Windows.getFocusedWindow().content.style.height = '';
                    Windows.getFocusedWindow().content.style.maxHeight = '630px';
                }, 50);
            }
        });
    },

    saveSearchSettings: function()
    {
        var self = this,
            data;

        if (self.searchSettingsForm && !self.searchSettingsForm.validate()) {
            return;
        }

        data = $('search_settings_form').serialize(true);
        data.id = searchSettnigsPopup.listingId;

        new Ajax.Request(M2ePro.url.saveSearchSettings, {
            method: 'post',
            parameters: data,
            onSuccess: function(transport) {
                self.actionHandler.gridHandler.unselectAllAndReload();
                searchSettnigsPopup.close();
            }
        });
    },

    closeSearchSettings: function()
    {
        searchSettnigsPopup.close();
    },

    checkSearchResults: function(listingId)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.checkSearchResults, {
            method: 'post',
            parameters: {
                id: listingId
            },
            onSuccess: function(transport) {
                var response = self.parseResponse(transport);

                if (response.redirect) {
                    return setLocation(response.redirect);
                }

                newAsinPopup = Dialog.info(response.data, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.text.new_asin_popup_title,
                    top: 30,
                    height: 230,
                    width: 500,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });
                newAsinPopup.options.destroyOnClose = true;

                newAsinPopup.listingId = listingId;

                $('modal_dialog_message').setStyle({
                    padding: '10px'
                });
            }
        });
    },

    newAsinPopupYesClick: function() {
        this.showNewAsinPopup(1);
    },

    noAsinPopupNoClick: function() {
        this.showNewAsinPopup(0);
    },

    showNewAsinPopup: function(showNewAsinStep)
    {
        var self = this,
            remember = $('asin_search_new_asin_remember_checkbox').checked;

        newAsinPopup.close();

        new Ajax.Request(M2ePro.url.showNewAsinStep, {
            method: 'post',
            parameters: {
                show_new_asin_step: + showNewAsinStep,
                remember: + remember
            },
            onSuccess: function(transport)
            {
                var response = self.parseResponse(transport);

                if (response.redirect) {
                    return setLocation(response.redirect);
                }
            }
        });
    },

    // ---------------------------------------

    showNotCompletedPopup: function()
    {
        var self = this;

        notCompletedPopup = Dialog.info($('asin_search_not_completed_popup').innerHTML, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.text.not_completed_popup_title,
            height: 230,
            width: 500,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        notCompletedPopup.options.destroyOnClose = true;

        $('modal_dialog_message').setStyle({
            padding: '10px'
        });
    }

    // ---------------------------------------
});