EbayListingProductAddHandler = Class.create(CommonHandler, {

    // ---------------------------------------

    options: {
        show_settings_step: true,
        show_settings_popup: false,
        show_autoaction_popup: false,

        get_selected_products: function(callback) {}
    },

    // ---------------------------------------

    initialize: function(options)
    {
        this.options = Object.extend(this.options,options);
    },

    // ---------------------------------------

    continue: function()
    {
        this.options.get_selected_products((function(selectedProducts) {

            if (!selectedProducts) {
                return alert(M2ePro.translator.translate('Please select the Products you want to perform the Action on.'));
            }

            if (this.options.show_autoaction_popup) {
                return this.showAutoactionPopup();
            }

            if (this.options.show_settings_popup) {
                return this.showSettingsPopup();
            }

            this.add(selectedProducts);

        }).bind(this));
    },

    // ---------------------------------------

    add: function(products)
    {
        var self = this;

        self.products = products;

        var parts = self.makeProductsParts();

        ProgressBarObj.reset();
        ProgressBarObj.setTitle('Adding Products to Listing');
        ProgressBarObj.setStatus('Adding in process. Please wait...');
        ProgressBarObj.show();
        self.scroll_page_to_top();

        $('loading-mask').setStyle({visibility: 'hidden'});
        WrapperObj.lock();

        self.sendPartsProducts(parts, parts.length);
    },

    makeProductsParts: function()
    {
        var self = this;

        var productsInPart = 50;
        var productsArray = explode(',', self.products);
        var parts = new Array();

        if (productsArray.length < productsInPart) {
            return parts[0] = productsArray;
        }

        var result = new Array();
        for (var i = 0; i < productsArray.length; i++) {
            if (result.length == 0 || result[result.length-1].length == productsInPart) {
                result[result.length] = new Array();
            }
            result[result.length-1][result[result.length-1].length] = productsArray[i];
        }

        return result;
    },

    sendPartsProducts: function(parts, partsCount)
    {
        var self = this;

        if (parts.length == 0) {
            return;
        }

        var part = parts.splice(0,1);
        part = part[0];
        var partString = implode(',',part);

        var isLastPart = '';
        if (parts.length <= 0) {
            isLastPart = 'yes';
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_productAdd/add'), {
            method: 'post',
            parameters: {
                is_last_part: isLastPart,
                products: partString
            },
            onSuccess: function(transport) {

                var percents = (100/partsCount)*(partsCount-parts.length);

                if (percents <= 0) {
                    ProgressBarObj.setPercents(0,0);
                } else if (percents >= 100) {
                    ProgressBarObj.setPercents(100,0);
                    ProgressBarObj.setStatus('Adding has been completed.');

                    var url;
                    if (self.options.show_settings_step) {
                        url = M2ePro.url.get('adminhtml_ebay_listing_productAdd', {step: 2});
                    } else {
                        url = M2ePro.url.get('adminhtml_ebay_listing_categorySettings', {step: 1});
                    }

                    setLocation(url);
                } else {
                    ProgressBarObj.setPercents(percents,1);
                }

                setTimeout(function() {
                    self.sendPartsProducts(parts, partsCount);
                }, 500);
            }
        });
    },

    // ---------------------------------------

    showAutoactionPopup: function()
    {
        var url = M2ePro.url.get('adminhtml_ebay_listing_productAdd/setAutoActionPopupShown');
        new Ajax.Request(url, {
            method:'get',
            onSuccess: function() {
                this.options.show_autoaction_popup = false;
                this.autoactionPopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Auto Add/Remove Rules'),
                    width: 430,
                    height: 200,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });
                this.autoactionPopup.options.destroyOnClose = false;
                $('modal_dialog_message').insert($('autoaction_popup_content').show());

            }.bind(this)
        });
    },

    // ---------------------------------------

    cancelAutoActionPopup: function()
    {
        this.autoactionPopup.hide();
        this.continue();
    },

    // ---------------------------------------

    showSettingsPopup: function()
    {
        this.settingsPopup = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Listing Settings Customization'),
            width: 430,
            height: 200,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        this.settingsPopup.options.destroyOnClose = false;
        $('modal_dialog_message').insert($('settings_popup_content').show());
    },

    // ---------------------------------------

    settingsPopupYesClick: function()
    {
        this.setShowSettingsStep(
            true,
            this.continue.bind(this)
        );

        this.options.show_settings_popup = false;
    },

    settingsPopupNoClick: function()
    {
        this.setShowSettingsStep(
            false,
            this.continue.bind(this)
        );

        this.options.show_settings_popup = false;
    },

    setShowSettingsStep: function(showSettingsStep,callback)
    {
        this.settingsPopup.hide();
        this.options.show_settings_step = showSettingsStep;

        var url = M2ePro.url.get('adminhtml_ebay_listing_productAdd/setShowSettingsStep', {});
        new Ajax.Request(url, {
            method: 'post',
            parameters: {
                show_settings_step: +showSettingsStep,
                remember: +$('remember_checkbox').checked
            },
            onSuccess: function() {
                callback && callback()
            }
        });
    }

    // ---------------------------------------
});