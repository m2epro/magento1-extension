EbayMotorsHandler = Class.create();
EbayMotorsHandler.prototype = Object.extend(new CommonHandler(), {

    listingId: null,
    motorsType: null,
    saveAsGroupPopupHtml: '',
    setNotePopupHtml: '',

    selectedData: {
        items: [],
        filters: [],
        groups: []
    },

    // ---------------------------------------

    initialize: function(listingId, motorsType) {
        this.listingId = listingId;
        this.motorsType = motorsType;
    },

    // ---------------------------------------

    openAddPopUp: function(listingProductsIds)
    {
        var self = this;

        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/addView'), {
            method: 'get',
            parameters: {
                motors_type: self.motorsType
            },
            onSuccess: function(transport) {

                self.addPopUp = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Add Compatible Vehicles'),
                    width: 1000,
                    height: 620,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show,
                    onClose: function() {
                        EbayListingSettingsGridHandlerObj.unselectAllAndReload();

                        self.selectedData = {
                            items: [],
                            filters: [],
                            groups: []
                        };
                    }
                });

                self.addPopUp.options.destroyOnClose = true;

                self.addPopUp.listingProductsIds = listingProductsIds;

                $('modal_dialog_message').update(transport.responseText);

                setTimeout(function() {
                    Windows.getFocusedWindow().content.style.height = '';
                    Windows.getFocusedWindow().content.style.maxHeight = '630px';
                }, 50);
            }
        });
    },

    //----------------------------------

    openViewItemPopup: function(entityId, grid)
    {
        var self = this;

        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/viewItem'), {
            method: 'get',
            parameters: {
                entity_id: entityId,
                motors_type: self.motorsType
            },
            onSuccess: function(transport) {

                self.viewItemPopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('View Items'),
                    width: 850,
                    height: 620,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show,
                    onClose: function() {
                        grid.unselectAllAndReload();
                    }
                });

                self.viewItemPopup.options.destroyOnClose = true;

                $('modal_dialog_message').update(transport.responseText);

                setTimeout(function() {
                    Windows.getFocusedWindow().content.style.height = '';
                    Windows.getFocusedWindow().content.style.maxHeight = '630px';
                }, 50);
            }
        });
    },

    openViewFilterPopup: function(entityId, grid)
    {
        var self = this;

        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/viewFilter'), {
            method: 'get',
            parameters: {
                entity_id: entityId,
                motors_type: self.motorsType
            },
            onSuccess: function(transport) {

                self.viewFilterPopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('View Filters'),
                    width: 850,
                    height: 620,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show,
                    onClose: function() {
                        grid.unselectAllAndReload();
                    }
                });

                self.viewFilterPopup.options.destroyOnClose = true;

                $('modal_dialog_message').update(transport.responseText);

                setTimeout(function() {
                    Windows.getFocusedWindow().content.style.height = '';
                    Windows.getFocusedWindow().content.style.maxHeight = '630px';
                }, 50);
            }
        });
    },

    openViewGroupPopup: function(listingProductId, grid)
    {
        var self = this;

        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/viewGroup'), {
            method: 'get',
            parameters: {
                listing_product_id: listingProductId,
                motors_type: self.motorsType
            },
            onSuccess: function(transport) {

                self.viewGroupPopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('View Groups'),
                    width: 850,
                    height: 620,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show,
                    onClose: function() {
                        grid.unselectAllAndReload();
                    }
                });

                self.viewGroupPopup.options.destroyOnClose = true;

                $('modal_dialog_message').update(transport.responseText);

                setTimeout(function() {
                    Windows.getFocusedWindow().content.style.height = '';
                    Windows.getFocusedWindow().content.style.maxHeight = '630px';
                }, 50);
            }
        });
    },

    // ---------------------------------------

    updateSelectedData: function()
    {
        var self = this,
            containerEl = $('selected_motors_data_container'),
            dataEl = $('selected_motors_data');

        containerEl.hide();

        dataEl.down('.items').hide();
        if (self.selectedData.items.length > 0) {
            containerEl.show();

            dataEl.down('.items').show();
            dataEl.down('.items .count').innerHTML = self.selectedData.items.length;
        }

        dataEl.down('.filters').hide();
        if (self.selectedData.filters.length > 0) {
            containerEl.show();

            dataEl.down('.filters').show();
            dataEl.down('.filters .count').innerHTML = self.selectedData.filters.length;
        }

        dataEl.down('.groups').hide();
        if (self.selectedData.groups.length > 0) {
            containerEl.show();

            dataEl.down('.groups').show();
            dataEl.down('.groups .count').innerHTML = self.selectedData.groups.length;
        }
    },

    //----------------------------------

    viewSelectedItemPopup: function()
    {
        var self = this;

        self.viewSelectedItemsPopup = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Selected Items'),
            top: 100,
            width: 500,
            height: 620,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        self.viewSelectedItemsPopup.options.destroyOnClose = true;

        var table = new Element('table', {
            cellspacing: '0',
            class: 'data',
            style: 'background-color: white; width: 100%; margin: 10px 10px 10px 0; border: 1px solid #cbd3d4;'
        });

        table.update(
        '<thead> ' +
            '<tr class="headings">' +
                '<th style="font-weight: bold; padding-left: 5px;">' +
                    M2ePro.translator.translate('Motor Item') +
                '</th>' +
                '<th style="font-weight: bold; padding-left: 5px">' +
                    M2ePro.translator.translate('Note') +
                '</th>' +
                '<th></th>' +
            '</tr>' +
        '</thead>' +
        '<tbody></tbody>'
        );

        var tbody = table.down('tbody');

        self.selectedData.items.each(function(item) {
            var tr = new Element('tr'),
                tdItem = new Element('td', {
                    style: 'padding: 5px; width: 75px; text-align: center'
                }),
                tdNote = new Element('td', {
                    style: 'padding: 5px'
                }),
                tdRemove = new Element('td', {
                    style: 'padding: 5px; width: 18px;'
                });

            tdItem.innerHTML = item;
            if (EbayMotorAddItemGridHandlerObj.savedNotes[item]) {
                tdNote.innerHTML = EbayMotorAddItemGridHandlerObj.savedNotes[item];
            }

            var removeImageUrl = M2ePro.url.get('m2epro_skin_url') + '/images/delete.png';
            var removeImg = new Element('img', {
                style: 'cursor; pointer',
                src: removeImageUrl
            });

            removeImg.observe('click', function() {
                tr.remove();

                var index = self.selectedData.items.indexOf(item);
                self.selectedData.items.splice(index, 1);

                EbayMotorsHandlerObj.updateSelectedData();

                if (self.selectedData.items.length == 0) {
                    Windows.getFocusedWindow().close();
                }
            }, self);

            tdRemove.insert({bottom: removeImg});

            tr.insert({bottom: tdItem});
            tr.insert({bottom: tdNote});
            tr.insert({bottom: tdRemove});

            tbody.insert({bottom: tr});
        });

        var dialogContent = $('modal_dialog_message');

        dialogContent.insert({
            bottom: new Element('div', {
                style: 'max-height: 200px; overflow: auto;',
                class: 'grid'
            }).insert({bottom: table})
        });

        var closeBtn = new Element('button',{
            type: 'button',
            class: 'scalable close-btn',
            onclick: 'Windows.getFocusedWindow().close()'
        });

        closeBtn.update(M2ePro.translator.translate('Close'));

        dialogContent.insert({
            bottom: new Element('div', {
                style: 'float: right; margin-top: 10px; margin-bottom: 8px;'
            }).insert({bottom: closeBtn})
        });

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '500px';
        }, 50);
    },

    viewSelectedFilterPopup: function()
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_general/modelGetAll'), {
            method: 'get',
            parameters: {
                model: 'Ebay_Motor_Filter',
                id_field: 'id',
                data_field: 'title'
            },
            onSuccess: function (transport) {
                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var filters = transport.responseText.evalJSON();

                self.viewSelectedFiltersPopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Selected Filters'),
                    top: 100,
                    width: 500,
                    height: 620,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });

                self.viewSelectedFiltersPopup.options.destroyOnClose = true;

                var table = new Element('table', {
                    cellspacing: '0',
                    class: 'data',
                    style: 'background-color: white; width: 100%; margin: 10px 10px 10px 0; border: 1px solid #cbd3d4;'
                });

                table.update(
                    '<thead> ' +
                        '<tr class="headings">' +
                            '<th style="font-weight: bold; padding-left: 5px;">' +
                            M2ePro.translator.translate('Filter') +
                            '</th>' +
                            '<th></th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody></tbody>'
                );

                var tbody = table.down('tbody');

                filters.each(function(filter) {
                    if (self.selectedData.filters.indexOf(filter.id) == -1) {
                        return;
                    }

                    var tr = new Element('tr'),
                        tdTitle = new Element('td', {
                            style: 'padding: 5px'
                        }),
                        tdRemove = new Element('td', {
                            style: 'padding: 5px; width: 18px;'
                        });

                    tdTitle.innerHTML = filter.title;

                    var removeImageUrl = M2ePro.url.get('m2epro_skin_url') + '/images/delete.png';
                    var removeImg = new Element('img', {
                        style: 'cursor; pointer',
                        src: removeImageUrl
                    });

                    removeImg.observe('click', function() {
                        tr.remove();

                        var index = self.selectedData.filters.indexOf(filter.id);
                        self.selectedData.filters.splice(index, 1);

                        EbayMotorsHandlerObj.updateSelectedData();

                        if (self.selectedData.filters.length == 0) {
                            Windows.getFocusedWindow().close();
                        }
                    }, self);

                    tdRemove.insert({bottom: removeImg});

                    tr.insert({bottom: tdTitle});
                    tr.insert({bottom: tdRemove});

                    tbody.insert({bottom: tr});
                });

                var dialogContent = $('modal_dialog_message');

                dialogContent.insert({
                    bottom: new Element('div', {
                        style: 'max-height: 200px; overflow: auto;',
                        class: 'grid'
                    }).insert({bottom: table})
                });

                var closeBtn = new Element('button',{
                    type: 'button',
                    class: 'scalable close-btn',
                    onclick: 'Windows.getFocusedWindow().close()'
                });

                closeBtn.update(M2ePro.translator.translate('Close'));

                dialogContent.insert({
                    bottom: new Element('div', {
                        style: 'float: right; margin-top: 10px; margin-bottom: 8px;'
                    }).insert({bottom: closeBtn})
                });

                setTimeout(function() {
                    Windows.getFocusedWindow().content.style.height = '';
                    Windows.getFocusedWindow().content.style.maxHeight = '500px';
                }, 50);
            }
        });
    },

    viewSelectedGroupPopup: function()
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_general/modelGetAll'), {
            method: 'get',
            parameters: {
                model: 'Ebay_Motor_Group',
                id_field: 'id',
                data_field: 'title'
            },
            onSuccess: function (transport) {
                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var groups = transport.responseText.evalJSON();

                self.viewSelectedGroupsPopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Selected Groups'),
                    top: 100,
                    width: 500,
                    height: 620,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });

                self.viewSelectedGroupsPopup.options.destroyOnClose = true;

                var table = new Element('table', {
                    cellspacing: '0',
                    class: 'data',
                    style: 'background-color: white; width: 100%; margin: 10px 10px 10px 0; border: 1px solid #cbd3d4;'
                });

                table.update(
                    '<thead> ' +
                        '<tr class="headings">' +
                            '<th style="font-weight: bold; padding-left: 5px;">' +
                            M2ePro.translator.translate('Filter') +
                            '</th>' +
                            '<th></th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody></tbody>'
                );

                var tbody = table.down('tbody');

                groups.each(function(group) {
                    if (self.selectedData.groups.indexOf(group.id) == -1) {
                        return;
                    }

                    var tr = new Element('tr'),
                        tdTitle = new Element('td', {
                            style: 'padding: 5px'
                        }),
                        tdRemove = new Element('td', {
                            style: 'padding: 5px; width: 18px;'
                        });

                    tdTitle.innerHTML = group.title;

                    var removeImageUrl = M2ePro.url.get('m2epro_skin_url') + '/images/delete.png';
                    var removeImg = new Element('img', {
                        style: 'cursor; pointer',
                        src: removeImageUrl
                    });

                    removeImg.observe('click', function() {
                        tr.remove();

                        var index = self.selectedData.groups.indexOf(group.id);
                        self.selectedData.groups.splice(index, 1);

                        EbayMotorsHandlerObj.updateSelectedData();

                        if (self.selectedData.groups.length == 0) {
                            Windows.getFocusedWindow().close();
                        }
                    }, self);

                    tdRemove.insert({bottom: removeImg});

                    tr.insert({bottom: tdTitle});
                    tr.insert({bottom: tdRemove});

                    tbody.insert({bottom: tr});
                });

                var dialogContent = $('modal_dialog_message');

                dialogContent.insert({
                    bottom: new Element('div', {
                        style: 'max-height: 200px; overflow: auto;',
                        class: 'grid'
                    }).insert({bottom: table})
                });

                var closeBtn = new Element('button',{
                    type: 'button',
                    class: 'scalable close-btn',
                    onclick: 'Windows.getFocusedWindow().close()'
                });

                closeBtn.update(M2ePro.translator.translate('Close'));

                dialogContent.insert({
                    bottom: new Element('div', {
                        style: 'float: right; margin-top: 10px; margin-bottom: 8px;'
                    }).insert({bottom: closeBtn})
                });

                setTimeout(function() {
                    Windows.getFocusedWindow().content.style.height = '';
                    Windows.getFocusedWindow().content.style.maxHeight = '500px';
                }, 50);
            }
        });
    },

    //----------------------------------

    updateMotorsData: function(override)
    {
        var self = this;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        var items = {};
        self.selectedData.items.each(function(item){
            items[item] = EbayMotorAddItemGridHandlerObj.savedNotes[item];
        });

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/updateMotorsData'), {
            postmethod: 'post',
            parameters: {
                listing_id: self.listingId,
                listing_products_ids: self.addPopUp.listingProductsIds,
                motors_type: self.motorsType,
                overwrite: override,
                items: Object.toQueryString(items),
                filters_ids: implode(',', self.selectedData.filters),
                groups_ids: implode(',', self.selectedData.groups)
            },
            onSuccess: function(transport) {

                if (transport.responseText == '0') {
                    Windows.getFocusedWindow().close();
                }
            }
        });
    },

    //----------------------------------

    openAddRecordPopup: function()
    {
        var self = this;

        Dialog.info(null, {
            id: 'add_custom_motors_record_popup',
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Add Custom Compatible Vehicle'),
            top: 50,
            width: 500,
            height: 320,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            onOk: self.addCustomMotorsRecord
        });

        var contentEl = $('add_custom_motors_record_pop_up_content');

        if (contentEl) {
            self.costomMotorsRecordPopupContent = contentEl.innerHTML;
            contentEl.remove();
        }

        $('modal_dialog_message').insert(self.costomMotorsRecordPopupContent);
        $('modal_dialog_message').innerHTML.evalScripts();
    },

    addCustomMotorsRecord: function()
    {
        var validationResult = $$('#modal_dialog_message form *.popup-validate-entry').collect(Validation.validate);

        if (validationResult.indexOf(false) != -1) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/addCustomMotorsRecord'), {
            method: 'post',
            asynchronous: true,
            parameters: $$('#modal_dialog_message form').first().serialize(true),
            onSuccess: function(transport) {

                var result = transport.responseText.evalJSON();

                if (!result.result) {
                    return alert(result.message);
                }

                Windows.getFocusedWindow().close();
                EbayMotorAddItemGridHandlerObj.unselectAllAndReload();
            }
        });
    },

    removeCustomMotorsRecord: function(motorsType, keyId)
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/removeCustomMotorsRecord'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                motors_type: motorsType,
                key_id: keyId
            },
            onSuccess: function(transport) {

                var result = transport.responseText.evalJSON();

                if (!result.result) {
                    return alert(result.message);
                }

                EbayMotorAddItemGridHandlerObj.unselectAllAndReload();
            }
        });
    },

    // ---------------------------------------

    closeInstruction: function()
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/closeInstruction'), {
            method: 'post',
            asynchronous: true,
            onSuccess: function (transport) {
                $('add_custom_motors_record_instruction_container').hide();
                $('add_custom_motors_record_data_container').show();
            }
        });
    }

    // ---------------------------------------
});