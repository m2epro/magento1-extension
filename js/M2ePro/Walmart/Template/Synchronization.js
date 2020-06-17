window.WalmartTemplateSynchronization = Class.create(WalmartTemplateEdit, {

    // ---------------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-synchronization-tpl-title',
                                                M2ePro.translator.translate('The specified Title is already used for other Policy. Policy Title must be unique.'),
                                                'Template_Synchronization', 'title', 'id',
                                                M2ePro.formData.id,
                                                M2ePro.php.constant('Ess_M2ePro_Helper_Component_Walmart::NICK'));

        Validation.add('M2ePro-input-time', M2ePro.translator.translate('Wrong time format string.'), function(value) {
            return value.match(/^\d{2}:\d{2}$/g);
        });

        Validation.add('validate-qty', M2ePro.translator.translate('Wrong value. Only integer numbers.'), function(value, el) {

            if (!el.up('tr').visible()) {
                return true;
            }

            if (value.match(/[^\d]+/g)) {
                return false;
            }

            if (value <= 0) {
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

            if (WalmartTemplateSynchronizationObj.isRelistModeDisabled()) {
                return true;
            }

            if (WalmartTemplateSynchronizationObj.isStopModeDisabled()) {
                return true;
            }

            if ($('stop_status_disabled').value == 1 && $('relist_status_enabled').value == 0) {
                return false;
            }

            return true;
        });

        Validation.add('M2ePro-validate-stop-relist-conditions-stock-availability', M2ePro.translator.translate('Inconsistent Settings in Relist and Stop Rules.'), function(value, el) {

            if (WalmartTemplateSynchronizationObj.isRelistModeDisabled()) {
                return true;
            }

            if (WalmartTemplateSynchronizationObj.isStopModeDisabled()) {
                return true;
            }

            if ($('stop_out_off_stock').value == 1 && $('relist_is_in_stock').value == 0) {
                return false;
            }

            return true;
        });

        Validation.add('M2ePro-validate-stop-relist-conditions-item-qty', M2ePro.translator.translate('Inconsistent Settings in Relist and Stop Rules.'), function(value, el) {

            if (WalmartTemplateSynchronizationObj.isRelistModeDisabled()) {
                return true;
            }

            if (WalmartTemplateSynchronizationObj.isStopModeDisabled()) {
                return true;
            }

            var stopMaxQty = 0,
                relistMinQty = 0;

            switch (parseInt($('stop_qty_calculated').value)) {

                case M2ePro.php.constant('Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE'):
                    return true;
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_YES'):
                    stopMaxQty = parseInt($('stop_qty_calculated_value').value);
                    break;
            }

            switch (parseInt($('relist_qty_calculated').value)) {

                case M2ePro.php.constant('Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE'):
                    return false;
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_YES'):
                    relistMinQty = parseInt($('relist_qty_calculated_value').value);
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

    isStopModeDisabled: function()
    {
        return $('stop_mode').value == 0;
    },

    // ---------------------------------------

    duplicate_click: function(headId)
    {
        this.setValidationCheckRepetitionValue('M2ePro-synchronization-tpl-title',
                                                M2ePro.translator.translate('The specified Title is already used for other Policy. Policy Title must be unique.'),
                                                'Template_Synchronization', 'title', '','',
                                                M2ePro.php.constant('Ess_M2ePro_Helper_Component_Walmart::NICK'));

        CommonObj.duplicate_click(headId, M2ePro.translator.translate('Add Synchronization Policy'));
    },

    // ---------------------------------------

    getNavigationTabName: function(element)
    {
        return $('walmart_template_synchronization_edit_form_navigation_bar_' + element.id.split('_').shift());
    },

    // ---------------------------------------

    setVirtualTabsAsInactive: function()
    {
        $$('#walmart_template_synchronization_edit_form_container .form_content').invoke('hide');
        $$('#walmart_template_synchronization_edit_form_container .navigation_bar').invoke('removeClassName','active');
    },

    setVirtualTabAsActive: function()
    {
        WalmartTemplateSynchronizationObj.setVirtualTabsAsInactive();

        $(this.id.replace('navigation_bar','content')).show();
        this.addClassName('active');
    },

    setVirtualTabAsChanged: function()
    {
        var tab = WalmartTemplateSynchronizationObj.getNavigationTabName(this);
        tab && tab.addClassName('changed');
    },

    checkVirtualTabValidation: function()
    {
        var failedItems = $$('#walmart_template_synchronization_edit_form_container .validation-failed');

        $$('#walmart_template_synchronization_edit_form_container .navigation_bar').invoke('removeClassName','error');

        failedItems.each(function(el) {
            var tab = WalmartTemplateSynchronizationObj.getNavigationTabName(el);
            tab.addClassName('error');
        });

        if (failedItems.length > 0) {
            WalmartTemplateSynchronizationObj.setVirtualTabsAsInactive();

            var tab = WalmartTemplateSynchronizationObj.getNavigationTabName(failedItems.shift());
            $(tab.id.replace('navigation_bar','content')).show();
            tab.addClassName('active');
        }
    },

    // ---------------------------------------

    validateForm: function()
    {
        var validationResult = true;

        validationResult &= editForm.validate();
        validationResult &= Validation.validate($('title'));

        WalmartTemplateSynchronizationObj.checkVirtualTabValidation();

        return validationResult;
    },

    // ---------------------------------------

    stopQty_change: function()
    {
        var valueContainer    = $('stop_qty_calculated_value');
        valueContainer.hide();
        this.style.width = '280px';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_YES')) {
            valueContainer.show();
            this.style.width = '200px';
        }
    },

    listMode_change: function()
    {
        $('magento_block_walmart_template_synchronization_list_rules').hide();
        $('magento_block_walmart_template_synchronization_list_advanced').hide();

        if ($('list_mode').value == 1) {
            $('magento_block_walmart_template_synchronization_list_rules').show();
            $('magento_block_walmart_template_synchronization_list_advanced').show();
        }
    },

    listQty_change: function()
    {
        var valueContainer    = $('list_qty_calculated_value');
        valueContainer.hide();
        this.style.width = '280px';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_YES')) {
            valueContainer.show();
            this.style.width = '200px';
        }
    },

    relistMode_change: function()
    {
        $('relist_filter_user_lock_tr_container').hide();
        $('magento_block_walmart_template_synchronization_relist_rules').hide();
        $('magento_block_walmart_template_synchronization_relist_advanced').hide();

        if ($('relist_mode').value == 1) {
            $('relist_filter_user_lock_tr_container').show();
            $('magento_block_walmart_template_synchronization_relist_rules').show();
            $('magento_block_walmart_template_synchronization_relist_advanced').show();
        }
    },

    relistQty_change: function()
    {
        var valueContainer    = $('relist_qty_calculated_value');
        valueContainer.hide();
        this.style.width = '280px';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_YES')) {
            valueContainer.show();
            this.style.width = '200px';
        }
    },

    reviseQty_change: function()
    {
        if (this.value == 1) {
            $('revise_update_qty_max_applied_value_mode_tr').show();
            $('revise_update_qty_max_applied_value_line_tr').show();
            $('revise_update_qty_max_applied_value_mode').simulate('change');
        } else {
            $('revise_update_qty_max_applied_value_mode_tr').hide();
            $('revise_update_qty_max_applied_value_line_tr').hide();
            $('revise_update_qty_max_applied_value_mode').value = 0;
        }
    },

    reviseQtyMaxAppliedValueMode_change: function(event)
    {
        var self = WalmartTemplateSynchronizationObj;

        $('revise_update_qty_max_applied_value').hide();
        this.style.width = '280px';

        if (this.value == 1) {
            $('revise_update_qty_max_applied_value').show();
            this.style.width = '200px';
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

    stopMode_change: function ()
    {
        $('magento_block_walmart_template_synchronization_stop_rules').hide();
        $('magento_block_walmart_template_synchronization_stop_advanced').hide();

        if ($('stop_mode').value == 1) {
            $('magento_block_walmart_template_synchronization_stop_rules').show();
            $('magento_block_walmart_template_synchronization_stop_advanced').show();
        }
    },

    // ---------------------------------------

    listAdvancedRules_change: function()
    {
        $('list_advanced_rules_filters_container').hide();
        $('list_advanced_rules_filters_warning').hide();

        if (this.value == 1) {
            $('list_advanced_rules_filters_container').show();
            $('list_advanced_rules_filters_warning').show();
        }
    },

    relistAdvancedRules_change: function()
    {
        $('relist_advanced_rules_filters_container').hide();
        $('relist_advanced_rules_filters_warning').hide();

        if (this.value == 1) {
            $('relist_advanced_rules_filters_container').show();
            $('relist_advanced_rules_filters_warning').show();
        }
    },

    stopAdvancedRules_change: function()
    {
        $('stop_advanced_rules_filters_container').hide();
        $('stop_advanced_rules_filters_warning').hide();

        if (this.value == 1) {
            $('stop_advanced_rules_filters_container').show();
            $('stop_advanced_rules_filters_warning').show();
        }
    }

    // ---------------------------------------
});