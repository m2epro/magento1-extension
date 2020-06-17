window.EbayMotorAddGroupGrid = Class.create(Grid, {

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

            for(var i = 0; i < EbayMotorsObj.selectedData.groups.length; i++) {
                if (EbayMotorsObj.selectedData.groups[i] == group) {
                    return;
                }
            }

            EbayMotorsObj.selectedData.groups.push(group);
        });

        self.unselectAll();
        EbayMotorsObj.updateSelectedData();
    },

    //----------------------------------

    removeGroup: function()
    {
        var self = this,
            groups = self.getGridMassActionObj().checkedString.split(',');

        groups.each(function(group){
            var index = EbayMotorsObj.selectedData.groups.indexOf(group);

            if (index > -1) {
                EbayMotorsObj.selectedData.groups.splice(index, 1);
            }
        });

        EbayMotorsObj.updateSelectedData();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_motor/removeGroup'), {
            method: 'post',
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

        MessageObj.clearAll();

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
            method: 'post',
            parameters: {
                items_ids: itemId,
                group_id: groupId
            },
            onSuccess: function(transport) {

                if (transport.responseText == '0') {
                    if ($(el).up('tbody').select('tr').length == 1) {
                        var index = EbayMotorsObj.selectedData.groups.indexOf(''+groupId);

                        if (index > -1) {
                            EbayMotorsObj.selectedData.groups.splice(index, 1);
                            EbayMotorsObj.updateSelectedData();
                        }

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
            method: 'post',
            parameters: {
                filters_ids: filterId,
                group_id: groupId
            },
            onSuccess: function(transport) {

                if (transport.responseText == '0') {
                    if ($(el).up('tbody').select('tr').length == 1) {
                        var index = EbayMotorsObj.selectedData.groups.indexOf(''+groupId);

                        if (index > -1) {
                            EbayMotorsObj.selectedData.groups.splice(index, 1);
                            EbayMotorsObj.updateSelectedData();
                        }

                        Windows.getFocusedWindow().close();
                        return;
                    }

                    $(el).up('tr').remove();
                }
            }
        });
    }

    //##################################

});