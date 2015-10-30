EbayListingOtherSynchronizationHandler = Class.create();
EbayListingOtherSynchronizationHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    initialize: function()
    {
        // ---------------------------------------
        Validation.add('M2ePro-validate-conditions-between', M2ePro.translator.translate('Must be greater than "Min".'), function(value, el) {

            var minValue = $(el.id.replace('_max','')).value;

            if (!el.up('tr').visible()) {
                return true;
            }

            return parseInt(value) > parseInt(minValue);
        });

        // ---------------------------------------
        Validation.add('M2ePro-validate-stop-relist-conditions-product-status', M2ePro.translator.translate('Inconsistent Settings in Relist and Stop Rules.'), function(value, el) {

            if (EbayListingOtherSynchronizationHandlerObj.isRelistModeDisabled()) {
                return true;
            }

            if ($('stop_status_disabled').value == 1 && $('relist_status_enabled').value == 0) {
                return false;
            }

            return true;
        });

        Validation.add('M2ePro-validate-stop-relist-conditions-stock-availability', M2ePro.translator.translate('Inconsistent Settings in Relist and Stop Rules.'), function(value, el) {

            if (EbayListingOtherSynchronizationHandlerObj.isRelistModeDisabled()) {
                return true;
            }

            if ($('stop_out_off_stock').value == 1 && $('relist_is_in_stock').value == 0) {
                return false;
            }

            return true;
        });

        Validation.add('M2ePro-validate-stop-relist-conditions-item-qty', M2ePro.translator.translate('Inconsistent Settings in Relist and Stop Rules.'), function(value, el) {

            if (EbayListingOtherSynchronizationHandlerObj.isRelistModeDisabled()) {
                return true;
            }

            var stopMaxQty = 0,
                relistMinQty = 0;

            switch (parseInt($('stop_qty').value)) {

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::STOP_QTY_NONE'):
                    return true;
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::STOP_QTY_LESS'):
                    stopMaxQty = parseInt($('stop_qty_value').value);
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::STOP_QTY_BETWEEN'):
                    stopMaxQty = parseInt($('stop_qty_value_max').value);
                    break;
            }

            switch (parseInt($('relist_qty').value)) {

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::RELIST_QTY_NONE'):
                    return false;
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::RELIST_QTY_MORE'):
                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::RELIST_QTY_BETWEEN'):
                    relistMinQty = parseInt($('relist_qty_value').value);
                    break;
            }

            if (relistMinQty <= stopMaxQty) {
                return false;
            }

            return true;
        });
        // ---------------------------------------
    },

    // ---------------------------------------

    isRelistModeDisabled: function()
    {
        return $('relist_mode').value == 0;
    },

    // ---------------------------------------

    save_click: function(redirectUrl)
    {
        var url = M2ePro.url.get('adminhtml_ebay_listing_other_synchronization/save', {"back": redirectUrl});
        this.submitForm(url);
    },

    save_and_edit_click: function(back, tabsId)
    {
        var params = 'tab=' + $$('#' + tabsId + ' a.active')[0].name + '&back=' + back;

        var url = M2ePro.url.get('formSubmit', {'back': base64_encode('edit|' + params)});
        this.submitForm(url);
    },

    check_synchronization_mode: function()
    {
        var elem = $('synchronization_mode');
        var value = elem.options[elem.selectedIndex].value;

        if (value == 0) {
            alert(M2ePro.translator.translate('Please enable Synchronization first!'));
            ebayListingOtherSynchronizationEditTabsJsTabs.showTabContent(
                $('ebayListingOtherSynchronizationEditTabs_general'));
        }
    },

    // ---------------------------------------

    qtySourceChange: function()
    {
        var self = EbayListingOtherSynchronizationHandlerObj;

        var sourceMode = this.value;

        $('qty_attribute').value = '';
        if (sourceMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Source::QTY_SOURCE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('qty_attribute'))
        }

        var reviseUpdateQty = $('revise_update_qty');
        var relistQty = $('relist_qty');
        var stopQty = $('stop_qty');

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Source::QTY_SOURCE_NONE')) {

            reviseUpdateQty.selectedIndex = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::REVISE_UPDATE_QTY_NONE');
            reviseUpdateQty.disabled = true;

            relistQty.selectedIndex = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::RELIST_QTY_NONE');
            relistQty.simulate('change');
            relistQty.disabled = true;

            stopQty.selectedIndex = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::STOP_QTY_NONE');
            stopQty.simulate('change');
            stopQty.disabled = true;
        } else {
            reviseUpdateQty.disabled = false;
            relistQty.disabled = false;
            stopQty.disabled = false;
        }
    },

    priceSourceChange: function()
    {
        var self = EbayListingOtherSynchronizationHandlerObj;

        var sourceMode = this.value;

        $('price_attribute').value = '';
        if (sourceMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Source::PRICE_SOURCE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('price_attribute'));
        }

        var reviseUpdatePrice = $('revise_update_price');

        if (sourceMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Source::PRICE_SOURCE_NONE')) {
            reviseUpdatePrice.selectedIndex = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::REVISE_UPDATE_PRICE_NONE');
            reviseUpdatePrice.disabled = true;
        } else {
            reviseUpdatePrice.disabled = false;
        }
    },

    titleSourceChange: function()
    {
        var self = EbayListingOtherSynchronizationHandlerObj;

        var sourceMode = this.value;

        $('title_attribute').value = '';
        if (sourceMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Source::TITLE_SOURCE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('title_attribute'));
        }

        var reviseUpdateTitle = $('revise_update_title');

        if (sourceMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Source::TITLE_SOURCE_NONE')) {
            reviseUpdateTitle.selectedIndex = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::REVISE_UPDATE_TITLE_NONE');
            reviseUpdateTitle.disabled = true;
        } else {
            reviseUpdateTitle.disabled = false;
        }
    },

    subTitleSourceChange: function()
    {
        var self = EbayListingOtherSynchronizationHandlerObj;

        var sourceMode = this.value;

        $('sub_title_attribute').value = '';
        if (sourceMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Source::SUB_TITLE_SOURCE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('sub_title_attribute'));
        }

        var reviseUpdateSubTitle = $('revise_update_sub_title');

        if (sourceMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Source::SUB_TITLE_SOURCE_NONE')) {
            reviseUpdateSubTitle.selectedIndex = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::REVISE_UPDATE_SUB_TITLE_NONE');
            reviseUpdateSubTitle.disabled = true;
        } else {
            reviseUpdateSubTitle.disabled = false;
        }
    },

    descriptionSourceChange: function()
    {
        var self = EbayListingOtherSynchronizationHandlerObj;

        var sourceMode = this.value;

        $('description_attribute').value = '';
        if (sourceMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Source::DESCRIPTION_SOURCE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('description_attribute'));
        }

        var reviseUpdateDescription = $('revise_update_description');

        if (sourceMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Source::DESCRIPTION_SOURCE_NONE')) {
            reviseUpdateDescription.selectedIndex = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::REVISE_UPDATE_DESCRIPTION_NONE');
            reviseUpdateDescription.disabled = true;
        } else {
            reviseUpdateDescription.disabled = false;
        }
    },

    // ---------------------------------------

    relist_mode_change: function()
    {
        if (this.value == 1) {
            $$('.relist-options').each(function(elem) {
                elem.show();
            });
        } else {
            $$('.relist-options').each(function(elem) {
                elem.hide();
            });
        }
    },

    relist_qty_change: function()
    {
        var self = EbayListingOtherSynchronizationHandlerObj;

        $('relist_qty_value_container').hide();
        $('relist_qty_item').hide();
        $('relist_qty_value_max_container').hide();
        $('relist_qty_item_min').hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::RELIST_QTY_MORE')) {
            $('relist_qty_value_container').show();
            $('relist_qty_item').show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::RELIST_QTY_BETWEEN')) {
            $('relist_qty_value_max_container').show();
            $('relist_qty_item_min').show();
            $('relist_qty_value_container').show();
        }
    },

    stop_qty_change: function()
    {
        var self = EbayListingOtherSynchronizationHandlerObj;

        $('stop_qty_value_container').hide();
        $('stop_qty_item').hide();
        $('stop_qty_value_max_container').hide();
        $('stop_qty_item_min').hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::RELIST_QTY_LESS')) {
            $('stop_qty_value_container').show();
            $('stop_qty_item').show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::RELIST_QTY_BETWEEN')) {
            $('stop_qty_value_max_container').show();
            $('stop_qty_item_min').show();
            $('stop_qty_value_container').show();
        }
    },

    completeStep: function()
    {
        new Ajax.Request(M2ePro.url.formSubmit + '?' + $('edit_form').serialize(), {
            method: 'get',
            asynchronous: true,
            onSuccess: function(transport) {
                window.opener.completeStep = 1;
                window.close();
            }
        });
    }

    // ---------------------------------------
});