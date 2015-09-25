EbayMotorSpecificHandler = Class.create();
EbayMotorSpecificHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    listingId: null,
    specificsGridId: null,
    productsGridId: null,
    isEmptySpecificsAttribute: false,

    //----------------------------------

    initialize: function(listingId, specificsGridId, productsGridId, isEmptySpecificsAttribute)
    {
        this.listingId = listingId;
        this.specificsGridId = specificsGridId;
        this.productsGridId = productsGridId;
        this.isEmptySpecificsAttribute = isEmptySpecificsAttribute;
    },

    //----------------------------------

    initProductGrid: function()
    {
        var self = this;
        var grid = eval(self.productsGridId + 'JsObject');

        if (!grid.massaction) {
            grid.massaction = eval(self.productsGridId + '_massactionJsObject');
        }
    },

    initSpecificGrid: function()
    {
        var self = this;
        var grid = eval(self.specificsGridId + 'JsObject');

        if (!grid.massaction) {
            grid.massaction = eval(self.specificsGridId + '_massactionJsObject');
        }

        grid.massaction.updateCount = grid.massaction.updateCount.wrap(function(callOriginal) {

                callOriginal();

                $('attribute_content').value = grid.massaction.getCheckedValues()
                    .replace(/,/g, ',');

                $('attribute_content').value == ''
                    ? $('generate_attribute_content_container').hide() : $('generate_attribute_content_container').show();
            }
        );

        grid.massaction.apply = function() {
            if (this.getCheckedValues() == '') {
                alert(M2ePro.translator.translate('Please select Items.'));
                return;
            }

            var item = this.getSelectedItem();
            if (!item) {
                return;
            }

            if (item.confirm && !window.confirm(item.confirm)) {
                return;
            }

            switch (item.id) {
                case 'overwrite_attribute':
                    self.addSpecificsToProducts(true);
                    break;

                case 'add_to_attribute':
                    self.addSpecificsToProducts(false);
                    break;
            }
        };
    },

    loadSpecificsGrid: function()
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing/motorSpecificGrid'), {
            method: 'post',
            asynchronous: false,
            parameters: {},
            onSuccess: function(transport) {

                var responseText = transport.responseText.replace(/>\s+</g, '><');
                $('specifics_grid_container').update(responseText);
                setTimeout(function() {
                    self.initProductGrid();
                    self.initSpecificGrid();
                }, 150);
            }
        });
    },

    //----------------------------------

    initPopUp: function(title, popUpBlockId)
    {
        this.title = title;
        this.popUpBlockId = popUpBlockId;
        this.popUpId = 'save_to_products_pop_up';
    },

    openPopUp: function()
    {
        var self = this;

        MagentoMessageObj.clearAll();

        if (self.isEmptySpecificsAttribute) {
            MagentoMessageObj.addError(M2ePro.translator.translate('Please specify eBay Motors compatibility Attribute in %menu_label% <a target="_blank" href="%url%">General</a>'));
            return;
        }

        var isSpecificsGridExists = false;
        if ($(self.specificsGridId) != null && $('specifics_grid_container').innerHTML != '') {
            isSpecificsGridExists = true;
        }

        if (!isSpecificsGridExists) {
            self.loadSpecificsGrid();
        }

        this.popUp = Dialog.info(null, {
            id: this.popUpId,
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: self.title,
            top: 50,
            width: 1000,
            height: 550,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() { self.closeCallback(); return true; }
        });

        $('modal_dialog_message').appendChild($(self.popUpBlockId).show());

        if (isSpecificsGridExists) {
            self.initSpecificGrid();
        }
    },

    closePopUp: function()
    {
        Windows.close(this.popUpId);
    },

    closeCallback: function()
    {
        var self = this;

        $(document.body).appendChild($(this.popUpBlockId).hide());

        var specificsGrid = eval(self.specificsGridId + 'JsObject');
        specificsGrid.massaction.unselectAll();
        specificsGrid.massaction.select.value = '';

        var productsGrid = eval(self.productsGridId + 'JsObject');
        productsGrid.massaction.unselectAll();

        $('attribute_content').value = '';
    },

    //----------------------------------

    addSpecificsToProducts: function(overwrite)
    {
        var self = this;
        var specificsGrid = eval(self.specificsGridId + 'JsObject');
        var productsGrid = eval(self.productsGridId + 'JsObject');

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing/updateMotorsSpecificsAttributes'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                listing_id: this.listingId,
                listing_product_ids: EbayListingSettingsGridHandlerObj.selectedProductsIds.toString(),
                epids: specificsGrid.massaction.getCheckedValues(),
                overwrite: overwrite ? 'yes' : 'no'
            },
            onSuccess: function(transport) {

                specificsGrid.massaction.unselectAll();
                self.closePopUp();

                var response = transport.responseText.evalJSON(true);

                if (response.ok) {
                    MagentoMessageObj.addSuccess(response.message);
                    productsGrid.doFilter();
                } else {
                    MagentoMessageObj.addError(response.message);
                }
            }
        });
    }

    //----------------------------------
});