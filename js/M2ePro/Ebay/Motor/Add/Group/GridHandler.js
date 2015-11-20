EbayMotorAddGroupGridHandler = Class.create(GridHandler, {

    //----------------------------------

    initialize: function($super,gridId)
    {
        $super(gridId);
    },

    //##################################

    prepareActions: function()
    {
        this.actions = {
            selectAction: this.selectGroups.bind(this),
            removeGroupAction: this.removeGroup.bind(this)
        };
    },

    //##################################

    selectGroups: function()
    {
        var self = this,
            groups = self.getGridMassActionObj().checkedString.split(',');

        groups.each(function(group){

            for(var i = 0; i < EbayMotorsHandlerObj.selectedData.groups.length; i++) {
                if (EbayMotorsHandlerObj.selectedData.groups[i] == group) {
                    return;
                }
            }

            EbayMotorsHandlerObj.selectedData.groups.push(group);
        });

        self.unselectAll();
        EbayMotorsHandlerObj.updateSelectedData();
    },

    //----------------------------------

    removeGroup: function()
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/removeGroup'), {
            postmethod: 'post',
            parameters: {
                groups_ids: self.getGridMassActionObj().checkedString
            },
            onSuccess: function(transport) {

                if (transport.responseText == '0') {
                    self.unselectAllAndReload();
                }
            }
        });
    },

    //##################################

    viewGroupContentPopup: function(groupId, title)
    {
        var self = this;

        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/viewGroupContent'), {
            method: 'get',
            parameters: {
                group_id: groupId
            },
            onSuccess: function(transport) {

                self.goupContentPopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: title,
                    top: 100,
                    width: 500,
                    height: 620,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show,
                    onClose: function() {
                        self.unselectAllAndReload();
                    }
                });

                self.goupContentPopup.options.destroyOnClose = true;

                $('modal_dialog_message').update(transport.responseText);

                setTimeout(function() {
                    Windows.getFocusedWindow().content.style.height = '';
                    Windows.getFocusedWindow().content.style.maxHeight = '500px';
                }, 50);
            }
        });
    },

    removeItemFromGroup: function(el, itemId, groupId)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/removeItemFromGroup'), {
            postmethod: 'post',
            parameters: {
                items_ids: itemId,
                group_id: groupId
            },
            onSuccess: function(transport) {

                if (transport.responseText == '0') {
                    if ($(el).up('tbody').select('tr').length == 1) {
                        Windows.getFocusedWindow().close();
                        return;
                    }

                    $(el).up('tr').remove();
                }
            }
        });
    },

    removeFilterFromGroup: function(el, filterId, groupId)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/removeFilterFromGroup'), {
            postmethod: 'post',
            parameters: {
                filters_ids: filterId,
                group_id: groupId
            },
            onSuccess: function(transport) {

                if (transport.responseText == '0') {
                    if ($(el).up('tbody').select('tr').length == 1) {
                        Windows.getFocusedWindow().close();
                        return;
                    }

                    $(el).up('tr').remove();
                }
            }
        });
    },

    //##################################

});