WalmartAccountHandler = Class.create();
WalmartAccountHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    initialize: function()
    {
        this.accountHandler = new AccountHandler();

        this.setValidationCheckRepetitionValue('M2ePro-account-title',
                                                M2ePro.translator.translate('The specified Title is already used for other Account. Account Title must be unique.'),
                                                'Account', 'title', 'id',
                                                M2ePro.formData.id,
                                                M2ePro.php.constant('Ess_M2ePro_Helper_Component_Walmart::NICK'));

        Validation.add('M2ePro-marketplace-merchant', M2ePro.translator.translate('M2E Pro was not able to get access to the Walmart Account. Please, make sure, that you choose correct Option on Private Key Authorization Page and enter correct Consumer ID.'), function(value, el) {

            // reset error message to the default
            this.error = M2ePro.translator.translate('M2E Pro was not able to get access to the Walmart Account. Please, make sure, that you choose correct Option on Private Key Authorization Page and enter correct Consumer ID.');

            var consumerId    = $('consumer_id').value;
            var privateKey     = $('private_key').value;
            var marketplace_id = $('marketplace_id').value;

            var checkResult = false;
            var checkReason = null;

            new Ajax.Request(M2ePro.url.get('adminhtml_walmart_account/checkAuth'), {
                method: 'post',
                asynchronous: false,
                parameters: {
                    consumer_id    : consumerId,
                    private_key    : privateKey,
                    marketplace_id : marketplace_id
                },
                onSuccess: function(transport) {
                    var response = transport.responseText.evalJSON();
                    checkResult = response['result'];
                    checkReason = response['reason'];
                }
            });

            if (checkReason != null && typeof checkReason != 'undefined') {
                this.error = M2ePro.translator.translate('M2E Pro was not able to get access to the Walmart Account. Reason: %error_message%').replace('%error_message%', checkReason);
            }

            return checkResult;

        });

        Validation.add('M2ePro-account-customer-id', M2ePro.translator.translate('No Customer entry is found for specified ID.'), function(value) {

            var checkResult = false;

            if ($('magento_orders_customer_id_container').getStyle('display') == 'none') {
                return true;
            }

            new Ajax.Request(M2ePro.url.get('adminhtml_general/checkCustomerId'), {
                method: 'post',
                asynchronous: false,
                parameters: {
                    customer_id: value,
                    id         : M2ePro.formData.id
                },
                onSuccess: function(transport) {
                    checkResult = transport.responseText.evalJSON()['ok'];
                }
            });

            return checkResult;
        });

        Validation.add('M2ePro-account-order-number-prefix', M2ePro.translator.translate('Prefix length should not be greater than 5 characters.'), function(value) {

            if ($('magento_orders_number_prefix_mode').value == 0) {
                return true;
            }

            return value.length <= 5;
        });

        Validation.add('M2ePro-require-select-attribute', M2ePro.translator.translate('If Yes is chosen, you must select at least one Attribute for Product Mapping.'), function(value, el) {

            if ($('other_listings_mapping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_MODE_NO')) {
                return true;
            }

            var isAttributeSelected = false;

            $$('.attribute-mode-select').each(function(obj) {
                if (obj.value != 0) {
                    isAttributeSelected = true;
                }
            });

            return isAttributeSelected;
        });

        Validation.add('M2ePro-validate-price-coefficient', M2ePro.translator.translate('Coefficient is not valid.'), function(value) {

            if (value == '') {
                return true;
            }

            if (value == '0' || value == '0%') {
                return false;
            }

            return value.match(/^[+-]?\d+[.]?\d*[%]?$/g);
        });
    },

    // ---------------------------------------

    completeStep: function()
    {
        window.opener.completeStep = 1;
        window.close();
    },

    // ---------------------------------------

    delete_click: function(accountId)
    {
        this.accountHandler.on_delete_popup(accountId);
    },

    // ---------------------------------------

    showGetAccessData: function(id)
    {
        $$('.marketplaces_view_element').each(function(obj) {
            obj.hide();
        });

        $('marketplaces_register_url_container_'+id).show();
    },

    // ---------------------------------------

    other_listings_synchronization_change: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES')) {
            $('other_listings_mapping_mode_tr').show();
            $('other_listings_store_view_tr').show();
        } else {
            $('other_listings_mapping_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_MODE_NO');
            $('other_listings_mapping_mode').simulate('change');
            $('other_listings_mapping_mode_tr').hide();
            $('other_listings_store_view_tr').hide();
        }
    },

    other_listings_mapping_mode_change: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_MODE_YES')) {
            $('magento_block_walmart_accounts_other_listings_product_mapping').show();
        } else {
            $('magento_block_walmart_accounts_other_listings_product_mapping').hide();

            $('mapping_sku_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE');
            $('mapping_upc_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_UPC_MODE_NONE');
            $('mapping_gtin_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_GTIN_MODE_NONE');
            $('mapping_wpid_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_WPID_MODE_NONE');
            $('mapping_title_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE');
        }

        $('mapping_sku_mode').simulate('change');
        $('mapping_upc_mode').simulate('change');
        $('mapping_gtin_mode').simulate('change');
        $('mapping_wpid_mode').simulate('change');
        $('mapping_title_mode').simulate('change');
    },

    // ---------------------------------------

    mapping_sku_mode_change: function()
    {
        var self = WalmartAccountHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE')) {
            $('mapping_sku_priority_td').hide();
        } else {
            $('mapping_sku_priority_td').show();
        }

        $('mapping_sku_attribute').value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('mapping_sku_attribute'));
        }
    },

    mapping_upc_mode_change: function()
    {
        var self = WalmartAccountHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_UPC_MODE_NONE')) {
            $('mapping_upc_priority_td').hide();
        } else {
            $('mapping_upc_priority_td').show();
        }

        $('mapping_upc_attribute').value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_UPC_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('mapping_upc_attribute'));
        }
    },

    mapping_gtin_mode_change: function()
    {
        var self = WalmartAccountHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_GTIN_MODE_NONE')) {
            $('mapping_gtin_priority_td').hide();
        } else {
            $('mapping_gtin_priority_td').show();
        }

        $('mapping_gtin_attribute').value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_GTIN_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('mapping_gtin_attribute'));
        }
    },

    mapping_wpid_mode_change: function()
    {
        var self = WalmartAccountHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_WPID_MODE_NONE')) {
            $('mapping_wpid_priority_td').hide();
        } else {
            $('mapping_wpid_priority_td').show();
        }

        $('mapping_wpid_attribute').value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_WPID_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('mapping_wpid_attribute'));
        }
    },

    mapping_title_mode_change: function()
    {
        var self = WalmartAccountHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE')) {
            $('mapping_title_priority_td').hide();
        } else {
            $('mapping_title_priority_td').show();
        }

        $('mapping_title_attribute').value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('mapping_title_attribute'));
        }
    },

    // ---------------------------------------

    magentoOrdersListingsModeChange: function()
    {
        var self = WalmartAccountHandlerObj;

        if ($('magento_orders_listings_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_LISTINGS_MODE_YES')) {
            $('magento_orders_listings_store_mode_container').show();
        } else {
            $('magento_orders_listings_store_mode_container').hide();
        }

        $('magento_orders_listings_store_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT');
        self.magentoOrdersListingsStoreModeChange();

        self.changeVisibilityForOrdersModesRelatedBlocks();
    },

    magentoOrdersListingsStoreModeChange: function()
    {
        if ($('magento_orders_listings_store_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM')) {
            $('magento_orders_listings_store_id_container').show();
        } else {
            $('magento_orders_listings_store_id_container').hide();
        }

        $('magento_orders_listings_store_id').value = '';
    },

    magentoOrdersListingsOtherModeChange: function()
    {
        var self = WalmartAccountHandlerObj;

        if ($('magento_orders_listings_other_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_LISTINGS_OTHER_MODE_YES')) {
            $('magento_orders_listings_other_product_mode_container').show();
            $('magento_orders_listings_other_store_id_container').show();
        } else {
            $('magento_orders_listings_other_product_mode_container').hide();
            $('magento_orders_listings_other_store_id_container').hide();
        }

        $('magento_orders_listings_other_product_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE');
        $('magento_orders_listings_other_store_id').value = '';

        self.magentoOrdersListingsOtherProductModeChange();
        self.changeVisibilityForOrdersModesRelatedBlocks();
    },

    magentoOrdersListingsOtherProductModeChange: function()
    {
        if ($('magento_orders_listings_other_product_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE')) {
            $('magento_orders_listings_other_product_mode_note').hide();
            $('magento_orders_listings_other_product_tax_class_id_container').hide();
        } else {
            $('magento_orders_listings_other_product_mode_note').show();
            $('magento_orders_listings_other_product_tax_class_id_container').show();
        }
    },

    magentoOrdersNumberSourceChange: function()
    {
        var self = WalmartAccountHandlerObj;
        self.renderOrderNumberExample();
    },

    magentoOrdersNumberPrefixModeChange: function()
    {
        var self = WalmartAccountHandlerObj;

        if ($('magento_orders_number_prefix_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_NUMBER_PREFIX_MODE_YES')) {
            $('magento_orders_number_prefix_container').show();
        } else {
            $('magento_orders_number_prefix_container').hide();
            $('magento_orders_number_prefix_prefix').value = '';
        }

        self.renderOrderNumberExample();
    },

    magentoOrdersNumberPrefixPrefixChange: function()
    {
        var self = WalmartAccountHandlerObj;
        self.renderOrderNumberExample();
    },

    renderOrderNumberExample: function()
    {
        var orderNumber = $('sample_magento_order_id').value;
        if ($('magento_orders_number_source').value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_NUMBER_SOURCE_CHANNEL')) {
            orderNumber = $('sample_walmart_order_id').value;
        }

        if ($('magento_orders_number_prefix_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_NUMBER_PREFIX_MODE_YES')) {
            orderNumber = $('magento_orders_number_prefix_prefix').value + orderNumber;
        }

        $('order_number_example_container').update(orderNumber);
    },

    magentoOrdersCustomerModeChange: function()
    {
        var customerMode = $('magento_orders_customer_mode').value;

        if (customerMode == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED')) {
            $('magento_orders_customer_id_container').show();
            $('magento_orders_customer_id').addClassName('M2ePro-account-product-id');
        } else {  // M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::ORDERS_CUSTOMER_MODE_GUEST') || M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::ORDERS_CUSTOMER_MODE_NEW')
            $('magento_orders_customer_id_container').hide();
            $('magento_orders_customer_id').removeClassName('M2ePro-account-product-id');
        }

        var action = (customerMode == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_CUSTOMER_MODE_NEW')) ? 'show' : 'hide';
        $('magento_orders_customer_new_website_id_container')[action]();
        $('magento_orders_customer_new_group_id_container')[action]();
        $('magento_orders_customer_new_notifications_container')[action]();

        $('magento_orders_customer_id').value = '';
        $('magento_orders_customer_new_website_id').value = '';
        $('magento_orders_customer_new_group_id').value = '';
        $('magento_orders_customer_new_notifications').value = '';
//        $('magento_orders_customer_new_newsletter_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_CUSTOMER_NEW_SUBSCRIPTION_MODE_NO');
    },

    magentoOrdersStatusMappingModeChange: function()
    {
        // Reset dropdown selected values to default
        $('magento_orders_status_mapping_processing').value = M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING');
        $('magento_orders_status_mapping_shipped').value = M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED');

        // Default auto create invoice & shipment
        $('magento_orders_invoice_mode').checked = true;
        $('magento_orders_shipment_mode').checked = true;

        var disabled = $('magento_orders_status_mapping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT');
        $('magento_orders_status_mapping_processing').disabled = disabled;
        $('magento_orders_status_mapping_shipped').disabled = disabled;
        $('magento_orders_invoice_mode').disabled = disabled;
        $('magento_orders_shipment_mode').disabled = disabled;
    },

    changeVisibilityForOrdersModesRelatedBlocks: function()
    {
        var self = WalmartAccountHandlerObj;

        if ($('magento_orders_listings_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_LISTINGS_MODE_NO') &&
            $('magento_orders_listings_other_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_LISTINGS_OTHER_MODE_NO')) {

            $('magento_block_walmart_accounts_magento_orders_number').hide();
            $('magento_orders_number_source').value = M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO');
            $('magento_orders_number_prefix_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_NUMBER_PREFIX_MODE_NO');
            self.magentoOrdersNumberPrefixModeChange();

            $('magento_block_walmart_accounts_magento_orders_customer').hide();
            $('magento_orders_customer_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST');
            self.magentoOrdersCustomerModeChange();

            $('magento_block_walmart_accounts_magento_orders_status_mapping').hide();
            $('magento_orders_status_mapping_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT');
            self.magentoOrdersStatusMappingModeChange();

            $('magento_block_walmart_accounts_magento_orders_tax').hide();
            $('magento_orders_tax_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_TAX_MODE_MIXED');
        } else {
            $('magento_block_walmart_accounts_magento_orders_number').show();
            $('magento_block_walmart_accounts_magento_orders_customer').show();
            $('magento_block_walmart_accounts_magento_orders_status_mapping').show();
            $('magento_block_walmart_accounts_magento_orders_tax').show();
        }
    },

    vatCalculationModeChange: function()
    {
        $('is_magento_invoice_creation_disabled_tr').hide();

        if ($('is_vat_calculation_service_enabled').value == 1) {
            $('is_magento_invoice_creation_disabled_tr').show();
        }
    },

    // ---------------------------------------

    saveAndClose: function()
    {
        var url = typeof M2ePro.url.urls.formSubmit == 'undefined' ?
            M2ePro.url.formSubmit + 'back/'+base64_encode('list')+'/' :
            M2ePro.url.get('formSubmit', {'back': base64_encode('list')});

        if (!editForm.validate()) {
            return;
        }

        new Ajax.Request(url, {
            method: 'post',
            parameters: Form.serialize($(editForm.formId)),
            onSuccess: function() {
                window.close();
            }
        });
    },

    // ---------------------------------------
});