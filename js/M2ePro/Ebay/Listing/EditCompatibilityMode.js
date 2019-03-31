EditCompatibilityMode = Class.create(CommonHandler, {

    // ---------------------------------------

    initialize: function(gridId) {
        this.gridId = gridId;
    },

    openPopup: function(listingId)
    {
        window[this.gridId + '_massactionJsObject'].unselectAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing/getChangePartsCompatibilityModePopupHtml'), {
            parameters: {
                listing_id: listingId
            },
            onSuccess: (function(transport) {

                Dialog.info(transport.responseText, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Edit Parts Compatibility Mode'),
                    top: 250,
                    maxHeight: 500,
                    height: 180,
                    width: 400,
                    zIndex: 100,
                    recenterAuto: true,
                    hideEffect: Element.hide,
                    showEffect: Element.show,
                    onCancel: function() {
                        return this.closePopup();
                    }.bind(this),
                    onClose: function() {
                        return this.closePopup();
                    }.bind(this)
                });

                this.oldMode = trim($('listing_compatibility_mode_' + listingId).innerHTML);
                this.listingId = listingId;
                this.form = new varienForm('listing_part_compatibility_mode_form','');

                this.autoHeightFix();

            }).bind(this)
        });
    },

    saveListingMode: function()
    {
        var newMode = $('listing_compatibility_mode_select').value;

        if (this.oldMode == newMode) {

            this.closePopup();
            return;
        }

        if (!this.form.validate()) {
            return;
        }

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing/savePartsCompatibilityMode'), {
            parameters: {
                listing_id: this.listingId,
                mode:       newMode
            },
            onSuccess: (function(transport) {
                this.closePopup();
            }).bind(this)
        });
    },

    closePopup: function()
    {
        Windows.getFocusedWindow() && Windows.getFocusedWindow().close();
        window[this.gridId + 'JsObject'].reload();

        return true;
    }

    // ---------------------------------------
});
