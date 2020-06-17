window.EbayListingAutoAction = Class.create(ListingAutoAction, {

    // ---------------------------------------

    getController: function()
    {
        return 'adminhtml_ebay_listing_autoAction';
    },

    // ---------------------------------------

    addingModeChange: function($super)
    {
        $super();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY')) {
            $('confirm_button').hide();
            $('continue_button').show();
        } else {
            $('continue_button').hide();
            $('confirm_button').show();
        }
    },

    // ---------------------------------------

    loadCategoryChooser: function(callback)
    {
        new Ajax.Request(M2ePro.url.get(ListingAutoActionObj.getController() + '/getCategoryChooserHtml'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                auto_mode : $('auto_mode').value,
                group_id  : this.internalData.id,
                // this parameter only for auto_mode=category
                magento_category_id: typeof categories_selected_items != 'undefined' ? categories_selected_items[0] : null
            },
            onSuccess: function(transport) {
                $('data_container').update();
                $('ebay_category_chooser').update(transport.responseText);

                if (typeof callback == 'function') {
                    callback();
                }
            }.bind(this)
        });
    },

    // ---------------------------------------

    globalStepTwo: function()
    {
        ListingAutoActionObj.collectData();
        ListingAutoActionObj.loadCategoryChooser(
            function() {
                $('confirm_button').show();
                $('continue_button').hide();
            }
        );
    },

    websiteStepTwo: function()
    {
        ListingAutoActionObj.collectData();
        ListingAutoActionObj.loadCategoryChooser(
            function() {
                $('confirm_button').show();
                $('continue_button').hide();
            }
        );
    },

    categoryStepTwo: function()
    {
        if (!ListingAutoActionObj.validate()) {
            return;
        }

        ListingAutoActionObj.collectData();
        ListingAutoActionObj.loadCategoryChooser(
            function() {
                $('confirm_button').show();
                $('continue_button').hide();
            }
        );
    },

    // ---------------------------------------

    collectData: function($super)
    {
        $super();
        if (typeof EbayTemplateCategoryChooserObj !== 'undefined') {
            var selectedCategories = EbayTemplateCategoryChooserObj.selectedCategories;
            var typeMain = M2ePro.php.constant('Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN');
            if (typeof selectedCategories[typeMain] !== 'undefined') {
                selectedCategories[typeMain]['specific'] = EbayTemplateCategoryChooserObj.selectedSpecifics;
            }
            ListingAutoActionObj.internalData.template_category_data = selectedCategories;
        }
    }

    // ---------------------------------------
});
