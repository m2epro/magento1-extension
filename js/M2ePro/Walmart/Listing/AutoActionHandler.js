WalmartListingAutoActionHandler = Class.create(ListingAutoActionHandler, {

    // ---------------------------------------

    getController: function()
    {
        return 'adminhtml_walmart_listing_autoAction';
    },

    // ---------------------------------------

    addingModeChange: function(el)
    {
        if (el.target.value != M2ePro.php.constant('Ess_M2ePro_Model_Listing::ADDING_MODE_ADD')) {
            $('adding_category_template_id').value = '';
        }

        if (el.target.value != M2ePro.php.constant('Ess_M2ePro_Model_Listing::ADDING_MODE_NONE')) {
            $$('[id$="adding_add_not_visible_field"]')[0].show();
            $('auto_action_walmart_add_and_assign_category_template').show();
        } else {
            $('auto_action_walmart_add_and_assign_category_template').hide();
            $$('[id$="adding_add_not_visible"]')[0].value = M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES');
            $$('[id$="adding_add_not_visible_field"]')[0].hide();
        }

        $('continue_button').hide();
        $('confirm_button').show();
    },

    // ---------------------------------------

    collectData: function()
    {
        if ($('auto_mode')) {
            switch (parseInt($('auto_mode').value)) {
                case M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL'):
                    ListingAutoActionHandlerObj.internalData = {
                        auto_mode: $('auto_mode').value,
                        auto_global_adding_mode: $('auto_global_adding_mode').value,
                        auto_global_adding_add_not_visible: $('auto_global_adding_add_not_visible').value,
                        adding_category_template_id: $('adding_category_template_id').value
                    };
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE'):
                    ListingAutoActionHandlerObj.internalData = {
                        auto_mode: $('auto_mode').value,
                        auto_website_adding_mode: $('auto_website_adding_mode').value,
                        auto_website_adding_add_not_visible: $('auto_website_adding_add_not_visible').value,
                        auto_website_deleting_mode: $('auto_website_deleting_mode').value,
                        adding_category_template_id: $('adding_category_template_id').value
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
                        categories: categories_selected_items,
                        adding_category_template_id: $('adding_category_template_id').value
                    };
                    break;
            }
        }
    },

    reloadCategoriesTemplates: function() {

        var select = $('adding_category_template_id');

        new Ajax.Request(M2ePro.url.getCategoryTemplates, {
            onSuccess: function(transport) {

                var data = transport.responseText.evalJSON(true);

                var options = '';

                var firstItemValue = '';
                var currentValue = select.value;

                data.each(function(item) {
                    var key = item.id;
                    var val = item.title;
                    var disabled = item.is_new_asin_accepted == 0 ? ' disabled="disabled"' : '';

                    options += '<option value="' + key + '"' + disabled + '>' + val + '</option>\n';

                    if (firstItemValue == '') {
                        firstItemValue = key;
                    }
                });

                select.update();
                select.insert(options);

                if (currentValue != '') {
                    $('adding_category_template_id').value = currentValue;
                } else {
                    if (M2ePro.formData[id] > 0) {
                        select.value = M2ePro.formData[id];
                    } else {
                        select.value = firstItemValue;
                    }
                }
                select.simulate('change');
            }
        });
    },

    // ---------------------------------------

    addNewTemplate: function(url, callback)
    {
        var win = window.open(url);

        var intervalId = setInterval(function() {

            if (!win.closed) {
                return;
            }

            clearInterval(intervalId);

            callback && callback();

        }, 1000);
    }

    // ---------------------------------------

});