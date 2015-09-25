ListingAutoActionHandler.prototype.addingModeChange = function(el)
{
    if (el.target.value == M2ePro.php.constant('Ess_M2ePro_Model_Listing::ADDING_MODE_ADD') &&
        ListingAutoActionHandlerObj.showCreateNewAsin) {

        $('auto_action_amazon_add_and_create_asin').show();
    } else {
        $('auto_action_amazon_add_and_create_asin').hide();
        $('auto_action_amazon_add_and_assign_description_template').hide();
        $('auto_action_create_asin').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::ADDING_MODE_ADD_AND_CREATE_NEW_ASIN_NO');
        $('adding_description_template_id').value = '';
    }

    $('continue_button').hide();
    $('confirm_button').show();
};

ListingAutoActionHandler.prototype.createAsinChange = function(el)
{
    if (el.target.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::ADDING_MODE_ADD_AND_CREATE_NEW_ASIN_YES')) {
        $('auto_action_amazon_add_and_assign_description_template').show();
    } else {
        $('auto_action_amazon_add_and_assign_description_template').hide();
        $('adding_description_template_id').value = '';
    }
};

ListingAutoActionHandler.prototype.collectData = function()
{
    if ($('auto_mode')) {
        switch (parseInt($('auto_mode').value)) {
            case M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL'):
                ListingAutoActionHandlerObj.internalData = {
                    auto_mode: $('auto_mode').value,
                    auto_global_adding_mode: $('auto_global_adding_mode').value,
                    adding_description_template_id: $('adding_description_template_id').value
                };
                break;

            case M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE'):
                ListingAutoActionHandlerObj.internalData = {
                    auto_mode: $('auto_mode').value,
                    auto_website_adding_mode: $('auto_website_adding_mode').value,
                    auto_website_deleting_mode: $('auto_website_deleting_mode').value,
                    adding_description_template_id: $('adding_description_template_id').value
                };
                break;

            case M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY'):
                ListingAutoActionHandlerObj.internalData = {
                    id: $('group_id').value,
                    title: $('group_title').value,
                    auto_mode: $('auto_mode').value,
                    adding_mode: $('adding_mode').value,
                    deleting_mode: $('deleting_mode').value,
                    categories: categories_selected_items,
                    adding_description_template_id: $('adding_description_template_id').value
                };
                break;
        }
    }
};

ListingAutoActionHandler.prototype.reloadDescriptionTemplates = function() {

    var select = $('adding_description_template_id');

    new Ajax.Request(M2ePro.url.getDescriptionTemplates, {
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
                $('adding_description_template_id').value = currentValue;
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
};

ListingAutoActionHandler.prototype.addNewTemplate = function(url, callback)
{
    var win = window.open(url);

    var intervalId = setInterval(function() {

        if (!win.closed) {
            return;
        }

        clearInterval(intervalId);

        callback && callback();

    }, 1000);
};