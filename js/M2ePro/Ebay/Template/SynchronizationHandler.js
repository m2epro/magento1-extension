EbayTemplateSynchronizationHandler = Class.create();
EbayTemplateSynchronizationHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    initialize: function()
    {
        Validation.add('validate-qty', M2ePro.translator.translate('Wrong value. Only integer numbers.'), function(value, el) {

            if (!el.up('tr').visible()) {
                return true;
            }

            if (value.match(/[^\d]+/g) || value <= 0) {
                return false;
            }

            return true;
        });

        // ---------------------------------------
        Validation.add('M2ePro-validate-conditions-between', M2ePro.translator.translate('Must be greater than "Min".'), function(value, el) {

            var minValue = $(el.id.replace('_max','')).value;

            if (!el.up('tr').visible()) {
                return true;
            }

            return parseInt(value) > parseInt(minValue);
        });
        // ---------------------------------------

        // ---------------------------------------
        Validation.add('M2ePro-validate-stop-relist-conditions-product-status', M2ePro.translator.translate('Inconsistent Settings in Relist and Stop Rules.'), function(value, el) {

            if (EbayTemplateSynchronizationHandlerObj.isRelistModeDisabled()) {
                return true;
            }

            if ($('stop_status_disabled').value == 1 && $('relist_status_enabled').value == 0) {
                return false;
            }

            return true;
        });

        Validation.add('M2ePro-validate-stop-relist-conditions-stock-availability', M2ePro.translator.translate('Inconsistent Settings in Relist and Stop Rules.'), function(value, el) {

            if (EbayTemplateSynchronizationHandlerObj.isRelistModeDisabled()) {
                return true;
            }

            if ($('stop_out_off_stock').value == 1 && $('relist_is_in_stock').value == 0) {
                return false;
            }

            return true;
        });

        Validation.add('M2ePro-validate-stop-relist-conditions-item-qty', M2ePro.translator.translate('Inconsistent Settings in Relist and Stop Rules.'), function(value, el) {

            if (EbayTemplateSynchronizationHandlerObj.isRelistModeDisabled()) {
                return true;
            }

            var stopMaxQty = 0,
                relistMinQty = 0;

            var qtyType = el.getAttribute('qty_type');

            switch (parseInt($('stop_qty_' + qtyType).value)) {

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_NONE'):
                    return true;
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_LESS'):
                    stopMaxQty = parseInt($('stop_qty_' + qtyType + '_value').value);
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_BETWEEN'):
                    stopMaxQty = parseInt($('stop_qty_' + qtyType + '_value_max').value);
                    break;
            }

            switch (parseInt($('relist_qty_' + qtyType).value)) {

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_NONE'):
                    return false;
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_MORE'):
                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_BETWEEN'):
                    relistMinQty = parseInt($('relist_qty_' + qtyType + '_value').value);
                    break;
            }

            if (relistMinQty <= stopMaxQty) {
                return false;
            }

            return true;
        });
        // ---------------------------------------

        // ---------------------------------------
        Validation.add('M2ePro-validate-schedule-interval-date', M2ePro.translator.translate('Wrong value.'), function(value, el) {

            if (!el.up('tr').visible()) {
                return true;
            }

            return el.value.match('^[0-9]{4}-[0-9]{2}-[0-9]{1,2}$');
        });

        Validation.add('M2ePro-validate-schedule-week-days', M2ePro.translator.translate('You need to choose at set at least one time for the schedule to run.'), function(value, el) {

            var countOfCheckedDays = 0;

            if (!EbayTemplateSynchronizationHandlerObj.isScheduleModeEnabled()) {
                return true;
            }

            $$('.schedule_week_day_mode').each(function(el) {
                el.checked && countOfCheckedDays++;
            });

            return countOfCheckedDays > 0;
        });

        Validation.add('M2ePro-validate-selected-schedule-time', M2ePro.translator.translate('You should specify time.'), function(value, el) {

            var countUnselectedControls = 0;

            if (!EbayTemplateSynchronizationHandlerObj.isScheduleModeEnabled()) {
                return true;
            }

            if (!el.up('tr').select('.schedule_week_day_mode').shift().checked) {
                return true;
            }

            el.up('td').select('select').each(function(el) {
                el.value == '' && countUnselectedControls++;
            });

            return countUnselectedControls == 0;
        });
        // ---------------------------------------

        // ---------------------------------------
        Validation.add('M2ePro-validate-schedule-wrong-interval-date', M2ePro.translator.translate('Must be greater than "Active From" Date.'), function(value, el) {

            if (!el.up('tr').visible()) {
                return true;
            }

            var fromDate = new Date($('schedule_interval_date_from').value),
                toDate   = new Date(value);

            return (toDate - fromDate) >= 0;
        });

        Validation.add('M2ePro-validate-schedule-wrong-interval-time', M2ePro.translator.translate('Must be greater than "From Time".'), function(value, el) {

            if (!EbayTemplateSynchronizationHandlerObj.isScheduleModeEnabled()) {
                return true;
            }

            if (!el.up('tr').select('.schedule_week_day_mode').shift().checked) {
                return true;
            }

            var now      = new Date(),
                fromTime = new Date(now.toDateString() + ' ' + $(el.id.replace('validator_to','from')).value),
                toTime   = new Date(now.toDateString() + ' ' + $(el.id.replace('validator_to','to')).value);

            return (toTime - fromTime) > 0;
        });
        // ---------------------------------------
    },

    // ---------------------------------------

    isRelistModeDisabled: function()
    {
        return $('relist_mode').value == 0;
    },

    isScheduleModeEnabled: function()
    {
        return $('schedule_mode').value == 1;
    },

    // ---------------------------------------

    getNavigationTabName: function(element)
    {
        return $('ebay_template_synchronization_edit_form_navigation_bar_' + element.id.split('_').shift());
    },

    // ---------------------------------------

    setVirtualTabsAsInactive: function()
    {
        $$('#ebay_template_synchronization_edit_form_container .form_content').invoke('hide');
        $$('#ebay_template_synchronization_edit_form_container .navigation_bar').invoke('removeClassName','active');
    },

    setVirtualTabAsActive: function()
    {
        EbayTemplateSynchronizationHandlerObj.setVirtualTabsAsInactive();

        $(this.id.replace('navigation_bar','content')).show();
        this.addClassName('active');
    },

    setVirtualTabAsChanged: function()
    {
        var tab = EbayTemplateSynchronizationHandlerObj.getNavigationTabName(this);
        tab.addClassName('changed');
    },

    checkVirtualTabValidation: function()
    {
        var failedItems = $$('#ebay_template_synchronization_edit_form_container .validation-failed');

        $$('#ebay_template_synchronization_edit_form_container .navigation_bar').invoke('removeClassName','error');

        failedItems.each(function(el) {
            var tab = EbayTemplateSynchronizationHandlerObj.getNavigationTabName(el);
            tab.addClassName('error');
        });

        if (failedItems.length > 0) {
            EbayTemplateSynchronizationHandlerObj.setVirtualTabsAsInactive();

            var tab = EbayTemplateSynchronizationHandlerObj.getNavigationTabName(failedItems.shift());
            $(tab.id.replace('navigation_bar','content')).show();
            tab.addClassName('active');
        }
    },

    // ---------------------------------------

    listMode_change: function()
    {
        $('magento_block_ebay_template_synchronization_form_data_list_rules').hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_MODE_YES')) {
            $('magento_block_ebay_template_synchronization_form_data_list_rules').show();
        }
    },

    listQty_change: function()
    {
        var qtyType = this.getAttribute('qty_type');

        var valueContainer    = $('list_qty_' + qtyType + '_value_container'),
            valueMaxContainer = $('list_qty_' + qtyType + '_value_max_container'),
            itemMin           = $('list_qty_' + qtyType + '_item_min'),
            item              = $('list_qty_' + qtyType + '_item');

        valueContainer.hide();
        valueMaxContainer.hide();
        itemMin.hide();
        item.hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_LESS') ||
            this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_MORE')) {
            item.show();
            valueContainer.show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_BETWEEN')) {
            itemMin.show();
            valueContainer.show();
            valueMaxContainer.show();
        }
    },

    // ---------------------------------------

    reviseQty_change: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::REVISE_UPDATE_QTY_YES')) {
            $('revise_update_qty_max_applied_value_mode_tr').show();
            $('revise_update_qty_max_applied_value_line_tr').show();
            $('revise_update_qty_max_applied_value_mode').simulate('change');
        } else {
            $('revise_update_qty_max_applied_value_mode_tr').hide();
            $('revise_update_qty_max_applied_value_line_tr').hide();
            $('revise_update_qty_max_applied_value_tr').hide();
            $('revise_update_qty_max_applied_value_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::REVISE_MAX_AFFECTED_QTY_MODE_OFF');
        }
    },

    reviseQtyMaxAppliedValueMode_change: function(event)
    {
        var self = EbayTemplateSynchronizationHandlerObj;

        $('revise_update_qty_max_applied_value_tr').hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::REVISE_MAX_AFFECTED_QTY_MODE_ON')) {
            $('revise_update_qty_max_applied_value_tr').show();
        } else if (!event.cancelable) {
            self.openReviseMaxAppliedQtyDisableConfirmationPopUp();
        }
    },

    openReviseMaxAppliedQtyDisableConfirmationPopUp: function()
    {
        Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: 'Are you sure?',
            width: 600,
            height: 400,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            onClose: function() {
                $('revise_update_qty_max_applied_value_mode').selectedIndex = 1;
                $('revise_update_qty_max_applied_value_mode').simulate('change');
            }
        });

        $('modal_dialog_message').update($('revise_qty_max_applied_value_confirmation_popup_template').innerHTML);

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '630px';
        }, 50);
    },

    reviseQtyMaxAppliedValueDisableConfirm: function()
    {
        Windows.getFocusedWindow().close();

        $('revise_update_qty_max_applied_value_mode').selectedIndex = 0;
        $('revise_update_qty_max_applied_value_mode').simulate('change');
    },

    // ---------------------------------------

    revisePrice_change: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::REVISE_UPDATE_PRICE_YES')) {
            $('revise_update_price_max_allowed_deviation_mode_tr').show();
            $('revise_update_price_max_allowed_deviation_tr').show();
            $('revise_update_price_line').show();
            $('revise_update_price_max_allowed_deviation_mode').simulate('change');
        } else {
            $('revise_update_price_max_allowed_deviation_mode_tr').hide();
            $('revise_update_price_max_allowed_deviation_tr').hide();
            $('revise_update_price_line').hide();
            $('revise_update_price_max_allowed_deviation_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_OFF');
        }
    },

    revisePriceMaxAllowedDeviationMode_change: function(event)
    {
        var self = EbayTemplateSynchronizationHandlerObj;

        $('revise_update_price_max_allowed_deviation_tr').hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_ON')) {
            $('revise_update_price_max_allowed_deviation_tr').show();
        } else if (!event.cancelable) {
            self.openReviseMaxAllowedDeviationPriceDisableConfirmationPopUp();
        }
    },

    openReviseMaxAllowedDeviationPriceDisableConfirmationPopUp: function()
    {
        Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: 'Are you sure?',
            width: 600,
            height: 400,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            onClose: function() {
                $('revise_update_price_max_allowed_deviation_mode').selectedIndex = 1;
                $('revise_update_price_max_allowed_deviation_mode').simulate('change');
            }
        });

        $('modal_dialog_message').update($('revise_price_max_max_allowed_deviation_confirmation_popup_template').innerHTML);

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '630px';
        }, 50);
    },

    revisePriceMaxAllowedDeviationDisableConfirm: function()
    {
        Windows.getFocusedWindow().close();

        $('revise_update_price_max_allowed_deviation_mode').selectedIndex = 0;
        $('revise_update_price_max_allowed_deviation_mode').simulate('change');
    },

    // ---------------------------------------

    relistMode_change: function()
    {
        $('relist_filter_user_lock_tr_container').hide();
        $('relist_send_data_tr_container').hide();
        $('magento_block_ebay_template_synchronization_form_data_relist_rules').hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_MODE_YES')) {
            $('relist_filter_user_lock_tr_container').show();
            $('relist_send_data_tr_container').show();
            $('magento_block_ebay_template_synchronization_form_data_relist_rules').show();
        }
    },

    relistQty_change: function()
    {
        var qtyType = this.getAttribute('qty_type');

        var valueContainer    = $('relist_qty_' + qtyType + '_value_container'),
            valueMaxContainer = $('relist_qty_' + qtyType + '_value_max_container'),
            itemMin           = $('relist_qty_' + qtyType + '_item_min'),
            item              = $('relist_qty_' + qtyType + '_item');

        valueContainer.hide();
        valueMaxContainer.hide();
        itemMin.hide();
        item.hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_LESS') ||
            this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_MORE')) {
            item.show();
            valueContainer.show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_BETWEEN')) {
            itemMin.show();
            valueContainer.show();
            valueMaxContainer.show();
        }
    },

    // ---------------------------------------

    stopQty_change: function()
    {
        var qtyType = this.getAttribute('qty_type');

        var valueContainer    = $('stop_qty_' + qtyType + '_value_container'),
            valueMaxContainer = $('stop_qty_' + qtyType + '_value_max_container'),
            itemMin           = $('stop_qty_' + qtyType + '_item_min'),
            item              = $('stop_qty_' + qtyType + '_item');

        valueContainer.hide();
        valueMaxContainer.hide();
        itemMin.hide();
        item.hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_LESS') ||
            this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_MORE')) {
            item.show();
            valueContainer.show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_BETWEEN')) {
            itemMin.show();
            valueContainer.show();
            valueMaxContainer.show();
        }
    },

    // ---------------------------------------

    scheduleModeChange: function()
    {
        $('schedule_interval_mode_tr','schedule_interval_value_tr',
          'magento_block_ebay_template_synchronization_form_data_schedule_week').invoke('hide');

        if (this.value == 1) {
            $('schedule_interval_mode_tr','magento_block_ebay_template_synchronization_form_data_schedule_week').invoke('show');
            $('schedule_interval_mode').simulate('change');
        }
    },

    scheduleIntervalModeChange: function()
    {
        var valueTr = $('schedule_interval_value_tr');

        valueTr.hide();
        if (EbayTemplateSynchronizationHandlerObj.isScheduleModeEnabled() && this.value == 1) {
            valueTr.show();
        }
    },

    scheduleDayModeChange: function()
    {
        var containerFrom = $(this.id.replace('mode','') + 'container_from'),
            containerTo   = $(this.id.replace('mode','') + 'container_to');

        containerFrom.hide();
        containerTo.hide();

        if (this.checked) {
            containerFrom.show();
            containerTo.show();
        }
    },

    scheduleTimeSelectChange: function()
    {
        var inputId = this.id.match('(.)*(?=_)')[0];

        var hours   = $(inputId + '_hours').value,
            minutes = $(inputId + '_minutes').value,
            ampm    = $(inputId + '_ampm').value;

        $(inputId).value =  hours + ':' + minutes + ' ' + ampm;
    }

    // ---------------------------------------
});