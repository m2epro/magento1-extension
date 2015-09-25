ListingAutoActionHandler = Class.create(CommonHandler, {

    //----------------------------------

    controller: 'adminhtml_common_listing_autoAction',

    internalData: {},

    magentoCategoryIdsFromOtherGroups: {},
    magentoCategoryTreeChangeEventInProgress: false,

    //----------------------------------

    initialize: function()
    {
        Validation.add('M2ePro-validate-category-selection', M2ePro.translator.translate('You must select at least 1 Category.'), function() {
            return categories_selected_items.length > 0
        });

        Validation.add('M2ePro-validate-category-group-title', M2ePro.translator.translate('Rule with the same Title already exists.'), function(value, element) {

            var unique = true;

            new Ajax.Request(M2ePro.url.get(ListingAutoActionHandlerObj.controller + '/isCategoryGroupTitleUnique'), {
                method: 'get',
                asynchronous: false,
                parameters: {
                    group_id: $('group_id').value,
                    title: $('group_title').value
                },
                onSuccess: function(transport) {
                    unique = transport.responseText.evalJSON()['unique'];
                }
            });

            return unique;
        });
    },

    clear: function()
    {
        this.internalData = {};
        this.magentoCategoryTreeChangeEventInProgress = false;
    },

    //----------------------------------

    loadAutoActionHtml: function(mode, callback)
    {
        new Ajax.Request(M2ePro.url.get(ListingAutoActionHandlerObj.controller + '/index'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                auto_mode: mode || null
            },
            onSuccess: function(transport) {

                var content = transport.responseText;
                var title = M2ePro.translator.translate('Auto Add/Remove Rules');

                this.clear();
                this.openPopUp(title, content);

                if (typeof callback == 'function') {
                    callback();
                }
            }.bind(this)
        });
    },

    //----------------------------------

    openPopUp: function(title, content)
    {
        var config = {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 50,
            maxHeight: 500,
            width: 900,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() {
                ListingAutoActionHandlerObj.clear();

                return true;
            }
        };

        try {
            Windows.getFocusedWindow() || Dialog.info(null, config);
            Windows.getFocusedWindow().setTitle(title);
            $('modal_dialog_message').innerHTML = content;
            $('modal_dialog_message').innerHTML.evalScripts();
        } catch (ignored) {}

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '500px';
        }, 50);
    },

    //----------------------------------

    addingModeChange: function()
    {
        $('continue_button').hide();
        $('confirm_button').show();
    },

    //----------------------------------

    loadAutoCategoryForm: function(groupId, callback)
    {
        new Ajax.Request(M2ePro.url.get(ListingAutoActionHandlerObj.controller + '/getAutoCategoryFormHtml'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                group_id: groupId || null
            },
            onSuccess: function(transport) {

                $('data_container').replace(transport.responseText);
                this.magentoCategoryTreeChangeEventInProgress = false;

                if (typeof callback == 'function') {
                    callback();
                }
            }.bind(this)
        });
    },

    magentoCategorySelectCallback: function(selectedCategories)
    {
        if (this.magentoCategoryTreeChangeEventInProgress) {
            return;
        }

        this.magentoCategoryTreeChangeEventInProgress = true;

        var latestCategory = selectedCategories[selectedCategories.length - 1];

        if (!latestCategory || typeof this.magentoCategoryIdsFromOtherGroups[latestCategory] == 'undefined') {
            this.magentoCategoryTreeChangeEventInProgress = false;
            return;
        }

        var template = $('dialog_confirm_container');

        template.down('.dialog_confirm_content').innerHTML = $('dialog_confirm_content').innerHTML;
        template.down('.dialog_confirm_content').innerHTML = template.down('.dialog_confirm_content')
            .innerHTML
            .replace('%s', this.magentoCategoryIdsFromOtherGroups[latestCategory].title);

        Dialog._openDialog(template.innerHTML, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            title: 'Remove Category',
            width: 400,
            height: 80,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            id: "selected-category-already-used",
            ok: function() {
                new Ajax.Request(M2ePro.url.get(ListingAutoActionHandlerObj.controller + '/deleteCategory'), {
                    method: 'post',
                    asynchronous: true,
                    parameters: {
                        group_id: this.magentoCategoryIdsFromOtherGroups[latestCategory].id,
                        category_id: latestCategory
                    },
                    onSuccess: function(transport) {
                        delete ListingAutoActionHandlerObj.magentoCategoryIdsFromOtherGroups[latestCategory];
                    }
                });

                return true;
            }.bind(this),
            cancel: function() {
                tree.getNodeById(latestCategory).ui.check(false);
            },
            onClose: function() {
                ListingAutoActionHandlerObj.magentoCategoryTreeChangeEventInProgress = false;
            }
        });
    },

    //----------------------------------

    highlightBreadcrumbStep: function(step)
    {
        $$('#breadcrumb_container .breadcrumb').each(function(element) { element.removeClassName('selected'); });

        $('step_' + step).addClassName('selected');
    },

    //----------------------------------

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
        if (!ListingCategoryChooserHandlerObj.validate()) {
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

    //----------------------------------

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
        if (!ListingCategoryChooserHandlerObj.validate()) {
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

    //----------------------------------

    isCategoryAlreadyUsed: function(categoryId)
    {
        return this.magentoCategoryUsedIds.indexOf(categoryId) != -1;
    },

    categoryCancel: function()
    {
        ListingAutoActionHandlerObj.loadAutoActionHtml(
            M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY')
        );
    },

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
        ListingAutoActionHandlerObj.collectData();

        var callback = function() {
            ListingAutoActionHandlerObj.highlightBreadcrumbStep(3);

            $('confirm_button').show();
            $('continue_button').hide();
        };
    },

    //----------------------------------

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
            onSuccess: function(transport)
            {
                listingAutoActionModeCategoryGroupGridJsObject.doFilter();
            }.bind(this)
        });
    },

    //----------------------------------

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
                        auto_global_adding_mode: $('auto_global_adding_mode').value
                    };
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE'):
                    ListingAutoActionHandlerObj.internalData = {
                        auto_mode: $('auto_mode').value,
                        auto_website_adding_mode: $('auto_website_adding_mode').value,
                        auto_website_deleting_mode: $('auto_website_deleting_mode').value
                    };
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY'):
                    ListingAutoActionHandlerObj.internalData = {
                        id: $('group_id').value,
                        title: $('group_title').value,
                        auto_mode: $('auto_mode').value,
                        adding_mode: $('adding_mode').value,
                        deleting_mode: $('deleting_mode').value,
                        categories: categories_selected_items
                    };
                    break;
            }
        }
    },

    submitData: function(callback)
    {
        var data = this.internalData;

        new Ajax.Request(M2ePro.url.get(ListingAutoActionHandlerObj.controller + '/save'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                auto_action_data: Object.toJSON(data)
            },
            onSuccess: function(transport) {
                if (typeof callback == 'function') {
                    callback();
                }
            }
        });
    },

    reset: function(skipConfirmation)
    {
        skipConfirmation = skipConfirmation || false;

        if (!skipConfirmation && !confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get(ListingAutoActionHandlerObj.controller + '/reset'), {
            method: 'post',
            asynchronous: true,
            parameters: {},
            onSuccess: function(transport) {
                ListingAutoActionHandlerObj.loadAutoActionHtml();
            }
        });
    }

    //----------------------------------
});
