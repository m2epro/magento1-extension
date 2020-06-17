window.EbayMotorAddFilterGrid = Class.create(Grid, {

    filtersConditions: {},

    //----------------------------------

    initialize: function($super,gridId)
    {
        $super(gridId);
    },

    //##################################

    prepareActions: function()
    {
        this.actions = {
            selectAction: this.selectFilters.bind(this),
            setNoteAction: this.setNote.bind(this),
            resetNoteAction: this.resetNote.bind(this),
            saveAsGroupAction: this.saveAsGroup.bind(this),
            removeFilterAction: this.removeFilter.bind(this)
        };
    },

    //##################################

    selectFilters: function()
    {
        var self = this,
            filters = self.getGridMassActionObj().checkedString.split(',');

        filters.each(function(filter){

            for(var i = 0; i < EbayMotorsObj.selectedData.filters.length; i++) {
                if (EbayMotorsObj.selectedData.filters[i] == filter) {
                    return;
                }
            }

            EbayMotorsObj.selectedData.filters.push(filter);
        });

        self.unselectAll();
        EbayMotorsObj.updateSelectedData();
    },

    //----------------------------------

    setNote: function()
    {
        var self = this,
            popUpContent;

        this.notePopup = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Set Note'),
            top: 70,
            width: 470,
            height: 250,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        self.notePopup.options.destroyOnClose = true;

        popUpContent = $('modal_dialog_message');
        popUpContent.insert(EbayMotorsObj.setNotePopupHtml);

        popUpContent.down('.save-btn').observe('click', function () {

            if (!self.validatePopupForm()) {
                return;
            }

            var data = $('set_note_form').serialize(true);
            data['filters_ids'] = self.getGridMassActionObj().checkedString;

            new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/setNoteToFilters'), {
                method: 'post',
                parameters: data,
                onSuccess: function(transport) {

                    if (transport.responseText == '0') {
                        self.unselectAllAndReload();
                    }

                    self.notePopup.close();
                }
            });
        });

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '630px';
        }, 50);
    },

    resetNote: function()
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/setNoteToFilters'), {
            method: 'post',
            parameters: {
                filters_ids: self.getGridMassActionObj().checkedString,
                note: ''
            },
            onSuccess: function(transport) {

                if (transport.responseText == '0') {
                    self.unselectAllAndReload();
                }

                self.notePopup.close();
            }
        });
    },

    //----------------------------------

    saveAsGroup: function()
    {
        var self = this,
            popUpContent;

        this.groupPopup = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Save as Group'),
            top: 70,
            width: 470,
            height: 250,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        self.groupPopup.options.destroyOnClose = true;

        popUpContent = $('modal_dialog_message');
        popUpContent.insert(EbayMotorsObj.saveAsGroupPopupHtml);

        popUpContent.down('.save-btn').observe('click', function () {

            if (!self.validatePopupForm()) {
                return;
            }

            var data = $('save_as_group_form').serialize(true);
            data.items = self.getGridMassActionObj().checkedString;
            data.type = EbayMotorsObj.motorsType;
            data.mode = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Motor_Group::MODE_FILTER');

            new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/saveAsGroup'), {
                method: 'post',
                parameters: data,
                onSuccess: function(transport) {

                    if (transport.responseText == '0') {
                        self.unselectAll();
                        $(self.getGridMassActionObj().select).value = '';

                        EbayMotorAddGroupGridObj.unselectAllAndReload();
                    }

                    self.groupPopup.close();
                }
            });
        });

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '630px';
        }, 50);
    },

    //----------------------------------

    removeFilter: function()
    {
        var self = this,
            filters = self.getGridMassActionObj().checkedString.split(',');

        filters.each(function(filter){

            var index = EbayMotorsObj.selectedData.filters.indexOf(filter);

            if (index > -1) {
                EbayMotorsObj.selectedData.filters.splice(index, 1);
            }
        });

        EbayMotorsObj.updateSelectedData();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/removeFilter'), {
            method: 'post',
            parameters: {
                filters_ids: self.getGridMassActionObj().checkedString
            },
            onSuccess: function(transport) {

                if (transport.responseText == '0') {
                    self.unselectAllAndReload();
                    EbayMotorAddGroupGridObj.unselectAllAndReload();
                }
            }
        });
    },

    //##################################

    showFilterResult: function(filterId)
    {
        ebayMotorAddTabsJsTabs.showTabContent(ebayMotorAddTabsJsTabs.tabs[0]);
        EbayMotorAddItemGridObj.showFilterResult(this.filtersConditions[filterId]);
    },

    //##################################

    validatePopupForm: function () {
        var self = this,
            result = true;

        Windows.getFocusedWindow().element.down('form').getElements().each(function(el){
            el.classNames().each(function (className) {
                var validationResult = Validation.test(className, el);
                result = validationResult ? result : false;

                if (!validationResult) {
                    throw $break;
                }
            });
        });

        return result;
    }

    //##################################

});