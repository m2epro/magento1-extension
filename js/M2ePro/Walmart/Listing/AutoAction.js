window.WalmartListingAutoAction = Class.create(ListingAutoAction, {

    // ---------------------------------------

    getController: function()
    {
        return 'adminhtml_walmart_listing_autoAction';
    },

    // ---------------------------------------

    addingModeChange: function(el)
    {
        if (el.target.value != M2ePro.php.constant('Ess_M2ePro_Model_Listing::ADDING_MODE_ADD')) {
            $('adding_product_type_id').value = '';
        }

        if (el.target.value != M2ePro.php.constant('Ess_M2ePro_Model_Listing::ADDING_MODE_NONE')) {
            $$('[id$="adding_add_not_visible_field"]')[0].show();
            $('auto_action_walmart_add_and_assign_product_type').show();
        } else {
            $('auto_action_walmart_add_and_assign_product_type').hide();
            $$('[id$="adding_add_not_visible"]')[0].value = M2ePro.php.constant('Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES');
            $$('[id$="adding_add_not_visible_field"]')[0].hide();
        }

        $('continue_button').hide();
        $('confirm_button').show();
    },

    // ---------------------------------------

    collectData: function($super)
    {
        $super();
        if ($('auto_mode')) {
            ListingAutoActionObj.internalData = Object.extend(
                ListingAutoActionObj.internalData,
                {
                    adding_product_type_id : $('adding_product_type_id').value
                }
            );
        }
    },

    reloadProductTypes: function() {

        var select = $('adding_product_type_id');

        new Ajax.Request(M2ePro.url.getProductTypes, {
            onSuccess: function(transport) {

                var data = transport.responseText.evalJSON(true);

                var options = '';

                var firstItemValue = '';
                var currentValue = select.value;

                data.each(function(item) {
                    var key = item.id;
                    var val = item.title;
                    
                    options += '<option value="' + key + '"' + '>' + val + '</option>\n';

                    if (firstItemValue == '') {
                        firstItemValue = key;
                    }
                });

                select.update();
                select.insert(options);

                if (currentValue != '') {
                    $('adding_product_type_id').value = currentValue;
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

    addNewProductType: function(url, callback)
    {
        return this.openWindow(url, callback);
    }

    // ---------------------------------------

});
