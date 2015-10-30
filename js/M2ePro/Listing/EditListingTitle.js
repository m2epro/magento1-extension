ListingEditListingTitle = Class.create();
ListingEditListingTitle.prototype = {

    // ---------------------------------------

    initialize: function(gridId) {
        this.gridId = gridId;
        CommonHandlerObj.setValidationCheckRepetitionValue('M2ePro-listing-title',
            M2ePro.text.title_not_unique_error,
            'Listing', 'title', 'id', null,
            M2ePro.php.constant('Ess_M2ePro_Helper_Component::NICK'));
    },

    openPopup: function(id)
    {
        window[this.gridId + '_massactionJsObject'].unselectAll();

        var config = {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Edit Listing Title'),
            top: 250,
            maxHeight: 500,
            height: 105,
            width: 460,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show
        };

        this.oldTitle = $('listing_title_' + id).innerHTML;

        var popUpHtml = '' +
            '<form id="editListingTitle" action="javascript:void(0)">' +
                '<div style="margin: 10px">' +
                    '<input id="listing_title_input" class="input-text required-entry M2ePro-listing-title" style="width: 410px;" type="text" value="'+this.oldTitle+'" />' +
                '</div>' +
                '<div style="float: right; margin-top: 10px;">' +
                    '<a onclick="Windows.getFocusedWindow().close();" href="javascript:void(0)">' +
                        M2ePro.translator.translate('Cancel')+
                    '</a>&nbsp;&nbsp;&nbsp;' +
                    '<button onclick="EditListingTitleObj.saveListingTitle('+id+'); return false;">'+
                        M2ePro.translator.translate('Save') +
                    '</button>' +
                '</div>' +
            '</form>'
        ;

        Dialog.info(popUpHtml, config);

        this.form = new varienForm('editListingTitle','');
    },

    saveListingTitle: function(listingId)
    {
        var newTitle = $('listing_title_input').value;

        if (this.oldTitle == newTitle) {
            window[this.gridId + 'JsObject'].reload();
            Windows.getFocusedWindow().close();
            return;
        }

        if (!this.form.validate()) {
            return;
        }

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_common_listing/saveTitle'), {
            parameters: {
                id: listingId,
                title: newTitle
            },
            onSuccess: (function(transport) {
                Windows.getFocusedWindow().close();
                window[this.gridId + 'JsObject'].reload();
            }).bind(this)
        });
    }

    // ---------------------------------------
};
