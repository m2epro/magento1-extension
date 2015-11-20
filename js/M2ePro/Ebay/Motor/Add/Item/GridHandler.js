EbayMotorAddItemGridHandler = Class.create(GridHandler, {

    savedNotes: {},

    //----------------------------------

    initialize: function($super,gridId)
    {
        $super(gridId);

        this.savedNotes = {};

        this.filterPopupHtml = $('save_items_filter_popup').innerHTML;
        $('save_items_filter_popup').remove();
    },

    //##################################

    prepareActions: function()
    {
        this.actions = {
            selectAction: this.selectItems.bind(this),
            setNoteAction: this.setNote.bind(this),
            resetNoteAction: this.resetNote.bind(this),
            saveAsGroupAction: this.saveAsGroup.bind(this)
        };
    },

    afterInitPage: function()
    {
        var self = this;

        GridHandler.prototype.afterInitPage.call(this);

        $(self.gridId).down('.filter').on('change', function (e) {

            self.checkFilterValues();

        });

        $(self.gridId).down('.filter').on('keyup', function (e) {

            self.checkFilterValues();

        });

        self.checkFilterValues();

        $H(self.savedNotes).each(function(note) {

            var noteEl = $('note_' + note.key);

            if (noteEl && note.value != '') {
                noteEl.show();
                noteEl.down('.note-view').innerHTML = note.value;
            }
        });

    },

    checkFilterValues: function()
    {
        var self = this;

        $('save_filter_btn').addClassName('disabled');

        $(self.gridId).down('.filter').select('select', 'input').each(function(el){
            if (el.name == 'massaction') {
                return;
            }

            if (el.value != '') {
                $('save_filter_btn').removeClassName('disabled');
                throw $break;
            }
        });
    },

    //##################################

    selectItems: function()
    {
        var self = this,
            items = self.getGridMassActionObj().checkedString.split(',');

        items.each(function(item){

            for(var i = 0; i < EbayMotorsHandlerObj.selectedData.items.length; i++) {
                if (EbayMotorsHandlerObj.selectedData.items[i] == item) {
                    return;
                }
            }

            EbayMotorsHandlerObj.selectedData.items.push(item);
        });

        self.unselectAll();
        EbayMotorsHandlerObj.updateSelectedData();
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
        popUpContent.insert(EbayMotorsHandlerObj.setNotePopupHtml);

        popUpContent.down('.save-btn').observe('click', function () {

            if (!self.validatePopupForm()) {
                return;
            }

            var note = $('set_note_form').down('[name=note]').value;

            note = note.trim();

            self.getGridObj().massaction.getCheckedValues().split(',').each(function(id) {

                self.savedNotes[id] = note;

                var noteEl = $('note_' + id);

                if (noteEl) {
                    noteEl.hide();

                    if (note != '') {
                        noteEl.show();
                        noteEl.down('.note-view').innerHTML = note;
                    }
                }
            });

            $(self.getGridObj().massaction.select).value = '';
            self.notePopup.close();
        });

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '630px';
        }, 50);
    },

    resetNote: function()
    {
        var self = this;

        self.getGridObj().massaction.getCheckedValues().split(',').each(function(id) {

            self.savedNotes[id] = '';

            var noteEl = $('note_' + id);

            if (noteEl) {
                noteEl.hide();
                noteEl.down('.note-view').innerHTML = '';
            }
        });

        $(self.getGridObj().massaction.select).value = '';
        self.unselectAll();
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
        popUpContent.insert(EbayMotorsHandlerObj.saveAsGroupPopupHtml);

        popUpContent.down('.save-btn').observe('click', function () {

            if (!self.validatePopupForm()) {
                return;
            }

            var data = $('save_as_group_form').serialize(true);
            data.items = self.getGridMassActionObj().checkedString.split(',');
            data.type = EbayMotorsHandlerObj.motorsType;
            data.mode = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Motor_Group::MODE_ITEM');

            var items = {};
            data.items.each(function(item){
                items[item] = self.savedNotes[item];
            });

            data.items = Object.toQueryString(items);

            new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/saveAsGroup'), {
                postmethod: 'post',
                parameters: data,
                onSuccess: function(transport) {

                    if (transport.responseText == '0') {
                        self.unselectAll();
                        $(self.getGridObj().massaction.select).value = '';

                        EbayMotorAddGroupGridHandlerObj.unselectAllAndReload();
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

    //##################################

    saveFilter: function()
    {
        var self = this,
            popUpContent;

        if ($('save_filter_btn').hasClassName('disabled')) {
            return;
        }

        this.filterPopup = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Save Filter'),
            top: 70,
            width: 470,
            height: 250,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        self.filterPopup.options.destroyOnClose = true;

        popUpContent = $('modal_dialog_message');
        popUpContent.insert(self.filterPopupHtml);

        var conditionsEl = popUpContent.down('.filter_conditions');
        var conditionsData = {};
        $(self.gridId).down('.filter').select('select', 'input').each(function(el){

            if (el.name == 'massaction') {
                return;
            }

            if (el.value != '') {
                var li = new Element('li'),
                    valueText = '',
                    valueName = el.name.capitalize().replace('_', ' ');

                if (el.name == 'product_type') {
                    valueText =  el[el.selectedIndex].text;
                    valueName = M2ePro.translator.translate('Type');
                } else {
                    valueText = el.value;
                }

                if (el.name == 'epid') {
                    valueName = M2ePro.translator.translate('ePID');
                }

                if (el.name == 'ktype') {
                    valueName = M2ePro.translator.translate('kType');
                }

                if (el.name == 'body_style') {
                    valueName = M2ePro.translator.translate('Body Style');
                }

                if (el.name == 'year[from]') {
                    valueName = M2ePro.translator.translate('Year From');
                }

                if (el.name == 'year[to]') {
                    valueName = M2ePro.translator.translate('Year To');
                }

                li.update('<b>' + valueName + '</b>: ' + valueText);

                conditionsData[el.name] = el.value;

                conditionsEl.insert({bottom: li});
            }
        });

        popUpContent.down('.save-btn').observe('click', function () {

            if (!self.validatePopupForm()) {
                return;
            }

            var data = $('save_items_filter_form').serialize(true);
            data.conditions = Form.serialize($(self.gridId).down('.filter'));
            data.type = EbayMotorsHandlerObj.motorsType;

            new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/saveFilter'), {
             postmethod: 'post',
                parameters: data,
                onSuccess: function(transport) {

                    if (transport.responseText == '0') {
                        $(self.getGridObj().massaction.select).value = '';

                        EbayMotorAddFilterGridHandlerObj.unselectAllAndReload();
                    }

                    self.filterPopup.close();
                }
            });
        });

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '630px';
        }, 50);
    },

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
    },

    //##################################

    showFilterResult: function(comnditions)
    {
        var self = this;

        $(self.gridId).down('.filter').select('input', 'select').each(function(el) {
            el.value = '';
        });

        $H(comnditions).each(function (item) {
            $(self.gridId).down('.filter').select('[name^='+item.key+']').each(function(el) {
                if (item.key != 'year') {
                    el.value = item.value;
                    return null;
                }

                if (typeof item.value == 'string') {
                    el.value = item.value;
                    return null;
                }

                $(self.gridId).down('.filter').down('[name=year[from]]').value = item.value.from;
                $(self.gridId).down('.filter').down('[name=year[to]]').value = item.value.to;

            });
        });

        self.getGridObj().doFilter();
    }

    //##################################

});