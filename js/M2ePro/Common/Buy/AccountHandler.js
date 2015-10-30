CommonBuyAccountHandler = Class.create();
CommonBuyAccountHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-account-title',
                                                M2ePro.translator.translate('The specified Title is already used for other Account. Account Title must be unique.'),
                                                'Account', 'title', 'id',
                                                M2ePro.formData.id,
                                                M2ePro.php.constant('Ess_M2ePro_Helper_Component_Buy::NICK'));

        Validation.add('M2ePro-require-select-attribute', M2ePro.translator.translate('If Yes is chosen, you must select at least one Attribute for Product Mapping.'), function(value, el) {

            if ($('other_listings_mapping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_MAPPING_MODE_NO')) {
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

        Validation.add('M2ePro-web-access', M2ePro.translator.translate('M2E Pro was not able to get access to the Rakuten.com Account. Please, make sure, that you enter correct Rakuten.com Seller Tools login and password.'), function(value, el) {

            var checkResult = false;
            var login = $('web_login').value;
            var password = $('web_password').value;

            if (password == '') {
                return true;
            }

            new Ajax.Request(M2ePro.url.get('adminhtml_common_buy_account/checkAuth'), {
                method: 'post',
                asynchronous: false,
                parameters: {
                    login: login,
                    password: password,
                    mode: 'web'
                },
                onSuccess: function(transport) {
                    checkResult = transport.responseText.evalJSON()['result'];
                }
            });

            return checkResult;
        });

        Validation.add('M2ePro-ftp-access', M2ePro.translator.translate('M2E Pro was not able to get access to the Rakuten.com Account. Please, make sure, that you enter correct Rakuten.com FTP login and password.'), function(value, el) {

            var checkResult = false;
            var login = $('ftp_login').value;
            var password = $('ftp_password').value;

            if (password == '') {
                return true;
            }

            new Ajax.Request(M2ePro.url.get('adminhtml_common_buy_account/checkAuth'), {
                method: 'post',
                asynchronous: false,
                parameters: {
                    login: login,
                    password: password,
                    mode: 'ftp'
                },
                onSuccess: function(transport) {
                    checkResult = transport.responseText.evalJSON()['result'];
                }
            });

            return checkResult;
        });

        Validation.add('M2ePro-marketplace-disabled', M2ePro.translator.translate('You must enable Marketplace first.'), function(value, el) {
            return false;
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
                    customer_id : value,
                    id          : M2ePro.formData.id
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

    update_password: function(mode)
    {
         $(mode + '_password_button').hide();
         $(mode + '_password_input').show();
         $(mode + '_password_required').show();
    },

    // ---------------------------------------

    other_listings_synchronization_change: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES')) {
            $('other_listings_mapping_mode_tr').show();
            $('related_store_id_container').show();
        } else {
            $('other_listings_mapping_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_MAPPING_MODE_NO');
            $('other_listings_mapping_mode').simulate('change');
            $('other_listings_mapping_mode_tr').hide();
            $('related_store_id_container').hide();
        }
    },

    other_listings_mapping_mode_change: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_MAPPING_MODE_YES')) {
            $('magento_block_buy_accounts_other_listings_product_mapping').show();
            $('magento_block_buy_accounts_other_listings_move_mode').show();
        } else {
            $('magento_block_buy_accounts_other_listings_product_mapping').hide();
            $('magento_block_buy_accounts_other_listings_move_mode').hide();

            $('other_listings_move_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_MOVE_TO_LISTINGS_DISABLED');
            $('mapping_general_id_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_NONE');
            $('mapping_sku_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE');
        }

        $('mapping_general_id_mode').simulate('change');
        $('mapping_sku_mode').simulate('change');

        $('other_listings_move_mode').simulate('change');
    },

    // ---------------------------------------

    mapping_general_id_mode_change: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_NONE')) {
            $('mapping_general_id_priority_td').hide();
        } else {
            $('mapping_general_id_priority_td').show();

            $('mapping_general_id_attribute').value = '';
            BuyAccountHandlerObj.updateHiddenValue(this, $('mapping_general_id_attribute'));
        }
    },

    mapping_sku_mode_change: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE')) {
            $('mapping_sku_priority_td').hide();
        } else {
            $('mapping_sku_priority_td').show();

            $('mapping_sku_attribute').value = '';
            if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE')) {
                BuyAccountHandlerObj.updateHiddenValue(this, $('mapping_sku_attribute'));
            }
        }
    },

    // ---------------------------------------

    move_mode_change: function()
    {
        if ($('other_listings_move_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_MOVE_TO_LISTINGS_ENABLED')) {
            $('other_listings_move_synch_tr').show();
        } else {
            $('other_listings_move_synch_tr').hide();
        }
    },

    // ---------------------------------------

    magentoOrdersListingsModeChange: function()
    {
        var self = BuyAccountHandlerObj;

        if ($('magento_orders_listings_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_LISTINGS_MODE_YES')) {
            $('magento_orders_listings_store_mode_container').show();
        } else {
            $('magento_orders_listings_store_mode_container').hide();
        }

        $('magento_orders_listings_store_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT');
        self.magentoOrdersListingsStoreModeChange();

        self.changeVisibilityForOrdersModesRelatedBlocks();
    },

    magentoOrdersListingsStoreModeChange: function()
    {
        if ($('magento_orders_listings_store_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM')) {
            $('magento_orders_listings_store_id_container').show();
        } else {
            $('magento_orders_listings_store_id_container').hide();
        }

        $('magento_orders_listings_store_id').value = '';
    },

    magentoOrdersListingsOtherModeChange: function()
    {
        var self = BuyAccountHandlerObj;

        if ($('magento_orders_listings_other_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_LISTINGS_OTHER_MODE_YES')) {
            $('magento_orders_listings_other_product_mode_container').show();
            $('magento_orders_listings_other_store_id_container').show();
        } else {
            $('magento_orders_listings_other_product_mode_container').hide();
            $('magento_orders_listings_other_store_id_container').hide();
        }

        $('magento_orders_listings_other_product_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE');
        $('magento_orders_listings_other_store_id').value = '';

        self.magentoOrdersListingsOtherProductModeChange();
        self.changeVisibilityForOrdersModesRelatedBlocks();
    },

    magentoOrdersListingsOtherProductModeChange: function()
    {
        var self = BuyAccountHandlerObj;

        if ($('magento_orders_listings_other_product_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE')) {
            $('magento_orders_listings_other_product_mode_note').hide();
            $('magento_orders_listings_other_product_tax_class_id_container').hide();
        } else {
            $('magento_orders_listings_other_product_mode_note').show();
            $('magento_orders_listings_other_product_tax_class_id_container').show();
        }
    },

    magentoOrdersNumberSourceChange: function()
    {
        var self = BuyAccountHandlerObj;
        self.renderOrderNumberExample();
    },

    magentoOrdersNumberPrefixModeChange: function()
    {
        var self = BuyAccountHandlerObj;

        if ($('magento_orders_number_prefix_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_NUMBER_PREFIX_MODE_YES')) {
            $('magento_orders_number_prefix_container').show();
        } else {
            $('magento_orders_number_prefix_container').hide();
            $('magento_orders_number_prefix_prefix').value = '';
        }

        self.renderOrderNumberExample();
    },

    magentoOrdersNumberPrefixPrefixChange: function()
    {
        var self = BuyAccountHandlerObj;
        self.renderOrderNumberExample();
    },

    renderOrderNumberExample: function()
    {
        var orderNumber = $('sample_magento_order_id').value;
        if ($('magento_orders_number_source').value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_NUMBER_SOURCE_CHANNEL')) {
            orderNumber = $('sample_buy_order_id').value;
        }

        if ($('magento_orders_number_prefix_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_NUMBER_PREFIX_MODE_YES')) {
            orderNumber = $('magento_orders_number_prefix_prefix').value + orderNumber;
        }

        $('order_number_example_container').update(orderNumber);
    },

    magentoOrdersCustomerModeChange: function()
    {
        var self = BuyAccountHandlerObj,
            customerMode = $('magento_orders_customer_mode').value;

        if (customerMode == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED')) {
            $('magento_orders_customer_id_container').show();
            $('magento_orders_customer_id').addClassName('M2ePro-account-product-id');
        } else {  // M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::ORDERS_CUSTOMER_MODE_GUEST') || M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::ORDERS_CUSTOMER_MODE_NEW')
            $('magento_orders_customer_id_container').hide();
            $('magento_orders_customer_id').removeClassName('M2ePro-account-product-id');
        }

        var action = (customerMode == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_CUSTOMER_MODE_NEW')) ? 'show' : 'hide';
        $('magento_orders_customer_new_website_id_container')[action]();
        $('magento_orders_customer_new_group_id_container')[action]();
        $('magento_orders_customer_new_notifications_container')[action]();

        $('magento_orders_customer_id').value = '';
        $('magento_orders_customer_new_website_id').value = '';
        $('magento_orders_customer_new_group_id').value = '';
        $('magento_orders_customer_new_notifications').value = '';
//        $('magento_orders_customer_new_newsletter_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_CUSTOMER_NEW_SUBSCRIPTION_MODE_NO');
    },

    magentoOrdersStatusMappingModeChange: function()
    {
        var self = BuyAccountHandlerObj;

        // Reset dropdown selected values to default
        $('magento_orders_status_mapping_processing').value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING');

        // Default auto create invoice & shipment
        $('magento_orders_invoice_mode').checked = true;

        var disabled = $('magento_orders_status_mapping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT');
        $('magento_orders_status_mapping_processing').disabled = disabled;
        $('magento_orders_invoice_mode').disabled = disabled;
    },

    changeVisibilityForOrdersModesRelatedBlocks: function()
    {
        var self = BuyAccountHandlerObj;

        if ($('magento_orders_listings_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_LISTINGS_MODE_NO') &&
            $('magento_orders_listings_other_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_LISTINGS_OTHER_MODE_NO')) {

            $('magento_block_buy_accounts_magento_orders_number').hide();
            $('magento_orders_number_source').value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO');
            $('magento_orders_number_prefix_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_NUMBER_PREFIX_MODE_NO');
            self.magentoOrdersNumberPrefixModeChange();

            $('magento_block_buy_accounts_magento_orders_customer').hide();
            $('magento_orders_customer_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST');
            self.magentoOrdersCustomerModeChange();

            $('magento_block_buy_accounts_magento_orders_status_mapping').hide();
            $('magento_orders_status_mapping_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT');
            self.magentoOrdersStatusMappingModeChange();

            $('magento_block_buy_accounts_magento_orders_tax').hide();
            $('magento_orders_tax_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_TAX_MODE_MIXED');
        } else {
            $('magento_block_buy_accounts_magento_orders_number').show();
            $('magento_block_buy_accounts_magento_orders_customer').show();
            $('magento_block_buy_accounts_magento_orders_status_mapping').show();
            $('magento_block_buy_accounts_magento_orders_tax').show();
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
