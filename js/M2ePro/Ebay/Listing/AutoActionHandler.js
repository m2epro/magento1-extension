EbayListingAutoActionHandler = Class.create(ListingAutoActionHandler, {

    // ---------------------------------------

    controller: 'adminhtml_ebay_listing_autoAction',

    // ---------------------------------------

    addingModeChange: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY')) {
            $('confirm_button').hide();
            $('continue_button').show();
            $('breadcrumb_container').show();
        } else {
            $('continue_button').hide();
            $('breadcrumb_container').hide();
            $('confirm_button').show();
        }

        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Listing::ADDING_MODE_NONE')) {
            $$('[id$="adding_add_not_visible_field"]')[0].show();
        } else {
            $$('[id$="adding_add_not_visible"]')[0].value = M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES');
            $$('[id$="adding_add_not_visible_field"]')[0].hide();
        }
    },

    // ---------------------------------------

    loadCategoryChooser: function(callback)
    {
        new Ajax.Request(M2ePro.url.get(ListingAutoActionHandlerObj.controller + '/getCategoryChooserHtml'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                auto_mode: $('auto_mode').value,
                group_id: this.internalData.id,
                // this parameter only for auto_mode=category
                magento_category_id: typeof categories_selected_items != 'undefined' ? categories_selected_items[0] : null
            },
            onSuccess: function(transport) {

                $('data_container').update(transport.responseText);

                if (typeof callback == 'function') {
                    callback();
                }
            }.bind(this)
        });
    },

    loadSpecific: function(callback)
    {
        var category = EbayListingCategoryChooserHandlerObj.getSelectedCategory(0);

        if (!category.mode) {
            return;
        }

        new Ajax.Request(M2ePro.url.get(ListingAutoActionHandlerObj.controller + '/getCategorySpecificHtml'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                auto_mode: this.internalData.auto_mode,
                category_mode: category.mode,
                category_value: category.value,
                group_id: this.internalData.id,
                // this parameter only for auto_mode=category
                magento_category_id: typeof categories_selected_items != 'undefined' ? categories_selected_items[0] : null
            },
            onSuccess: function(transport) {

                $('data_container').innerHTML = transport.responseText;
                try {
                    $('data_container').innerHTML.evalScripts();
                } catch (ignored) {

                }

                if (typeof callback == 'function') {
                    callback();
                }

            }.bind(this)
        });
    },

    // ---------------------------------------

    globalStepTwo: function()
    {
        ListingAutoActionHandlerObj.collectData();

        var callback = function() {
            $('continue_button')
                .stopObserving('click')
                .observe('click', ListingAutoActionHandlerObj.globalStepThree);

            ListingAutoActionHandlerObj.highlightBreadcrumbStep(2);
        };

        ListingAutoActionHandlerObj.loadCategoryChooser(callback);
    },

    globalStepThree: function()
    {
        if (!EbayListingCategoryChooserHandlerObj.validate()) {
            return;
        }

        ListingAutoActionHandlerObj.collectData();

        var callback = function() {
            ListingAutoActionHandlerObj.highlightBreadcrumbStep(3);

            $('confirm_button').show();
            $('continue_button').hide();
        };

        ListingAutoActionHandlerObj.loadSpecific(callback);
    },

    // ---------------------------------------

    websiteStepTwo: function()
    {
        ListingAutoActionHandlerObj.collectData();

        var callback = function() {
            $('continue_button')
                .stopObserving('click')
                .observe('click', ListingAutoActionHandlerObj.websiteStepThree);

            ListingAutoActionHandlerObj.highlightBreadcrumbStep(2);
        };

        ListingAutoActionHandlerObj.loadCategoryChooser(callback);
    },

    websiteStepThree: function()
    {
        if (!EbayListingCategoryChooserHandlerObj.validate()) {
            return;
        }

        ListingAutoActionHandlerObj.collectData();

        var callback = function() {
            ListingAutoActionHandlerObj.highlightBreadcrumbStep(3);

            $('confirm_button').show();
            $('continue_button').hide();
        };

        ListingAutoActionHandlerObj.loadSpecific(callback);
    },

    // ---------------------------------------

    categoryStepOne: function(groupId)
    {
        var callback = function() {
            $('add_button').hide();
            $('reset_button').hide();
            $('close_button').hide();
            $('cancel_button').show();
        };

        this.loadAutoCategoryForm(groupId, callback);
    },

    categoryStepTwo: function()
    {
        if (!ListingAutoActionHandlerObj.validate()) {
            return;
        }

        ListingAutoActionHandlerObj.collectData();

        var callback = function() {
            $('continue_button')
                .stopObserving('click')
                .observe('click', ListingAutoActionHandlerObj.categoryStepThree);

            ListingAutoActionHandlerObj.highlightBreadcrumbStep(2);
        };

        ListingAutoActionHandlerObj.loadCategoryChooser(callback);
    },

    categoryStepThree: function()
    {
        if (!EbayListingCategoryChooserHandlerObj.validate()) {
            return;
        }

        ListingAutoActionHandlerObj.collectData();

        var callback = function() {
            ListingAutoActionHandlerObj.highlightBreadcrumbStep(3);

            $('confirm_button').show();
            $('continue_button').hide();
        };

        ListingAutoActionHandlerObj.loadSpecific(callback);
    },

    // ---------------------------------------

    categoryDeleteGroup: function(groupId)
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get(ListingAutoActionHandlerObj.controller + '/deleteCategoryGroup'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                group_id: groupId
            },
            onSuccess: function(transport) {
                listingAutoActionModeCategoryGroupGridJsObject.doFilter();
            }.bind(this)
        });
    },

    // ---------------------------------------

    validate: function()
    {
        var validationResult = [];

        if ($('edit_form')) {
            validationResult = Form.getElements('edit_form').collect(Validation.validate);

            if ($('auto_mode') && $('auto_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY')) {
                validationResult.push(Validation.validate($('validate_category_selection')));
            }
        } else if ($('category_specific_form')) {
            validationResult = Form.getElements('category_specific_form').collect(Validation.validate);
        }

        if (validationResult.indexOf(false) != -1) {
            return false;
        }

        return true;
    },

    confirm: function()
    {
        if ($('listingAutoActionModeCategoryGroupGrid')) {
            Windows.getFocusedWindow().close();
            return;
        }

        if (!ListingAutoActionHandlerObj.validate()) {
            return;
        }

        ListingAutoActionHandlerObj.collectData();

        var callback;
        if (ListingAutoActionHandlerObj.internalData.auto_mode == M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY')) {
            callback = ListingAutoActionHandlerObj.loadAutoActionHtml.bind(ListingAutoActionHandlerObj);
        } else {
            callback = Windows.getFocusedWindow().close.bind(Windows.getFocusedWindow());
        }

        ListingAutoActionHandlerObj.submitData(callback);
    },

    collectData: function()
    {
        if ($('auto_mode')) {
            switch (parseInt($('auto_mode').value)) {
                case M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL'):
                    ListingAutoActionHandlerObj.internalData = {
                        auto_mode: $('auto_mode').value,
                        auto_global_adding_mode: $('auto_global_adding_mode').value,
                        auto_global_adding_add_not_visible: $('auto_global_adding_add_not_visible').value,
                        auto_global_adding_template_category_id: null
                    };
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE'):
                    ListingAutoActionHandlerObj.internalData = {
                        auto_mode: $('auto_mode').value,
                        auto_website_adding_mode: $('auto_website_adding_mode').value,
                        auto_website_adding_add_not_visible: $('auto_website_adding_add_not_visible').value,
                        auto_website_adding_template_category_id: null,
                        auto_website_deleting_mode: $('auto_website_deleting_mode').value
                    };
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY'):
                    ListingAutoActionHandlerObj.internalData = {
                        id: $('group_id').value,
                        title: $('group_title').value,
                        auto_mode: $('auto_mode').value,
                        adding_mode: $('adding_mode').value,
                        adding_add_not_visible: $('adding_add_not_visible').value,
                        deleting_mode: $('deleting_mode').value,
                        categories: categories_selected_items
                    };
                    break;
            }
        }

        if ($('ebay_category_chooser')) {
            ListingAutoActionHandlerObj.internalData.template_category_data = EbayListingCategoryChooserHandlerObj.getInternalData();
        }

        if ($('category_specific_form')) {
            ListingAutoActionHandlerObj.internalData.template_category_specifics_data = EbayListingCategorySpecificHandlerObj.getInternalData();
        }
    }

    // ---------------------------------------
});
