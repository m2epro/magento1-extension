OrderMerchantFulfillmentHandler = Class.create();
OrderMerchantFulfillmentHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    orderId: null,
    validateCustomDimension: true,
    cachedFields: {},

    // ---------------------------------------

    initialize: function()
    {
        var self = this;

        Validation.add('M2ePro-validate-must-arrive-date', M2ePro.translator.translate('Please enter a valid date.'), function(value) {
            return value.match('^[0-9]{4}-[0-9]{2}-[0-9]{1,2}$');
        });

        Validation.add('M2ePro-validate-dimension', M2ePro.translator.translate('Please select an option.'), function(value) {
            return value != M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon_MerchantFulfillment::DIMENSION_SOURCE_NONE');
        });

        Validation.add('M2ePro-validate-required-custom-dimension', M2ePro.translator.translate('This is a required fields.'), function(value, element) {
            if (self.validateCustomDimension) {
                var validationResult = Validation.get('M2ePro-required-when-visible').test(value, element);
                self.validateCustomDimension = validationResult;
                return validationResult;
            } else {
                return true;
            }
        });

        Validation.add('M2ePro-validate-custom-dimension', M2ePro.translator.translate('Please enter a number greater than 0 in this fields.'), function(value) {
            if (self.validateCustomDimension) {
                var validationResult = Validation.get('validate-greater-than-zero').test(value);
                self.validateCustomDimension = validationResult;
                return validationResult;
            } else {
                return true;
            }
        });
    },

    // ---------------------------------------

    openPopUp: function(content, customConfig)
    {
        var self = this;
        var title = M2ePro.translator.translate('Amazon\'s Shipping Services');

        var config = {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 20,
            width: 800,
            maxHeight: 500,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() {
                self.popUp = null;
                self.cachedFields = {};

                if ($('fulfillment_must_arrive_by_date')) {
                    self.onPopupScroll();
                    Event.stopObserving(window, 'scroll', self.onPopupScroll);
                }

                return true;
            }
        };

        for (var param in customConfig) {
            config[param] = customConfig[param];
        }

        if (!this.popUp) {
            this.popUp = Dialog.info(content, config);
        } else {
            $('modal_dialog_message').update(content);
            var newDimensions = $('modal_dialog_message').getDimensions();
            this.popUp.setTitle(title);
            this.popUp._recenter();
        }

        $('modal_dialog_message').innerHTML.evalScripts();

        this.autoHeightFix();

        return this.popUp;
    },

    closePopUp: function()
    {
        if (this.popUp) {
            this.popUp.close();
        }
    },

    // ---------------------------------------

    validate: function()
    {
        this.validateCustomDimension = true;
        var validationResult = [];

        if ($('fulfillment_form')) {
            validationResult = Form.getElements('fulfillment_form').collect(Validation.validate);
        }

        if (validationResult.indexOf(false) != -1) {
            $('fulfillment_form_container').scrollTop = validationResult.indexOf(false) * 20;
            return false;
        }

        return true;
    },

    // ---------------------------------------

    getPopupAction: function(orderId)
    {
        var self = this;

        if (orderId && this.orderId != orderId) {
            this.orderId = orderId;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_order_merchantFulfillment/getPopup', {order_id: this.orderId}), {
            method: 'post',
            onSuccess: function(transport) {
                var data = transport.responseText.evalJSON(true);

                if (data.status) {
                    self.openPopUp(data.html);
                    self.cacheForm(false);
                    $('fulfillment_form_container').observe('scroll', self.onPopupScroll);
                    Event.observe(window, 'scroll', self.onPopupScroll);
                } else {
                    self.openPopUp(data.html, {width: 400});
                }
            }
        });
    },

    getShippingServicesAction: function()
    {
        if (!this.validate()) {
            return;
        }

        this.cacheForm(true);

        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_order_merchantFulfillment/getShippingServices', {order_id: this.orderId}), {
            method: 'post',
            parameters: Form.serialize($('fulfillment_form')),
            onSuccess: function(transport) {
                self.openPopUp(transport.responseText);
            }
        });
    },

    createShippingOfferAction: function()
    {
        if (!confirm(M2ePro.translator.translate('Are you sure you want to create Shipment now?'))) {
            return;
        }

        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_order_merchantFulfillment/createShippingOffer', {order_id: this.orderId}), {
            method: 'post',
            parameters: Form.serialize($('fulfillment_form')),
            onSuccess: function(transport) {
                self.openPopUp(transport.responseText);
            }
        });
    },

    cancelShippingOfferAction: function()
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_order_merchantFulfillment/cancelShippingOffer', {order_id: this.orderId}), {
            method: 'post',
            onSuccess: function(transport) {
                var data = transport.responseText.evalJSON(true);

                if (data['success']) {
                    self.getPopupAction();
                } else {
                    alert('Internal error: ' + data['error_message']);
                }
            }
        });
    },

    refreshDataAction: function()
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_order_merchantFulfillment/refreshData', {order_id: this.orderId}), {
            method: 'post',
            onSuccess: function(transport) {
                var data = transport.responseText.evalJSON(true);

                if (data['success']) {
                    self.getPopupAction();
                } else {
                    alert('Internal error: ' + data['error_message']);
                }
            }
        });
    },

    resetDataAction: function()
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_order_merchantFulfillment/resetData', {order_id: this.orderId}), {
            method: 'post',
            onSuccess: function(transport) {
                var data = transport.responseText.evalJSON(true);

                if (data['success']) {
                    self.getPopupAction();
                } else {
                    alert('Internal error.');
                }

            }
        });
    },

    getShippingLabelAction: function()
    {
        this.openWindow(M2ePro.url.get('adminhtml_common_amazon_order_merchantFulfillment/getLabel', {
            order_id: this.orderId
        }));
    },

    markAsShippedAction: function(orderId)
    {
        var self = this;
        this.orderId = orderId;

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_order_merchantFulfillment/markAsShipped', {order_id: this.orderId}), {
            method: 'post',
            onSuccess: function(transport) {
                var data = transport.responseText.evalJSON(true);

                if (data['success']) {
                    setLocation(
                        M2ePro.url.get('adminhtml_common_amazon_order/updateShippingStatus', {
                            id: self.orderId
                        })
                    );
                } else {
                    self.openPopUp(data['html'], {width: 400});
                }
            }
        });
    },

    useMerchantFulfillmentAction: function()
    {
        this.closePopUp();
        this.getPopupAction();
    },

    // ---------------------------------------

    onPopupScroll: function()
    {
        if (!$('fulfillment_must_arrive_by_date')) {
            return;
        }

        var bounds = $('fulfillment_must_arrive_by_date').getBoundingClientRect();

        Calendar.setup({
            inputField: "fulfillment_must_arrive_by_date",
            ifFormat: "%Y-%m-%d",
            singleClick: true,
            cache: true,
            position: [
                bounds.left + bounds.width,
                bounds.top + window.scrollY,
            ]
        });

        var calendar = $$('.calendar');

        if (calendar.length) {
            calendar = calendar[0];
            calendar.hide();
        }
    },

    shippingServicesChange: function()
    {
        $('fulfillment_save_shipping_services').enable();
        $('fulfillment_save_shipping_services').removeClassName('disabled');
    },

    packageDimensionChange: function()
    {
        if(this.value == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon_MerchantFulfillment::DIMENSION_SOURCE_NONE')) {
            $('fulfillment_package_dimension_custom').hide();
            $('fulfillment_package_dimension_source').value = M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon_MerchantFulfillment::DIMENSION_SOURCE_NONE');
        } else if(this.value == M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon_MerchantFulfillment::DIMENSION_SOURCE_CUSTOM')) {
            $('fulfillment_package_dimension_custom').show();
            $('fulfillment_package_dimension_source').value = M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon_MerchantFulfillment::DIMENSION_SOURCE_CUSTOM');
        } else {
            $('fulfillment_package_dimension_custom').hide();
            $('fulfillment_package_dimension_width').clear();
            $('fulfillment_package_dimension_length').clear();
            $('fulfillment_package_dimension_height').clear();
            $('fulfillment_package_dimension_source').value = M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon_MerchantFulfillment::DIMENSION_SOURCE_PREDEFINED');
        }
    },

    shippingCountryChange: function()
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_order/getCountryRegions'), {
            method: 'post',
            parameters: {
                country: this.value
            },
            onSuccess: function(transport) {
                var data = transport.responseText.evalJSON(true);

                if (data.length == 0) {
                    var inputHtml = '<input type="text" id="fulfillment_ship_from_address_region" name="ship_from_address_region_state" class="input-text" />';
                    $('fulfillment_ship_from_address_region_state').update(inputHtml);
                    $('fulfillment_ship_from_address_region').value = $('fulfillment_ship_from_address_region_state_default').value;
                } else {
                    var optionHtml = '<select id="fulfillment_ship_from_address_state" name="ship_from_address_region_state">';

                    data.each(function(item) {
                        var selected = '';
                        var regionValue = $('fulfillment_ship_from_address_region_state_default').value;
                        if (item.id == regionValue || item.label == regionValue) {
                            selected = 'selected="selected"';
                        }
                        optionHtml += '<option value="' + item.label + '" ' + selected + '>' + item.label + '</option>';
                    });

                    optionHtml += '</select>';

                    $('fulfillment_ship_from_address_region_state').update(optionHtml);

                    var firstOption = $('fulfillment_ship_from_address_state').select('option:first')[0];
                    firstOption.value = '';
                    firstOption.hide();
                }

                $('fulfillment_ship_from_address_region_state_default').clear();
            }
        });
    },

    // ---------------------------------------

    cacheForm: function(isSerialize)
    {
        var self = this;
        var fieldsToCache = [
            'fulfillment_must_arrive_by_date',
            'fulfillment_general_declared_value',
            'fulfillment_package_dimension_source',
            'fulfillment_package_dimension_predefined',
            'fulfillment_package_dimension_length',
            'fulfillment_package_dimension_width',
            'fulfillment_package_dimension_height',
            'fulfillment_package_weight',
        ];

        if (isSerialize) {
            this.cachedFields.cached = true;
            fieldsToCache.forEach(function(field){
                self.cachedFields[field] = $(field).value;
            });
        } else if(this.cachedFields.hasOwnProperty('cached')) {
            fieldsToCache.forEach(function(field){
                $(field).value = self.cachedFields[field];
            });
            $('fulfillment_package_dimension_predefined').simulate('change');
        }
    },

    // ---------------------------------------

    autoHeightFix: function()
    {
        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '700px';

            if ($('fulfillment_form_container')) {
                var containerHeight = parseInt($('fulfillment_form_container').getStyle('height'));

                if($('fulfillment_form_container').scrollHeight <= containerHeight){
                    $('fulfillment_form_container').setStyle({
                        paddingRight: 0
                    });
                }
            }
        }, 50);
    }

    // ---------------------------------------
});