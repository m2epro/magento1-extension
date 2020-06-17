window.ListingAutoAction = Class.create(Common, {

    // ---------------------------------------

    internalData: {},

    magentoCategoryIdsFromOtherGroups: {},
    magentoCategoryTreeChangeEventInProgress: false,

    // ---------------------------------------

    getController: function()
    {
        throw Error('Method should be overrided and return controller')
    },

    // ---------------------------------------

    initialize: function()
    {
        Validation.add('M2ePro-validate-mode', M2ePro.translator.translate('This is a required field.'), function() {
            return $$('input[name="auto_mode"]').any(function(el) {
                return el.checked;
            })
        });

        Validation.add('M2ePro-validate-category-selection', M2ePro.translator.translate('You must select at least 1 Category.'), function() {
            return categories_selected_items.length > 0
        });

        Validation.add('M2ePro-validate-category-group-title', M2ePro.translator.translate('Rule with the same Title already exists.'), function(value, element) {

            var unique = true;

            new Ajax.Request(M2ePro.url.get(ListingAutoActionObj.getController() + '/isCategoryGroupTitleUnique'), {
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

    // ---------------------------------------

    loadAutoActionHtml: function(mode)
    {
        new Ajax.Request(M2ePro.url.get(ListingAutoActionObj.getController() + '/index'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                auto_mode: mode || null
            },
            onSuccess: function(transport) {

                var content = transport.responseText;
                var title = M2ePro.translator.translate('Auto Add/Remove Rules');

                this.clear();
                Windows.closeAll();
                this.openPopUp(title, content);

            }.bind(this)
        });
    },

    // ---------------------------------------

    openPopUp: function(title, content)
    {
        var self = this;
        var popup = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 20,
            maxHeight: 500,
            width: 900,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() {

                ListingAutoActionObj.clear();
                return true;
            }
        });

        popup.options.destroyOnClose = true;

        $('modal_dialog_message').innerHTML = content;
        $('modal_dialog_message').innerHTML.evalScripts();

        self.autoHeightFix();
    },

    // ---------------------------------------

    addingModeContinue: function()
    {
        var validationResult = Form.getElements('edit_form').collect(Validation.validate);
        if (validationResult.indexOf(false) != -1) {
            return false;
        }

        var mode = $$('input[name="auto_mode"]:checked')[0].value;
        ListingAutoActionObj.loadAutoActionHtml(mode);
    },

    addingModeCancel: function()
    {
        Windows.getFocusedWindow().close();
    },

    addingModeChange: function()
    {
        $('continue_button').hide();
        $('confirm_button').show();

        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Listing::ADDING_MODE_NONE')) {
            $$('[id$="adding_add_not_visible_field"]')[0].show();
        } else {
            $$('[id$="adding_add_not_visible"]')[0].value = M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES');
            $$('[id$="adding_add_not_visible_field"]')[0].hide();
        }
    },

    // ---------------------------------------

    loadAutoCategoryForm: function(groupId, callback)
    {
        new Ajax.Request(M2ePro.url.get(ListingAutoActionObj.getController() + '/getAutoCategoryFormHtml'), {
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
                new Ajax.Request(M2ePro.url.get(ListingAutoActionObj.getController() + '/deleteCategory'), {
                    method: 'post',
                    asynchronous: true,
                    parameters: {
                        group_id: this.magentoCategoryIdsFromOtherGroups[latestCategory].id,
                        category_id: latestCategory
                    },
                    onSuccess: function(transport) {
                        delete ListingAutoActionObj.magentoCategoryIdsFromOtherGroups[latestCategory];
                    }
                });

                return true;
            }.bind(this),
            cancel: function() {
                tree.getNodeById(latestCategory).ui.check(false);
            },
            onClose: function() {
                ListingAutoActionObj.magentoCategoryTreeChangeEventInProgress = false;
            }
        });
    },

    // ---------------------------------------

    isCategoryAlreadyUsed: function(categoryId)
    {
        return this.magentoCategoryUsedIds.indexOf(categoryId) != -1;
    },

    categoryCancel: function()
    {
        ListingAutoActionObj.loadAutoActionHtml(
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

    // ---------------------------------------

    categoryDeleteGroup: function(groupId)
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get(ListingAutoActionObj.getController() + '/deleteCategoryGroup'), {
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

    // ---------------------------------------

    validate: function()
    {
        var validationResult = [];

        if ($('edit_form')) {
            validationResult = Form.getElements('edit_form').collect(Validation.validate);

            if ($('auto_mode') && $('auto_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY')) {
                validationResult.push(Validation.validate($('validate_category_selection')));
            }
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

        if (!ListingAutoActionObj.validate()) {
            return;
        }

        ListingAutoActionObj.collectData();

        var callback = function() {
            var mode = ListingAutoActionObj.internalData.auto_mode;
            Windows.closeAll.bind(Windows)();
            if (mode == M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY')) {
                ListingAutoActionObj.loadAutoActionHtml.bind(ListingAutoActionObj)();
            }
        };

        ListingAutoActionObj.submitData(callback);
    },

    collectData: function()
    {
        if ($('auto_mode')) {
            switch (parseInt($('auto_mode').value)) {
                case M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL'):
                    ListingAutoActionObj.internalData = {
                        auto_mode                          : $('auto_mode').value,
                        auto_global_adding_mode            : $('auto_global_adding_mode').value,
                        auto_global_adding_add_not_visible : $('auto_global_adding_add_not_visible').value
                    };
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE'):
                    ListingAutoActionObj.internalData = {
                        auto_mode                           : $('auto_mode').value,
                        auto_website_adding_mode            : $('auto_website_adding_mode').value,
                        auto_website_adding_add_not_visible : $('auto_website_adding_add_not_visible').value,
                        auto_website_deleting_mode          : $('auto_website_deleting_mode').value
                    };
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY'):
                    ListingAutoActionObj.internalData = {
                        id                     : $('group_id').value,
                        title                  : $('group_title').value,
                        auto_mode              : $('auto_mode').value,
                        adding_mode            : $('adding_mode').value,
                        adding_add_not_visible : $('adding_add_not_visible').value,
                        deleting_mode          : $('deleting_mode').value,
                        categories             : categories_selected_items
                    };
                    break;
            }
        }
    },

    submitData: function(callback)
    {
        var data = this.internalData;

        new Ajax.Request(M2ePro.url.get(ListingAutoActionObj.getController() + '/save'), {
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

        new Ajax.Request(M2ePro.url.get(ListingAutoActionObj.getController() + '/reset'), {
            method: 'post',
            asynchronous: true,
            parameters: {},
            onSuccess: function(transport) {
                Windows.closeAll();
                ListingAutoActionObj.loadAutoActionHtml();
            }
        });
    }

    // ---------------------------------------
});
