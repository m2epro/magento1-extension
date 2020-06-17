window.EbayListingCategoryProductGrid = Class.create(EbayListingCategoryGrid, {

    // ---------------------------------------

    productIdCellIndex: 1,
    productTitleCellIndex: 2,

    // ---------------------------------------

    prepareActions: function($super)
    {
        $super();

        this.actions = Object.extend(this.actions, {

            getSuggestedCategoriesAction: function(id) {
                this.getSuggestedCategories(id);
            }.bind(this),
            removeItemAction: function(id) {
                var ids = id ? [id] : this.getSelectedProductsArray();
                this.removeItems(ids);
            }.bind(this)

        });
    },

    // ---------------------------------------

    getSuggestedCategories: function(id)
    {
        this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();
        this.unselectAll();

        if (id && !confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        EbayListingCategoryProductSuggestedSearchObj.search(
            this.selectedProductsIds.join(','), function(searchResult) {
                this.getGridObj().doFilter();
                this.selectedProductsIds = [];

                MessageObj.clearAll();

                if (searchResult.failed > 0) {
                    MessageObj.addError(
                        M2ePro.translator.translate('Suggested Categories were not assigned.')
                                         .replace('%products_count%', searchResult.failed)
                    );
                } else if (searchResult.succeeded > 0) {
                    MessageObj.addSuccess(
                        M2ePro.translator.translate('Suggested Categories were assigned.')
                                         .replace('%products_count%', searchResult.succeeded)
                    );
                }
            }.bind(this)
        );
    },

    getSuggestedCategoriesForAll: function()
    {
        var gridIds = this.getGridMassActionObj().getGridIds().split(',');
        if (gridIds.length > 100 && !confirm('Are you sure?')) {
            return;
        }

        this.getGridMassActionObj().selectAll();
        this.getSuggestedCategories();
    },

    // ---------------------------------------

    removeItems: function(ids)
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        var url = M2ePro.url.get('adminhtml_ebay_listing_categorySettings/stepTwoDeleteProductsModeProduct');
        new Ajax.Request(url, {
            method: 'post',
            parameters: {
                ids: ids.join(',')
            },
            onSuccess: function() {
                this.unselectAllAndReload();
            }.bind(this)
        });
    },

    // ---------------------------------------

    confirm: function($super)
    {
        var action = '';

        $$('select#'+this.gridId+'_massaction-select option').each(function(o) {
            if (o.selected && o.value != '') {
                action = o.value;
            }
        });

        if (action == 'removeItem' ||
            action == 'editCategories' ||
            action == 'editPrimaryCategories' ||
            action == 'editStorePrimaryCategories') {
            return true;
        }

        var result = $super();
        if (action == 'getSuggestedCategories' && !result) {
            this.unselectAll();
        }

        return result;
    }

    // ---------------------------------------
});
