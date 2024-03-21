window.EbayAccount = Class.create(Common, {

    // ---------------------------------------

    initialize: function() {
        this.accountHandler = new Account();

        this.setValidationCheckRepetitionValue('M2ePro-account-title',
            M2ePro.translator.translate('The specified Title is already used for other Account. Account Title must be unique.'),
            'Account', 'title', 'id',
            M2ePro.formData.id,
            M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay::NICK')
        );

        Validation.add('M2ePro-account-token-session', M2ePro.translator.translate('You must get token.'), function(value) {
            return value != '';
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
                    id: M2ePro.formData.id
                },
                onSuccess: function(transport) {
                    checkResult = transport.responseText.evalJSON()['ok'];
                }
            });

            return checkResult;
        });

        Validation.add('M2ePro-account-feedback-templates', M2ePro.translator.translate('You should create at least one Response Template.'), function(value) {

            if (value == 0) {
                return true;
            }

            var checkResult = false;

            new Ajax.Request(M2ePro.url.get('adminhtml_ebay_account/feedbackTemplateCheck'), {
                method: 'post',
                asynchronous: false,
                parameters: {
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

    delete_click: function(accountId) {
        this.accountHandler.on_delete_popup(accountId);
    },

    // ---------------------------------------

    feedbacksReceiveChange: function() {
        var self = EbayAccountObj;

        if ($('feedbacks_receive').value == 1) {
            $('magento_block_ebay_accounts_feedbacks_response').show();
        } else {
            $('magento_block_ebay_accounts_feedbacks_response').hide();

        }
        $('feedbacks_auto_response').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::FEEDBACKS_AUTO_RESPONSE_NONE');
        self.feedbacksAutoResponseChange();
    },

    feedbacksAutoResponseChange: function() {
        if ($('feedbacks_auto_response').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::FEEDBACKS_AUTO_RESPONSE_NONE')) {
            $('block_accounts_feedbacks_templates').hide();
            $('feedbacks_auto_response_only_positive_container').hide();
        } else {
            $('block_accounts_feedbacks_templates').show();
            $('feedbacks_auto_response_only_positive_container').show();
        }
    },

    // ---------------------------------------

    feedbacksOpenAddForm: function() {
        $('block_accounts_feedbacks_form_template_title_add').show();
        $('block_accounts_feedbacks_form_template_title_edit').hide();

        $('feedbacks_templates_id').value = '';
        $('feedbacks_templates_body').value = '';

        $('block_accounts_feedbacks_form_template_button_cancel').show();
        $('block_accounts_feedbacks_form_template_button_add').show();
        $('block_accounts_feedbacks_form_template_button_edit').hide();

        $('magento_block_ebay_accounts_feedbacks_form_template').show();
        $('feedbacks_templates_body_validate').hide();
    },

    feedbacksOpenEditForm: function(id, body) {
        $('block_accounts_feedbacks_form_template_title_add').hide();
        $('block_accounts_feedbacks_form_template_title_edit').show();

        $('feedbacks_templates_id').value = id;
        $('feedbacks_templates_body').value = body;

        $('block_accounts_feedbacks_form_template_button_cancel').show();
        $('block_accounts_feedbacks_form_template_button_add').hide();
        $('block_accounts_feedbacks_form_template_button_edit').show();

        $('magento_block_ebay_accounts_feedbacks_form_template').show();
        $('feedbacks_templates_body_validate').hide();
    },

    feedbacksCancelForm: function() {
        $('block_accounts_feedbacks_form_template_title_add').hide();
        $('block_accounts_feedbacks_form_template_title_edit').hide();

        $('feedbacks_templates_id').value = '';
        $('feedbacks_templates_body').value = '';

        $('block_accounts_feedbacks_form_template_button_cancel').hide();
        $('block_accounts_feedbacks_form_template_button_add').hide();
        $('block_accounts_feedbacks_form_template_button_edit').hide();

        $('magento_block_ebay_accounts_feedbacks_form_template').hide();
        $('feedbacks_templates_body_validate').hide();
    },

    // ---------------------------------------

    feedbacksAddAction: function() {
        var self = EbayAccountObj;

        if ($('feedbacks_templates_body').value.length < 2 || $('feedbacks_templates_body').value.length > 80) {
            $('feedbacks_templates_body_validate').show();
            return;
        } else {
            $('feedbacks_templates_body_validate').hide();
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_account/feedbackTemplateEdit'), {
            method: 'post',
            asynchronous: false,
            parameters: {
                account_id: M2ePro.formData.id,
                body: $('feedbacks_templates_body').value
            },
            onSuccess: function(transport) {
                self.feedbacksCancelForm();
                eval('ebayAccountEditTabsFeedbackGrid' + M2ePro.formData.id + 'JsObject.reload();');
            }
        });
    },

    feedbacksEditAction: function() {
        var self = EbayAccountObj;

        if ($('feedbacks_templates_body').value.length < 2 || $('feedbacks_templates_body').value.length > 80) {
            $('feedbacks_templates_body_validate').show();
            return;
        } else {
            $('feedbacks_templates_body_validate').hide();
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_account/feedbackTemplateEdit'), {
            method: 'post',
            asynchronous: false,
            parameters: {
                id: $('feedbacks_templates_id').value,
                account_id: M2ePro.formData.id,
                body: $('feedbacks_templates_body').value
            },
            onSuccess: function(transport) {
                self.feedbacksCancelForm();
                eval('ebayAccountEditTabsFeedbackGrid' + M2ePro.formData.id + 'JsObject.reload();');
            }
        });
    },

    feedbacksDeleteAction: function(id) {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return false;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_account/feedbackTemplateDelete'), {
            method: 'post',
            asynchronous: false,
            parameters: {
                id: id
            },
            onSuccess: function(transport) {
                eval('ebayAccountEditTabsFeedbackGrid' + M2ePro.formData.id + 'JsObject.reload();');
            }
        });
    },

    // ---------------------------------------

    ebayStoreSelectCategory: function(id) {
        $('ebay_store_categories_selected_container').show();
        $('ebay_store_categories_selected').value = id;
    },

    ebayStoreSelectCategoryHide: function() {
        $('ebay_store_categories_selected_container').hide();
        $('ebay_store_categories_selected').value = '';
    },

    // ---------------------------------------

    magentoOrdersListingsModeChange: function() {
        var self = EbayAccountObj;

        if ($('magento_orders_listings_mode').value == 1) {
            $('magento_orders_listings_store_mode_container').show();
        } else {
            $('magento_orders_listings_store_mode_container').hide();
        }

        $('magento_orders_listings_store_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT');
        self.magentoOrdersListingsStoreModeChange();

        self.changeVisibilityForOrdersModesRelatedBlocks();
    },

    magentoOrdersListingsStoreModeChange: function() {
        if ($('magento_orders_listings_store_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM')) {
            $('magento_orders_listings_store_id_container').show();
        } else {
            $('magento_orders_listings_store_id_container').hide();
        }

        $('magento_orders_listings_store_id').value = '';
    },

    magentoOrdersListingsOtherModeChange: function() {
        var self = EbayAccountObj;

        if ($('magento_orders_listings_other_mode').value == 1) {
            $('magento_orders_listings_other_product_mode_container').show();
            $('magento_orders_listings_other_store_id_container').show();
        } else {
            $('magento_orders_listings_other_product_mode_container').hide();
            $('magento_orders_listings_other_store_id_container').hide();
        }

        $('magento_orders_listings_other_product_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE');
        $('magento_orders_listings_other_store_id').value = '';

        self.magentoOrdersListingsOtherProductModeChange();
        self.changeVisibilityForOrdersModesRelatedBlocks();
    },

    magentoOrdersListingsOtherProductModeChange: function() {
        if ($('magento_orders_listings_other_product_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE')) {
            $('magento_orders_listings_other_product_mode_note').hide();
            $('magento_orders_listings_other_product_tax_class_id_container').hide();
            $('magento_orders_listings_other_product_mode_warning').hide();
        } else {
            $('magento_orders_listings_other_product_mode_note').show();
            $('magento_orders_listings_other_product_tax_class_id_container').show();
            $('magento_orders_listings_other_product_mode_warning').show();
        }
    },

    magentoOrdersNumberChange: function() {
        var self = EbayAccountObj;
        self.renderOrderNumberExample();
    },

    renderOrderNumberExample: function() {
        var orderNumber = $('sample_magento_order_id').value;
        if ($('magento_orders_number_source').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_NUMBER_SOURCE_CHANNEL')) {
            orderNumber = $('sample_ebay_order_id').value;
        }

        var marketplacePrefix = '';
        if ($('magento_orders_number_prefix_use_marketplace_prefix').value == 1) {
            marketplacePrefix = $('sample_marketplace_prefix').value;
        }

        orderNumber = $('magento_orders_number_prefix_prefix').value + marketplacePrefix + orderNumber;

        $('order_number_example_container').update(orderNumber);
    },

    magentoOrdersCustomerModeChange: function() {
        var customerMode = $('magento_orders_customer_mode').value;

        if (customerMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED')) {
            $('magento_orders_customer_id_container').show();
            $('magento_orders_customer_id').addClassName('M2ePro-account-product-id');
        } else {  // M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::ORDERS_CUSTOMER_MODE_GUEST') || M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::ORDERS_CUSTOMER_MODE_NEW')
            $('magento_orders_customer_id_container').hide();
            $('magento_orders_customer_id').removeClassName('M2ePro-account-product-id');
        }

        var action = (customerMode == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_CUSTOMER_MODE_NEW')) ? 'show' : 'hide';
        $('magento_orders_customer_new_website_id_container')[action]();
        $('magento_orders_customer_new_group_id_container')[action]();
        $('magento_orders_customer_new_notifications_container')[action]();

        $('magento_orders_customer_id').value = '';
        $('magento_orders_customer_new_website_id').value = '';
        $('magento_orders_customer_new_group_id').value = '';
        $('magento_orders_customer_new_notifications').value = '';
//        $('magento_orders_customer_new_newsletter_mode').value = 0;
    },
    magentoOrdersStatusMappingModeChange: function() {
        // Reset dropdown selected values to default
        $('magento_orders_status_mapping_new').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_STATUS_MAPPING_NEW');
        $('magento_orders_status_mapping_paid').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_STATUS_MAPPING_PAID');
        $('magento_orders_status_mapping_shipped').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED');

        var disabled = $('magento_orders_status_mapping_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT');
        $('magento_orders_status_mapping_new').disabled = disabled;
        $('magento_orders_status_mapping_paid').disabled = disabled;
        $('magento_orders_status_mapping_shipped').disabled = disabled;
    },

    changeVisibilityForOrdersModesRelatedBlocks: function() {
        var self = EbayAccountObj;

        if ($('magento_orders_listings_mode').value == 0 && $('magento_orders_listings_other_mode').value == 0) {

            $('magento_block_ebay_accounts_magento_orders_number').hide();
            $('magento_orders_number_source').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO');

            $('magento_block_ebay_accounts_magento_orders_customer').hide();
            $('magento_orders_customer_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST');
            self.magentoOrdersCustomerModeChange();

            $('magento_block_ebay_accounts_magento_orders_status_mapping').hide();
            $('magento_orders_status_mapping_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT');
            self.magentoOrdersStatusMappingModeChange();

            $('magento_block_ebay_accounts_magento_orders_rules').hide();
            $('magento_orders_creation_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_CREATE_CHECKOUT_AND_PAID');
            $('magento_orders_qty_reservation_days').value = 1;

            $('magento_block_ebay_accounts_magento_orders_refund_and_cancellation').hide();
            $('magento_orders_refund').value = 1;

            $('magento_block_ebay_accounts_magento_orders_tax').hide();
            $('magento_orders_tax_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_TAX_MODE_MIXED');

            $('magento_orders_customer_billing_address_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT');

        } else {
            $('magento_block_ebay_accounts_magento_orders_number').show();
            $('magento_block_ebay_accounts_magento_orders_customer').show();
            $('magento_block_ebay_accounts_magento_orders_status_mapping').show();
            $('magento_block_ebay_accounts_magento_orders_rules').show();
            $('magento_block_ebay_accounts_magento_orders_refund_and_cancellation').show();
            $('magento_block_ebay_accounts_magento_orders_tax').show();
        }
    },

    // ---------------------------------------

    other_listings_synchronization_change: function() {
        var relatedStoreViews = $('magento_block_ebay_accounts_other_listings_related_store_views');

        if (this.value == 1) {
            $('other_listings_mapping_mode_tr').show();
            $('other_listings_mapping_mode').simulate('change');
            if (relatedStoreViews) {
                relatedStoreViews.show();
            }
        } else {
            $('other_listings_mapping_mode').value = 0;
            $('other_listings_mapping_mode').simulate('change');
            $('other_listings_mapping_mode_tr').hide();
            if (relatedStoreViews) {
                relatedStoreViews.hide();
            }
        }
    },

    other_listings_mapping_mode_change: function() {
        if (this.value == 1) {
            $('magento_block_ebay_accounts_other_listings_product_mapping').show();
        } else {
            $('magento_block_ebay_accounts_other_listings_product_mapping').hide();

            $('mapping_sku_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE');
            $('mapping_title_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE');
        }

        $('mapping_sku_mode').simulate('change');
        $('mapping_title_mode').simulate('change');
    },

    synchronization_mapped_change: function() {
        if (this.value == 0) {
            $('settings_button').hide();
        } else {
            $('settings_button').show();
        }
    },

    mapping_sku_mode_change: function() {
        var self = EbayAccountObj,
            attributeEl = $('mapping_sku_attribute');

        $('mapping_sku_priority_td').hide();
        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE')) {
            $('mapping_sku_priority_td').show();
        }

        attributeEl.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, attributeEl);
        }
    },

    mapping_title_mode_change: function() {
        var self = EbayAccountObj,
            attributeEl = $('mapping_title_attribute');

        $('mapping_title_priority_td').hide();
        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE')) {
            $('mapping_title_priority_td').show();
        }

        attributeEl.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, attributeEl);
        }
    },

    mapping_item_id_mode_change: function() {
        var self = EbayAccountObj,
            attributeEl = $('mapping_item_id_attribute');

        $('mapping_item_id_priority_td').hide();
        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_NONE')) {
            $('mapping_item_id_priority_td').show();
        }

        attributeEl.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, attributeEl);
        }
    },

    refreshStoreCategories: function()
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_accountStoreCategory/refresh'), {
            method: 'post',
            parameters: {
                account_id: M2ePro.formData.id
            },
            onSuccess: function()
            {
                EbayAccountObj.renderCategories();
            }
        });
    },

    renderCategories: function()
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_accountStoreCategory/getTree'), {
            method: 'post',
            parameters: {
                account_id: M2ePro.formData.id
            },
            onSuccess: function(transport)
            {
                var categories = JSON.parse(transport.responseText);

                if (categories.length !== 0) {
                    if (document.getElementById('ebay_store_categories_not_found')) {
                        document.getElementById('ebay_store_categories_not_found').hide();
                    }

                    if (document.getElementById('ebay_store_categories_no_subscription_message')) {
                        document.getElementById('ebay_store_categories_no_subscription_message').hide();
                    }

                    if (document.getElementById('tree-div')) {
                        document.getElementById('tree-div').innerHTML = "";
                    }

                    EbayAccountObj.ebayStoreInitExtTree(categories);
                }
            }
        });
    },

    ebayStoreInitExtTree: function(categoriesTreeArray)
    {
        var tree = new Ext.tree.TreePanel('tree-div', {
            animate: true,
            enableDD: false,
            containerScroll: true,
            rootVisible: false
        });

        tree.on('click', function(node, clicked) {
            varienElementMethods.setHasChanges(node.getUI().checkbox);
            tree.getRootNode().cascade(function(n) {
                var ui = n.getUI();
                if (node !== n && ui.checkbox !== undefined) {
                    ui.checkbox.checked = false;
                }
            });
            EbayAccountObj.ebayStoreSelectCategory(node.attributes.id);
        }, tree);

        var root = new Ext.tree.TreeNode({
            text: 'root',
            draggable: false,
            checked: 'false',
            id: '__root__',
        });

        tree.setRootNode(root);

        var buildCategoryTree = function(parent, config) {
            if (!config) {
                return null;
            }

            if (parent && config && config.length) {

                for (var i = 0; i < config.length; i++) {
                    var node = new Ext.tree.TreeNode(config[i]);
                    parent.appendChild(node);
                    if (config[i].children) {
                        buildCategoryTree(node, config[i].children);
                    }
                }
            }
        };

        buildCategoryTree(root, categoriesTreeArray);

        tree.render();
        root.expand();
    },

    // ---------------------------------------
});
