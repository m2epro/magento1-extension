window.AmazonAccount = Class.create(Common, {

    // ---------------------------------------

    initialize: function() {
        this.accountHandler = new Account();

        this.setValidationCheckRepetitionValue('M2ePro-account-title',
            M2ePro.translator.translate('The specified Title is already used for other Account. Account Title must be unique.'),
            'Account', 'title', 'id',
            M2ePro.formData.id,
            M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon::NICK'));

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
                    id: M2ePro.formData.id
                },
                onSuccess: function(transport) {
                    checkResult = transport.responseText.evalJSON()['ok'];
                }
            });

            return checkResult;
        });

        Validation.add('M2ePro-require-select-attribute', M2ePro.translator.translate('If Yes is chosen, you must select at least one Attribute for Product Linking.'), function(value, el) {

            if ($('other_listings_mapping_mode').value == 0) {
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

        Validation.add('M2ePro-is-ready-for-document-generation', M2ePro.translator.translate('is_ready_for_document_generation'), function(value) {
            var checkResult = false;

            if ($('auto_invoicing').value != M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::AUTO_INVOICING_VAT_CALCULATION_SERVICE')) {
                return true;
            }

            if ($('invoice_generation').value != M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::INVOICE_GENERATION_BY_EXTENSION')) {
                return true;
            }

            new Ajax.Request(M2ePro.url.get('adminhtml_amazon_account/isReadyForDocumentGeneration'), {
                method: 'post',
                asynchronous: false,
                parameters: {
                    account_id: M2ePro.formData.id,
                    new_store_mode: $('magento_orders_listings_store_mode').value,
                    new_store_id: $('magento_orders_listings_store_id').value
                },
                onSuccess: function(transport) {
                    checkResult = transport.responseText.evalJSON()['result'];
                }
            });

            return checkResult;
        });
    },

    // ---------------------------------------

    completeStep: function() {
        window.opener.completeStep = 1;
        window.close();
    },

    // ---------------------------------------

    delete_click: function(accountId) {
        this.accountHandler.on_delete_popup(accountId);
    },

    check_click: function() {
        this.submitForm(M2ePro.url.get(
            'adminhtml_amazon_account/check'
        ));

        return false;
    },

    // ---------------------------------------

    get_token: function(marketplaceId) {
        var title = $('title');

        title.removeClassName('required-entry M2ePro-account-title');
        $('other_listings_mapping_mode').removeClassName('M2ePro-require-select-attribute');

        this.submitForm(M2ePro.url.get(
            'adminhtml_amazon_account/beforeGetToken',
            {
                'id': M2ePro.formData.id,
                'title': title.value,
                'marketplace_id': marketplaceId
            }
        ));
    },

    // ---------------------------------------

    other_listings_synchronization_change: function() {
        if (this.value == 1) {
            $('other_listings_mapping_mode_tr').show();
            $('other_listings_store_view_tr').show();
        } else {
            $('other_listings_mapping_mode').value = 0;
            $('other_listings_mapping_mode').simulate('change');
            $('other_listings_mapping_mode_tr').hide();
            $('other_listings_store_view_tr').hide();
        }
    },

    other_listings_mapping_mode_change: function() {
        if (this.value == 1) {
            $('magento_block_amazon_accounts_other_listings_product_mapping').show();
        } else {
            $('magento_block_amazon_accounts_other_listings_product_mapping').hide();

            $('mapping_general_id_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_NONE');
            $('mapping_sku_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE');
            $('mapping_title_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE');
        }

        $('mapping_general_id_mode').simulate('change');
        $('mapping_sku_mode').simulate('change');
        $('mapping_title_mode').simulate('change');
    },

    // ---------------------------------------

    mapping_general_id_mode_change: function() {
        var self = AmazonAccountObj;

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

    mapping_sku_mode_change: function() {
        var self = AmazonAccountObj;

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

    mapping_title_mode_change: function() {
        var self = AmazonAccountObj;

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

    magentoOrdersListingsModeChange: function() {
        var self = AmazonAccountObj;

        if ($('magento_orders_listings_mode').value == 1) {
            $('magento_orders_listings_store_mode_container').show();
        } else {
            $('magento_orders_listings_store_mode_container').hide();
            $('magento_orders_listings_store_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT');
        }

        self.magentoOrdersListingsStoreModeChange();

        self.changeVisibilityForOrdersModesRelatedBlocks();
    },

    magentoOrdersListingsStoreModeChange: function() {
        if ($('magento_orders_listings_store_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM')) {
            $('magento_orders_listings_store_id_container').show();
        } else {
            $('magento_orders_listings_store_id_container').hide();
            $('magento_orders_listings_store_id').value = '';
        }
    },

    magentoOrdersListingsOtherModeChange: function() {
        var self = AmazonAccountObj;

        if ($('magento_orders_listings_other_mode').value == 1) {
            $('magento_orders_listings_other_product_mode_container').show();
            $('magento_orders_listings_other_store_id_container').show();
        } else {
            $('magento_orders_listings_other_product_mode_container').hide();
            $('magento_orders_listings_other_store_id_container').hide();
            $('magento_orders_listings_other_product_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE');
            $('magento_orders_listings_other_store_id').value = '';
        }

        self.magentoOrdersListingsOtherProductModeChange();
        self.changeVisibilityForOrdersModesRelatedBlocks();
    },

    magentoOrdersListingsOtherProductModeChange: function() {
        if ($('magento_orders_listings_other_product_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE')) {
            $('magento_orders_listings_other_product_mode_note').hide();
            $('magento_orders_listings_other_product_tax_class_id_container').hide();
            $('magento_orders_listings_other_product_mode_warning').hide();
        } else {
            $('magento_orders_listings_other_product_mode_note').show();
            $('magento_orders_listings_other_product_tax_class_id_container').show();
            $('magento_orders_listings_other_product_mode_warning').show();
        }
    },

    magentoOrdersNumberSourceChange: function() {
        var self = AmazonAccountObj;
        self.renderOrderNumberExample();
    },

    magentoOrdersNumberPrefixPrefixChange: function() {
        var self = AmazonAccountObj;
        self.renderOrderNumberExample();
    },

    renderOrderNumberExample: function() {
        var orderNumber = $('sample_magento_order_id').value;
        if ($('magento_orders_number_source').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_NUMBER_SOURCE_CHANNEL')) {
            orderNumber = $('sample_amazon_order_id').value;
        }

        var regular = orderNumber,
            afn = orderNumber,
            prime = orderNumber,
            b2b = orderNumber;

        var regularPrefix = $('magento_orders_number_prefix_prefix').value;
        regular = regularPrefix + regular;
        afn = regularPrefix + $('magento_orders_number_prefix_afn').value + afn;
        prime = regularPrefix + $('magento_orders_number_prefix_prime').value + prime;
        b2b = regularPrefix + $('magento_orders_number_prefix_b2b').value + b2b;

        $('order_number_example_container_regular').update(regular);
        $('order_number_example_container_afn').update(afn);
        $('order_number_example_container_prime').update(prime);
        $('order_number_example_container_b2b').update(b2b);
    },

    magentoOrdersFbaModeChange: function() {
        var self = AmazonAccountObj;

        if ($('magento_orders_fba_mode').value == 0) {
            $('magento_orders_fba_store_mode').value = 0;
            $('magento_orders_fba_store_mode_container').hide();
            $('magento_orders_fba_stock_mode').value = 0;
            $('magento_orders_fba_stock_mode_container').hide();
        } else {
            $('magento_orders_fba_store_mode_container').show();
            $('magento_orders_fba_stock_mode_container').show();
        }

        self.magentoOrdersFbaStoreModeChange();
    },

    magentoOrdersFbaStoreModeChange: function() {
        if ($('magento_orders_fba_store_mode').value == 0) {
            $('magento_orders_fba_store_id').value = '';
            $('magento_orders_fba_store_id_container').hide();
        } else {
            $('magento_orders_fba_store_id_container').show();
        }
    },

    magentoOrdersCustomerModeChange: function() {
        var customerMode = $('magento_orders_customer_mode').value;

        if (customerMode == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED')) {
            $('magento_orders_customer_id_container').show();
            $('magento_orders_customer_id').addClassName('M2ePro-account-product-id');
        } else {
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
    },

    openExcludedStatesPopup: function() {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_amazon_account/getExcludedStatesPopupHtml'), {
            method: 'post',
            parameters: {
                selected_states: $('magento_orders_tax_excluded_states').value
            },
            onSuccess: function(transport) {

                var popup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Select states where tax will be excluded'),
                    width: 600,
                    height: 600,
                    zIndex: 100,
                    border: false,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });

                popup.options.destroyOnClose = true;

                $('modal_dialog_message').update(transport.responseText);
                self.autoHeightFix();
            }
        });
    },

    confirmExcludedStates: function() {
        var excludedStates = [];

        $$('.excluded_state_checkbox').each(function(element) {
            if (element.checked) {
                excludedStates.push(element.value);
            }
        });

        $('magento_orders_tax_excluded_states').value = excludedStates.toString();

        Windows.getFocusedWindow().close();
    },

    openExcludedCountriesPopup: function() {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_amazon_account/getExcludedCountriesPopupHtml'), {
            method: 'post',
            parameters: {
                selected_countries: $('magento_orders_tax_excluded_countries').value
            },
            onSuccess: function(transport) {
                var popup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Select countries where VAT will be excluded'),
                    width: 600,
                    height: 600,
                    zIndex: 100,
                    border: false,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });

                popup.options.destroyOnClose = true;

                $('modal_dialog_message').update(transport.responseText);
                self.autoHeightFix();
            }
        });
    },

    confirmExcludedCountries: function() {
        var excludedCountries = [];

        $$('.excluded_country_checkbox').each(function(element) {
            if (element.checked) {
                excludedCountries.push(element.value);
            }
        });

        $('magento_orders_tax_excluded_countries').value = excludedCountries.toString();

        Windows.getFocusedWindow().close();
    },

    magentoOrdersTaxModeChange: function() {
        if ($('magento_orders_tax_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_TAX_MODE_CHANNEL') ||
            $('magento_orders_tax_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_TAX_MODE_MIXED')) {
            $('tr_magento_orders_tax_excluded_states').show();
            $('tr_magento_orders_tax_collect_for_uk').show();
        } else {
            $('tr_magento_orders_tax_excluded_states').hide();
            $('tr_magento_orders_tax_collect_for_uk').hide();
        }

        if ($('marketplace_id').value != M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_US')) {
            $('tr_magento_orders_tax_excluded_states').hide();
        }

    },

    magentoOrdersTaxAmazonCollectsChange: function() {
        if ($('magento_orders_tax_amazon_collects').value == 1) {
            $('show_excluded_states_button').show();
        } else {
            $('show_excluded_states_button').hide();
        }
    },

    magentoOrdersTaxSkipTaxInEEAOrders: function() {
        if ($('magento_orders_tax_amazon_collects_for_eea_shipment').value == 1) {
            $('show_excluded_countries_button').show();
        } else {
            $('show_excluded_countries_button').hide();
        }
    },

    magentoOrdersStatusMappingModeChange: function() {
        // Reset dropdown selected values to default
        $('magento_orders_status_mapping_processing').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING');
        $('magento_orders_status_mapping_shipped').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED');

        var disabled = $('magento_orders_status_mapping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT');
        $('magento_orders_status_mapping_processing').disabled = disabled;
        $('magento_orders_status_mapping_shipped').disabled = disabled;
    },

    changeVisibilityForOrdersModesRelatedBlocks: function() {
        var self = AmazonAccountObj;

        if ($('magento_orders_listings_mode').value == 0 && $('magento_orders_listings_other_mode').value == 0) {

            $('magento_block_amazon_accounts_magento_orders_number').hide();
            $('magento_orders_number_source').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO');
            $('magento_orders_number_apply_to_amazon').value = 0;

            $('magento_block_amazon_accounts_magento_orders_fba').hide();
            $('magento_orders_fba_mode').value = 1;
            $('magento_orders_fba_store_mode').value = 0;
            $('magento_orders_fba_stock_mode').value = 1;

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

            $('magento_orders_customer_billing_address_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT');
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

    autoInvoicingModeChange: function() {
        var invoiceGenerationTR = $('invoice_generation').up('tr');
        var createMagentoInvoice = $('create_magento_invoice');

        invoiceGenerationTR.hide();

        if ($('auto_invoicing').value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account::AUTO_INVOICING_VAT_CALCULATION_SERVICE')) {
            invoiceGenerationTR.show();
            createMagentoInvoice.value = 0;
        }
    },

    // Repricing Integration
    // ---------------------------------------

    linkOrRegisterRepricing: function() {
        return setLocation(M2ePro.url.get('adminhtml_amazon_account_repricing/linkOrRegister'));
    },

    unlinkRepricing: function() {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        AmazonAccountObj.openUnlinkPage();
    },

    openUnlinkPage: function() {
        return setLocation(M2ePro.url.get('adminhtml_amazon_account_repricing/openUnlinkPage'));
    },

    openManagement: function() {
        window.open(M2ePro.url.get('adminhtml_amazon_account_repricing/openManagement'));
    },

    regular_price_mode_change: function() {
        var self = AmazonAccountObj,
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
            $$('.repricing-min-price-mode-regular-depended').each(function(element) {
                if (element.selected) {
                    element.up().selectedIndex = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_MANUAL');
                    element.simulate('change');
                }

                element.hide();
            });

            $$('.repricing-max-price-mode-regular-depended').each(function(element) {
                if (element.selected) {
                    element.up().selectedIndex = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_MANUAL');
                    element.simulate('change');
                }

                element.hide();
            });
        } else {
            $$('.repricing-min-price-mode-regular-depended').each(function(element) {
                element.show();
            });

            $$('.repricing-max-price-mode-regular-depended').each(function(element) {
                element.show();
            });
        }
    },

    min_price_mode_change: function() {
        var self = AmazonAccountObj,
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

    max_price_mode_change: function() {
        var self = AmazonAccountObj,
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

    disable_mode_change: function() {
        var self = AmazonAccountObj,
            disableModeAttr = $('disable_mode_attribute');

        disableModeAttr.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Account_Repricing::DISABLE_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, disableModeAttr);
        }
    },

    // ---------------------------------------

    saveAndClose: function() {
        var url = typeof M2ePro.url.urls.formSubmit == 'undefined' ?
            M2ePro.url.formSubmit + 'back/' + base64_encode('list') + '/' :
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

    repricing_refresh: function() {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_amazon_account_repricing/refresh'), {
            method: 'post',
            onSuccess: function(data) {

                var response = JSON.parse(data.responseText);

                MessageObj.clearAll();
                if (response.success) {
                    MessageObj.addSuccess(response.success);

                    $('repricing_total_products').innerHTML = response.repricing_total_products;
                    $('m2epro_repricing_total_products').innerHTML = response.m2epro_repricing_total_products;
                } else {
                    MessageObj.addError(response.error);
                }
            }
        });
    }

    // ---------------------------------------
});
