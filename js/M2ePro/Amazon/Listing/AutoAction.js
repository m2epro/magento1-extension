window.AmazonListingAutoAction = Class.create(ListingAutoAction, {

    // ---------------------------------------

    getController: function()
    {
        return 'adminhtml_amazon_listing_autoAction';
    },

    // ---------------------------------------

    addingModeChange: function(el)
    {
        if (el.target.value == M2ePro.php.constant('Ess_M2ePro_Model_Listing::ADDING_MODE_ADD')) {

            $('auto_action_amazon_add_and_create_asin').show();
        } else {
            $('auto_action_amazon_add_and_create_asin').hide();
            $('auto_action_amazon_add_and_assign_product_type_template').hide();
            $('auto_action_create_asin').value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::ADDING_MODE_ADD_AND_CREATE_NEW_ASIN_NO');
            $('adding_product_type_template_id').value = '';
        }

        if (el.target.value != M2ePro.php.constant('Ess_M2ePro_Model_Listing::ADDING_MODE_NONE')) {
            $$('[id$="adding_add_not_visible_field"]')[0].show();
        } else {
            $$('[id$="adding_add_not_visible"]')[0].value = M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES');
            $$('[id$="adding_add_not_visible_field"]')[0].hide();
        }

        $('continue_button').hide();
        $('confirm_button').show();
    },

    // ---------------------------------------

    createAsinChange: function(el)
    {
        if (el.target.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Listing::ADDING_MODE_ADD_AND_CREATE_NEW_ASIN_YES')) {
            $('auto_action_amazon_add_and_assign_product_type_template').show();
        } else {
            $('auto_action_amazon_add_and_assign_product_type_template').hide();
            $('adding_product_type_template_id').value = '';
        }
    },

    // ---------------------------------------

    collectData: function($super)
    {
        $super();
        if ($('auto_mode')) {
            ListingAutoActionObj.internalData = Object.extend(
                ListingAutoActionObj.internalData,
                {
                    adding_product_type_template_id : $('adding_product_type_template_id').value
                }
            );
        }
    },

    reloadProductTypeTemplates: function() {

        var select = $('adding_product_type_template_id');

        new Ajax.Request(M2ePro.url.getProductTypeTemplates, {
            onSuccess: function(transport) {

                var data = transport.responseText.evalJSON(true);

                var options = '<option></option>';

                var firstItem = null;
                var currentValue = select.value;

                data.each(function(item) {
                    options += `<option value="${item.id}">${item.title}</option>`;

                    if (!firstItem) {
                        firstItem = item;
                    }
                });

                select.update();
                select.insert(options);

                if (currentValue != '') {
                    $('adding_product_type_template_id').value = currentValue;
                } else {
                    if (M2ePro.formData[id] > 0) {
                        select.value = M2ePro.formData[id];
                    } else {
                        select.value = firstItem.id;
                    }
                }

                select.simulate('change');
            }
        });
    },

    // ---------------------------------------

    addNewProductType: function(url, callback)
    {
        return this.openWindow(url, callback);
    },

    // ---------------------------------------

});
