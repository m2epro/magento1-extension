EbayListingCategorySpecificWrapperHandler = Class.create(CommonHandler, {

    // ---------------------------------------

    initialize: function(currentCategory,wrapperObj)
    {
        this.wrapperObj = wrapperObj;
        this.loadingMask = $('loading-mask');

        this.setCurrentCategory(currentCategory);
    },

    // ---------------------------------------

    refreshButtons: function()
    {
        $$('button.specifics_buttons').invoke('hide');

        if (this.getNextCategory()) {

            $$('button.next_category_button').invoke('show');

        } else {

            $$('button.continue').invoke('show');

        }
    },

    // ---------------------------------------

    setCurrentCategory: function(category)
    {
        $('categories_list').select('li').each(function(li) {
            li.removeClassName('selected');
        });

        this.currentCategory = category;
        $(category).addClassName('selected');

        this.refreshButtons();
    },

    getCurrentCategory: function()
    {
        return this.currentCategory;
    },

    getNextCategory: function()
    {
        var nextLi = $(this.currentCategory).next('li');

        if (!nextLi) {
            return false;
        }

        return nextLi.id;
    },

    getPrevCategory: function()
    {
        var prevLi = $(this.currentCategory).previous('li');

        if (!prevLi) {
            return false;
        }

        return prevLi.id;
    },

    // ---------------------------------------

    renderPrevCategory: function()
    {
        if (!this.getPrevCategory()) {
            var url = M2ePro.url.get('adminhtml_ebay_listing_categorySettings');
            setLocation(url);
            return;
        }

        this.getSpecificsData(this.getPrevCategory(), function(transport) {

            var response = transport.responseText.evalJSON();

            try {
                $('specifics_main_container').innerHTML = response.text;
                $('specifics_main_container').innerHTML.evalScripts();
            } catch (e) {}

        });
    },

    renderNextCategory: function()
    {
        if (!EbayListingCategorySpecificHandlerObj.validate()) {
            return;
        }

        if (!!$('skip_optional_specifics').checked) {
            this.lock();
        }

        if (!this.getNextCategory()) {
            this.unlock();
            return this.showPopup();
        }

        this.saveCategory(function() {
            this.getSpecificsData(this.getNextCategory(), function(transport) {
                var response = transport.responseText.evalJSON();

                try {
                    $('specifics_main_container').innerHTML = response.text;
                    $('specifics_main_container').innerHTML.evalScripts();
                } catch (e) {}

                if (!response.hasRequiredSpecifics && !!$('skip_optional_specifics').checked) {
                    this.renderNextCategory();
                } else {
                    this.unlock();
                }

            })
        }.bind(this));
    },

    // ---------------------------------------

    getSpecificsData: function(category, callback)
    {
        var url = M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepThreeGetCategorySpecifics');
        new Ajax.Request(url, {
            method: 'get',
            parameters: {
                category: category
            },
            onSuccess: function(transport) {
                this.setCurrentCategory(category);
                callback && callback.call(this, transport);
            }.bind(this)
        });
    },

    // ---------------------------------------

    saveCategory: function(callback)
    {
        if (!EbayListingCategorySpecificHandlerObj.validate()) {
            return;
        }

        var url = M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepThreeSaveCategorySpecificsToSession');
        new Ajax.Request(url, {
            method: 'post',
            parameters: {
                category: this.getCurrentCategory(),
                data: Object.toJSON(EbayListingCategorySpecificHandlerObj.getInternalData())
            },
            onSuccess: function(transport) {
                callback.call(this);
            }.bind(this)
        });
    },

    // ---------------------------------------

    save: function()
    {
        if (this.getNextCategory()) {
            return;
        }

        this.saveCategory(function() {

            var url = M2ePro.url.get('adminhtml_ebay_listing_categorySettings/save');

            new Ajax.Request(url, {
                method: 'post',
                onSuccess: function(transport) {
                    setLocation(M2ePro.url.get('adminhtml_ebay_listing/review'))
                }
            });
        });
    },

    // ---------------------------------------

    showPopup: function()
    {
        this.popup = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: 'Set Item Specifics',
            width: 430,
            height: 250,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        this.popup.options.destroyOnClose = false;
        $('modal_dialog_message').insert($('popup_content').show());
    },

    hidePopup: function()
    {
        this.popup.hide();
    },

    // ---------------------------------------

    lock: function()
    {
        $(this.wrapperObj.wrapperId).visible() || this.wrapperObj.lock();

        this.loadingMask.setStyle({visibility: 'hidden'});
        $(this.wrapperObj.wrapperId).update(
            '<div style="height: 46%"></div>' +
            '<div>'+M2ePro.translator.translate('Loading. Please wait')+' ...</div>'
        );
    },

    unlock: function()
    {
        this.wrapperObj.unlock();
        this.loadingMask.setStyle({visibility: 'visible'});
    }

    // ---------------------------------------
});