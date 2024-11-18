window.AmazonProductTypeSearch = Class.create(Common, {
    productTypeList: [],
    currentProductType: null,

    initPopup: function (productTypeList) {
        this.productTypeList = productTypeList;

        this.setCurrentProductType(AmazonProductTypeObj.getProductType());
        this.applySearchFilter();

        $('product_type_reset_link').observe('click', this.resetCurrentProductType.bind(this));
        $('product_type_search_results').observe('change', this.updateProductTypeByResult.bind(this));

        $$('.product_type_confirm').each(function(element) {
            element.writeAttribute('disabled', true);
        });
    },

    getProductTypeTitle: function (productType) {
        for (var i = 0; i < this.productTypeList.length; i++) {
            if (this.productTypeList[i].nick === productType) {
                return this.productTypeList[i].title;
            }
        }

        return '';
    },

    setCurrentProductType: function (productType) {
        this.currentProductType = productType;
        const title = this.getProductTypeTitle(productType);

        const selectedProductType = $('search_popup_selected_product_type_title');
        const productTypeNotSelected = $('search_popup_product_type_not_selected');
        const productTypeResetLink = $('product_type_reset_link');

        if (title) {
            productTypeNotSelected.hide();
            selectedProductType.show();
            selectedProductType.update(title);
            productTypeResetLink.show();
        } else {
            productTypeNotSelected.show();
            selectedProductType.hide();
            productTypeResetLink.hide();
        }
    },

    applySearchFilter: function () {
        const title = $('product_type_search_query').getValue();
        const productTypes = this.searchProductTypeByTitle(title);

        const container = $('product_type_search_results');
        container.innerHTML = '';
        var productTypeId;

        for (var i = 0; i < productTypes.length; i++) {
            productTypeId = productTypes[i]['exist_product_type_id'] !== undefined ?
                productTypes[i]['exist_product_type_id'] : false;
            this.insertOption(container, productTypes[i].nick, productTypes[i].title, productTypeId);
        }
    },

    resetSearchFilter: function () {
        $('product_type_search_query').value = '';
        this.applySearchFilter();
    },

    searchProductTypeByTitle: function (title) {
        if (!title) {
            return this.productTypeList.clone();
        }

        const titleLowerCase = title.toLowerCase();
        return this.productTypeList.filter(
            function (value) {
                return value.title
                    .toLowerCase()
                    .indexOf(titleLowerCase) !== -1;
            }
        );
    },

    onClickPopupTab: function (item) {
        $$('#productTypeChooserTabs > ul > li').each(function(element) {
            element.removeClassName('ui-tabs-active');
            element.removeClassName('ui-state-active');
        });

        $(item.id).up().addClassName('ui-tabs-active');
        $(item.id).up().addClassName('ui-state-active');

        $$('#chooser_tabs_container > *').each(function(element) {
            element.hide();
        });

        $(item.id + '_content').style.display = 'block';

        this.resetTabsChanges();
    },

    resetTabsChanges: function () {
        $$('.product_type_confirm').each(function(element) {
            element.writeAttribute('disabled', true);
        });

        this.resetCurrentProductType();

        const rootContainer = $('product_type_browse_results');
        AmazonProductTypeFinderObj.clearChildCategories(rootContainer);
        AmazonProductTypeFinderObj.clearFollowingContainers(rootContainer);

        $('product_type_browse_error_content').update('');
        $('product_type_search_error_content').update('');
    },

    updateProductTypeByResult: function (event) {
        const selectValue = event.target.value;
        const selectElement = event.target;
        const options = Array.from(selectElement.options)
        const selectOption = options.find(option => option.value === selectValue);
        const errorContentWrapper = $('product_type_search_error_content');
        const confirmButton = $('product_type_confirm');

        if (selectOption.dataset.existProductTypeId) {
            confirmButton.writeAttribute('disabled', true);

            if (errorContentWrapper) {
                const url = M2ePro.url.get(
                    'adminhtml_amazon_productTypes/edit',
                    {id: selectOption.dataset.existProductTypeId}
                );
                const errorContent = str_replace(
                    'exist_product_type_url',
                    url,
                    M2ePro.translator.translate('product_type_configured')
                );
                errorContentWrapper.update(errorContent);
            }
        } else {
            confirmButton.removeAttribute('disabled');

            if (errorContentWrapper) {
                errorContentWrapper.update('');
            }
            this.setCurrentProductType(selectValue);
        }
    },

    resetCurrentProductType: function () {
        this.setCurrentProductType('');
        $('product_type_search_results').value = '';
        $$('.product_type_confirm').each(function(element) {
            element.writeAttribute('disabled', true);
        });
    },

    insertOption: function (container, value, title, typeId) {
        const productTypeOptions = {value: value};

        if (typeId) {
            productTypeOptions['data-exist-product-type-id'] = typeId;
        }

        const option = new Element('option', productTypeOptions);
        option.innerHTML = title;
        container.appendChild(option);
    }
});