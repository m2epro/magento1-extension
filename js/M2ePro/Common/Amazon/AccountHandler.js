CommonAmazonAccountHandler = Class.create();
CommonAmazonAccountHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-account-title',
                                                M2ePro.translator.translate('The specified Title is already used for other Account. Account Title must be unique.'),
                                                'Account', 'title', 'id',
                                                M2ePro.formData.id,
                                                M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon::NICK'));

        Validation.add('M2ePro-marketplace-merchant', M2ePro.translator.translate('M2E Pro was not able to get access to the Amazon Account. Please, make sure, that you choose correct Option on MWS Authorization Page and enter correct Merchant ID.'), function(value, el) {

            // reset error message to the default
            this.error = M2ePro.translator.translate('M2E Pro was not able to get access to the Amazon Account. Please, make sure, that you choose correct Option on MWS Authorization Page and enter correct Merchant ID.');

            var merchant_id    = $('merchant_id').value;
            var token          = $('token').value;
            var marketplace_id = $('marketplace_id').value;

            var pattern = /^[A-Z0-9]*$/;
            if (!pattern.test(merchant_id)) {
                return false;
            }

            var checkResult = false;
            var checkReason = null;

            new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_account/checkAuth'), {
                method: 'post',
                asynchronous: false,
                parameters: {
                    merchant_id    : merchant_id,
                    token          : token,
                    marketplace_id : marketplace_id
                },
                onSuccess: function(transport) {
                    var response = transport.responseText.evalJSON();
                    checkResult = response['result'];
                    checkReason = response['reason'];
                }
            });

            if (checkReason != null) {
                this.error = M2ePro.translator.translate('M2E Pro was not able to get access to the Amazon Account. Reason: %error_message%').replace('%error_message%', checkReason);
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

            if ($('other_listings_mapping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MAPPING_MODE_NO')) {
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

        Validation.add('M2ePro-account-repricing-price-value', M2ePro.translator.translate('Invalid input data. Decimal value required. Example 12.05'), function(value, el) {

            if (!el.up('tr').visible()) {
                return true;
            }

            if (!value.match(/^\d+[.]?\d*?$/g)) {
                return false;
            }

            if (value <= 0) {
                return false;
            }

            return true;
        });

        Validation.add('M2ePro-account-repricing-price-percent', M2ePro.translator.translate('Please enter correct value.'), function(value, el) {

            if (!el.up('tr').visible()) {
                return true;
            }

            if (!value.match(/^\d+$/g)) {
                return false;
            }

            if (value <= 0 || value > 100) {
                return false;
            }

            return true;

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

    delete_click: function()
    {
        if (!confirm(M2ePro.translator.translate('Be attentive! By Deleting Account you delete all information on it from M2E Pro Server. This will cause inappropriate work of all Accounts\' copies.'))) {
            return;
        }
        setLocation(M2ePro.url.get('deleteAction'));
    },

    // ---------------------------------------

    changeMarketplace: function(id)
    {
        var self = AmazonAccountHandlerObj;

        $$('.marketplaces_view_element').each(function(obj) {
            obj.hide();
        });

        $('marketplaces_related_store_id_container').show();
        $('marketplaces_merchant_id_container').show();
        $('marketplaces_token_container').show();

        self.showGetAccessData(id);

//        if ($('marketplace_current_mode_'+id).value == 0) {
//            $('marketplaces_register_url_container_'+id).show();
//            $('marketplaces_application_name_container_'+id).show();
//            $('marketplaces_developer_key_container_'+id).show();
//        }
    },

    showGetAccessData: function(id)
    {
        $('marketplaces_application_name_container').show();

        $('marketplaces_developer_key_container_'+id).show();
        $('marketplaces_register_url_container_'+id).show();
    },

    // ---------------------------------------

    other_listings_synchronization_change: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES')) {
            $('other_listings_mapping_mode_tr').show();
            $('other_listings_store_view_tr').show();
        } else {
            $('other_listings_mapping_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MAPPING_MODE_NO');
            $('other_listings_mapping_mode').simulate('change');
            $('other_listings_mapping_mode_tr').hide();
            $('other_listings_store_view_tr').hide();
        }
    },

    other_listings_mapping_mode_change: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MAPPING_MODE_YES')) {
            $('magento_block_amazon_accounts_other_listings_product_mapping').show();
            $('magento_block_amazon_accounts_other_listings_move_mode').show();
        } else {
            $('magento_block_amazon_accounts_other_listings_product_mapping').hide();
            $('magento_block_amazon_accounts_other_listings_move_mode').hide();

            $('other_listings_move_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MOVE_TO_LISTINGS_DISABLED');
            $('mapping_general_id_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_NONE');
            $('mapping_sku_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE');
            $('mapping_title_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE');
        }

        $('mapping_general_id_mode').simulate('change');
        $('mapping_sku_mode').simulate('change');
        $('mapping_title_mode').simulate('change');

        $('other_listings_move_mode').simulate('change');
    },

    // ---------------------------------------

    mapping_general_id_mode_change: function()
    {
        var self = AmazonAccountHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_NONE')) {
            $('mapping_general_id_priority_td').hide();
        } else {
            $('mapping_general_id_priority_td').show();
        }

        $('mapping_general_id_attribute').value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('mapping_general_id_attribute'));
        }
    },

    mapping_sku_mode_change: function()
    {
        var self = AmazonAccountHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE')) {
            $('mapping_sku_priority_td').hide();
        } else {
            $('mapping_sku_priority_td').show();
        }

        $('mapping_sku_attribute').value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('mapping_sku_attribute'));
        }
    },

    mapping_title_mode_change: function()
    {
        var self = AmazonAccountHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE')) {
            $('mapping_title_priority_td').hide();
        } else {
            $('mapping_title_priority_td').show();
        }

        $('mapping_title_attribute').value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('mapping_title_attribute'));
        }
    },

    // ---------------------------------------

    move_mode_change: function()
    {
        if ($('other_listings_move_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MOVE_TO_LISTINGS_ENABLED')) {
            $('other_listings_move_synch_tr').show();
        } else {
            $('other_listings_move_synch_tr').hide();
        }
    },

    // ---------------------------------------

    magentoOrdersListingsModeChange: function()
    {
        var self = AmazonAccountHandlerObj;

        if ($('magento_orders_listings_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_LISTINGS_MODE_YES')) {
            $('magento_orders_listings_store_mode_container').show();
        } else {
            $('magento_orders_listings_store_mode_container').hide();
        }

        $('magento_orders_listings_store_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT');
        self.magentoOrdersListingsStoreModeChange();

        self.changeVisibilityForOrdersModesRelatedBlocks();
    },

    magentoOrdersListingsStoreModeChange: function()
    {
        if ($('magento_orders_listings_store_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM')) {
            $('magento_orders_listings_store_id_container').show();
        } else {
            $('magento_orders_listings_store_id_container').hide();
        }

        $('magento_orders_listings_store_id').value = '';
    },

    magentoOrdersListingsOtherModeChange: function()
    {
        var self = AmazonAccountHandlerObj;

        if ($('magento_orders_listings_other_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_LISTINGS_OTHER_MODE_YES')) {
            $('magento_orders_listings_other_product_mode_container').show();
            $('magento_orders_listings_other_store_id_container').show();
        } else {
            $('magento_orders_listings_other_product_mode_container').hide();
            $('magento_orders_listings_other_store_id_container').hide();
        }

        $('magento_orders_listings_other_product_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE');
        $('magento_orders_listings_other_store_id').value = '';

        self.magentoOrdersListingsOtherProductModeChange();
        self.changeVisibilityForOrdersModesRelatedBlocks();
    },

    magentoOrdersListingsOtherProductModeChange: function()
    {
        if ($('magento_orders_listings_other_product_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE')) {
            $('magento_orders_listings_other_product_mode_note').hide();
            $('magento_orders_listings_other_product_tax_class_id_container').hide();
        } else {
            $('magento_orders_listings_other_product_mode_note').show();
            $('magento_orders_listings_other_product_tax_class_id_container').show();
        }
    },

    magentoOrdersNumberSourceChange: function()
    {
        var self = AmazonAccountHandlerObj;
        self.renderOrderNumberExample();
    },

    magentoOrdersNumberPrefixModeChange: function()
    {
        var self = AmazonAccountHandlerObj;

        if ($('magento_orders_number_prefix_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_NUMBER_PREFIX_MODE_YES')) {
            $('magento_orders_number_prefix_container').show();
        } else {
            $('magento_orders_number_prefix_container').hide();
            $('magento_orders_number_prefix_prefix').value = '';
        }

        self.renderOrderNumberExample();
    },

    magentoOrdersNumberPrefixPrefixChange: function()
    {
        var self = AmazonAccountHandlerObj;
        self.renderOrderNumberExample();
    },

    renderOrderNumberExample: function()
    {
        var orderNumber = $('sample_magento_order_id').value;
        if ($('magento_orders_number_source').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_NUMBER_SOURCE_CHANNEL')) {
            orderNumber = $('sample_amazon_order_id').value;
        }

        if ($('magento_orders_number_prefix_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_NUMBER_PREFIX_MODE_YES')) {
            orderNumber = $('magento_orders_number_prefix_prefix').value + orderNumber;
        }

        $('order_number_example_container').update(orderNumber);
    },

    magentoOrdersFbaModeChange: function()
    {
        if ($('magento_orders_fba_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_FBA_MODE_NO')) {
            $('magento_orders_fba_stock_mode_container').hide();
            $('magento_orders_fba_stock_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_FBA_STOCK_MODE_NO');
        } else {
            $('magento_orders_fba_stock_mode_container').show();
        }
    },

    magentoOrdersCustomerModeChange: function()
    {
        var customerMode = $('magento_orders_customer_mode').value;

        if (customerMode == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED')) {
            $('magento_orders_customer_id_container').show();
            $('magento_orders_customer_id').addClassName('M2ePro-account-product-id');
        } else {  // M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::ORDERS_CUSTOMER_MODE_GUEST') || M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::ORDERS_CUSTOMER_MODE_NEW')
            $('magento_orders_customer_id_container').hide();
            $('magento_orders_customer_id').removeClassName('M2ePro-account-product-id');
        }

        var action = (customerMode == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_CUSTOMER_MODE_NEW')) ? 'show' : 'hide';
        $('magento_orders_customer_new_website_id_container')[action]();
        $('magento_orders_customer_new_group_id_container')[action]();
        $('magento_orders_customer_new_notifications_container')[action]();

        $('magento_orders_customer_id').value = '';
        $('magento_orders_customer_new_website_id').value = '';
        $('magento_orders_customer_new_group_id').value = '';
        $('magento_orders_customer_new_notifications').value = '';
//        $('magento_orders_customer_new_newsletter_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_CUSTOMER_NEW_SUBSCRIPTION_MODE_NO');
    },

    magentoOrdersStatusMappingModeChange: function()
    {
        // Reset dropdown selected values to default
        $('magento_orders_status_mapping_processing').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING');
        $('magento_orders_status_mapping_shipped').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED');

        // Default auto create invoice & shipment
        $('magento_orders_invoice_mode').checked = true;
        $('magento_orders_shipment_mode').checked = true;

        var disabled = $('magento_orders_status_mapping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT');
        $('magento_orders_status_mapping_processing').disabled = disabled;
        $('magento_orders_status_mapping_shipped').disabled = disabled;
        $('magento_orders_invoice_mode').disabled = disabled;
        $('magento_orders_shipment_mode').disabled = disabled;
    },

    changeVisibilityForOrdersModesRelatedBlocks: function()
    {
        var self = AmazonAccountHandlerObj;

        if ($('magento_orders_listings_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_LISTINGS_MODE_NO') &&
            $('magento_orders_listings_other_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_LISTINGS_OTHER_MODE_NO')) {

            $('magento_block_amazon_accounts_magento_orders_number').hide();
            $('magento_orders_number_source').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO');
            $('magento_orders_number_prefix_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_NUMBER_PREFIX_MODE_NO');
            self.magentoOrdersNumberPrefixModeChange();

            $('magento_block_amazon_accounts_magento_orders_fba').hide();
            $('magento_orders_fba_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_FBA_MODE_YES');
            $('magento_orders_fba_stock_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_FBA_STOCK_MODE_YES');

            $('magento_block_amazon_accounts_magento_orders_refund_and_cancellation').hide();
            $('magento_orders_refund').value = 1;

            $('magento_block_amazon_accounts_magento_orders_customer').hide();
            $('magento_orders_customer_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST');
            self.magentoOrdersCustomerModeChange();

            $('magento_block_amazon_accounts_magento_orders_status_mapping').hide();
            $('magento_orders_status_mapping_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT');
            self.magentoOrdersStatusMappingModeChange();

            $('magento_block_amazon_accounts_magento_orders_rules').hide();
            $('magento_orders_qty_reservation_days').value = 1;

            $('magento_block_amazon_accounts_magento_orders_tax').hide();
            $('magento_orders_tax_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_TAX_MODE_MIXED');

            $('magento_orders_customer_billing_address_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_BILLING_ADDRESS_MODE_SHIPPING_IF_SAME_CUSTOMER_AND_RECIPIENT');
        } else {
            $('magento_block_amazon_accounts_magento_orders_number').show();
            $('magento_block_amazon_accounts_magento_orders_fba').show();
            $('magento_block_amazon_accounts_magento_orders_refund_and_cancellation').show();
            $('magento_block_amazon_accounts_magento_orders_customer').show();
            $('magento_block_amazon_accounts_magento_orders_status_mapping').show();
            $('magento_block_amazon_accounts_magento_orders_tax').show();
            $('magento_block_amazon_accounts_magento_orders_rules').show();
        }
    },

    // Repricing Integration
    // ---------------------------------------

    linkOrRegisterRepricing: function()
    {
        return setLocation(M2ePro.url.get('adminhtml_common_amazon_account_repricing/linkOrRegister'));
    },

    unlinkRepricing: function()
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        AmazonAccountHandlerObj.openUnlinkPage();
    },

    openUnlinkPage: function()
    {
        return setLocation(M2ePro.url.get('adminhtml_common_amazon_account_repricing/openUnlinkPage'));
    },

    openManagement: function()
    {
        window.open(M2ePro.url.get('adminhtml_common_amazon_account_repricing/openManagement'));
    },

    regular_price_mode_change: function()
    {
        var self = AmazonAccountHandlerObj,
            regularPriceAttr = $('regular_price_attribute'),
            regularPriceCoeficient = $('regular_price_coefficient_td'),
            variationRegularPrice = $('regular_price_variation_mode_tr');

        regularPriceAttr.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, regularPriceAttr);
        }

        regularPriceCoeficient.hide();
        variationRegularPrice.hide();

        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_MANUAL') &&
            this.value != M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::REGULAR_PRICE_MODE_PRODUCT_POLICY')) {

            regularPriceCoeficient.show();
            variationRegularPrice.show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_MANUAL')) {
            $$('.repricing-min-price-mode-regular-depended').each(function (element) {
                if (element.selected) {
                    element.up().selectedIndex = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_MANUAL');
                    element.simulate('change');
                }

                element.hide();
            });

            $$('.repricing-max-price-mode-regular-depended').each(function (element) {
                if (element.selected) {
                    element.up().selectedIndex = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_MANUAL');
                    element.simulate('change');
                }

                element.hide();
            });
        } else {
            $$('.repricing-min-price-mode-regular-depended').each(function (element) {
                element.show();
            });

            $$('.repricing-max-price-mode-regular-depended').each(function (element) {
                element.show();
            });
        }
    },

    min_price_mode_change: function()
    {
        var self = AmazonAccountHandlerObj,
            minPriceValueTr = $('min_price_value_tr'),
            minPricePercentTr = $('min_price_percent_tr'),
            minPriceWarning = $('min_price_warning_tr'),
            minPriceAttr = $('min_price_attribute'),
            minPriceCoeficient = $('min_price_coefficient_td'),
            variationMinPrice = $('min_price_variation_mode_tr');

        minPriceWarning.hide();
        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_MANUAL')) {
            minPriceWarning.show();
        }

        minPriceCoeficient.hide();
        variationMinPrice.hide();

        minPriceAttr.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, minPriceAttr);

            minPriceCoeficient.show();
            variationMinPrice.show();
        }

        minPriceValueTr.hide();
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::MIN_PRICE_MODE_REGULAR_VALUE')) {
            minPriceValueTr.show();
        }

        minPricePercentTr.hide();
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::MIN_PRICE_MODE_REGULAR_PERCENT')) {
            minPricePercentTr.show();
        }
    },

    max_price_mode_change: function()
    {
        var self = AmazonAccountHandlerObj,
            maxPriceValueTr = $('max_price_value_tr'),
            maxPricePercentTr = $('max_price_percent_tr'),
            maxPriceWarning = $('max_price_warning_tr'),
            maxPriceAttr = $('max_price_attribute'),
            maxPriceCoeficient = $('max_price_coefficient_td'),
            variationMaxPrice = $('max_price_variation_mode_tr');

        maxPriceWarning.hide();
        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_MANUAL')) {
            maxPriceWarning.show();
        }

        maxPriceCoeficient.hide();
        variationMaxPrice.hide();

        maxPriceAttr.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, maxPriceAttr);

            maxPriceCoeficient.show();
            variationMaxPrice.show();
        }

        maxPriceValueTr.hide();
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::MAX_PRICE_MODE_REGULAR_VALUE')) {
            maxPriceValueTr.show();
        }

        maxPricePercentTr.hide();
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::MAX_PRICE_MODE_REGULAR_PERCENT')) {
            maxPricePercentTr.show();
        }
    },

    disable_mode_change: function()
    {
        var self = AmazonAccountHandlerObj,
            disableModeAttr = $('disable_mode_attribute');

        disableModeAttr.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::DISABLE_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, disableModeAttr);
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
    }

    // ---------------------------------------
});