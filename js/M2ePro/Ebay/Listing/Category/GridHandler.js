EbayListingCategoryGridHandler = Class.create(GridHandler, {

    //----------------------------------

    prepareActions: function()
    {
        this.actions = {

            editCategoriesAction: function(id) {

                id && this.selectByRowId(id);
                this.editCategories();

            }.bind(this),

            editPrimaryCategoriesAction: function(id) {

                id && this.selectByRowId(id);
                this.editPrimaryCategories();

            }.bind(this),

            editStorePrimaryCategoriesAction: function(id) {

                id && this.selectByRowId(id);
                this.editStorePrimaryCategories();

            }.bind(this)

        };
    },

    //----------------------------------

    editPrimaryCategories: function()
    {
        alert('abstract editPrimaryCategories');
    },

    editStorePrimaryCategories: function()
    {
        alert('abstract editPrimaryCategories');
    },

    editCategoriesByType: function(type, validationRequired)
    {
        validationRequired = validationRequired || false;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_categorySettings/getChooserBlockHtml'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                ids: this.getSelectedProductsString()
            },
            onSuccess: function(transport) {

                var temp = document.createElement('div');
                temp.innerHTML = transport.responseText;
                temp.innerHTML.evalScripts();

                EbayListingCategoryChooserHandlerObj.showEditPopUp(type);

                validationRequired && (EbayListingCategoryChooserHandlerObj .categoriesRequiringValidation[type] = true);

                EbayListingCategoryChooserHandlerObj.doneCallback = function() {
                    this.saveCategoriesData(EbayListingCategoryChooserHandlerObj.getInternalDataByType(type));

                    EbayListingCategoryChooserHandlerObj.doneCallback = null;
                    EbayListingCategoryChooserHandlerObj.cancelCallback = null;

                    validationRequired && (delete EbayListingCategoryChooserHandlerObj.categoriesRequiringValidation[type]);
                }.bind(this);

                EbayListingCategoryChooserHandlerObj.cancelCallback = function() {
                    this.unselectAll();

                    EbayListingCategoryChooserHandlerObj.doneCallback = null;
                    EbayListingCategoryChooserHandlerObj.cancelCallback = null;

                    validationRequired && (delete EbayListingCategoryChooserHandlerObj.categoriesRequiringValidation[type]);
                }.bind(this);

            }.bind(this)
        });
    },

    saveCategoriesData: function(templateData)
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepTwoSaveToSession'), {
            method: 'post',
            parameters: {
                ids: this.getSelectedProductsString(),
                template_data: Object.toJSON(templateData)
            },
            onSuccess: function(transport) {

                this.unselectAll();
                this.getGridObj().doFilter();

                Windows.getFocusedWindow() && Windows.getFocusedWindow().close();
            }.bind(this)
        });
    },

    //----------------------------------

    editCategories: function()
    {
        alert('abstract editCategories');
    },

    //----------------------------------

    getComponent: function()
    {
        return 'ebay';
    }

    //----------------------------------
});