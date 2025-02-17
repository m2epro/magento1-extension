window.AmazonProductTypeFinder = Class.create(Common, {
    currentProductType: null,

    initialize: function () {
        this.rootContainer = null;
        this.containerMap = new Map();
    },

    getMarketplaceId: function () {
        const marketplaceId = $('general_marketplace_id').value;

        return marketplaceId !== undefined ? marketplaceId : 0;
    },

    renderRootCategories: function (containerId) {
        const self = this;
        this.rootContainer = $(containerId);

        if (!this.rootContainer) {
            this.rootContainer = document.createElement('div');
            this.rootContainer.id = containerId;
            this.rootContainer.textContent = 'Loading categories...';
            document.body.appendChild(this.rootContainer);
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_amazon_productTypes/getCategories'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                marketplace_id: self.getMarketplaceId()
            },
            onSuccess: function (transport) {
                const response = transport.responseText;
                const parsedResponse = JSON.parse(response);
                const categories = parsedResponse.categories;

                if (self.rootContainer) {
                    self.rootContainer.innerHTML = '';

                    categories.each(function (category) {
                        var categoryOption = document.createElement('option');
                        categoryOption.value = category.name;
                        categoryOption.setAttribute('is-leaf', category.is_leaf ? '1' : '0');
                        categoryOption.textContent = category.name + (category.is_leaf ? '' : ' >');
                        self.rootContainer.appendChild(categoryOption);

                        categoryOption.addEventListener('click', function () {
                            self.categoryPath = [category.name];
                            self.clearChildCategories(self.rootContainer);
                            self.renderChildCategories(self.rootContainer, self.categoryPath);
                        });
                    });
                }
            }
        });
    },

    //@todo to find places where it is called and where arguments are passed
    renderChildCategories: function (parentContainer, path) {
        var self = this;
        var childContainer = parentContainer.nextElementSibling;

        this.clearChildCategories(childContainer);
        this.clearFollowingContainers(parentContainer);

        if (!childContainer || childContainer.className !== 'child-block') {
            childContainer = document.createElement('div');
            childContainer.className = 'child-block';
            childContainer.style.marginLeft = '10px';
            parentContainer.parentNode.insertBefore(childContainer, parentContainer.nextSibling);
            this.containerMap.set(childContainer, []);
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_amazon_productTypes/getCategories'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                marketplace_id: self.getMarketplaceId(),
                criteria: JSON.stringify(path)
            },
            onSuccess: function (transport) {
                var response = transport.responseText;
                var parsedResponse = JSON.parse(response);
                var items = parsedResponse.categories;

                var select = document.createElement('select');
                select.className = 'multiselect admin__control-multiselect';
                select.style.minWidth = '200px';
                select.style.maxHeight = 'none';
                select.size = 8;

                items.each(function (item) {
                    var option = document.createElement('option');
                    option.value = item.path;
                    option.setAttribute('is-leaf', item.isLeaf ? '1' : '0');
                    var optionText = item.name;
                    if (item.isLeaf && (!item.productTypes || item.productTypes.length === 0)) {
                        optionText += ' ';
                    } else {
                        optionText += ' > ';
                    }
                    option.textContent = optionText;

                    select.appendChild(option);

                    option.addEventListener('click', function () {
                        if (item.isLeaf) {
                            if (item.productTypes && item.productTypes.length > 0) {
                                self.clearChildCategories(childContainer);
                                self.renderProductTypes(childContainer, item.productTypes);
                            }
                        } else {
                            var newPath = path.slice();
                            newPath.push(item.name);
                            self.clearChildCategories(childContainer);
                            self.renderChildCategories(childContainer, newPath);
                        }
                    });
                });

                childContainer.innerHTML = '';
                childContainer.appendChild(select);
                self.containerMap.set(childContainer, select);
                childContainer.scrollIntoView({behavior: 'smooth'});
            }
        });
    },

    renderProductTypes: function (parentContainer, productTypes) {
        var self = this;

        var childContainer = document.createElement('div');
        childContainer.className = 'child-block';
        childContainer.style.marginLeft = '10px';

        var select = document.createElement('select');
        select.className = 'multiselect admin__control-multiselect';
        select.style.minWidth = '200px';
        select.style.maxHeight = 'none';
        select.size = 8;

        productTypes.each(function (productType) {
            var option = document.createElement('option');
            option.value = productType.nick;
            option.setAttribute('template-id', productType.templateId);

            if (productType.templateId) {
                option.setAttribute('data-exist-product-type-id', productType.templateId);
            }

            option.textContent = productType.title;

            select.appendChild(option);

            option.addEventListener('click', function () {
                self.clearChildCategories(childContainer);
                self.setCurrentProductType(option, productType);
            });
        });

        childContainer.appendChild(select);

        this.clearFollowingContainers(parentContainer);

        self.clearChildCategories(parentContainer);
        parentContainer.parentNode.insertBefore(childContainer, parentContainer.nextSibling);
        self.containerMap.set(childContainer, select);
        childContainer.scrollIntoView({behavior: 'smooth'});
    },

    clearChildCategories: function (parentContainer) {
        var childContainer = parentContainer.nextElementSibling;

        if (childContainer && childContainer.className === 'child-block') {
            var childSelect = this.containerMap.get(childContainer);
            if (childSelect) {
                childSelect.remove();
            }
            parentContainer.parentNode.removeChild(childContainer);
            this.containerMap.delete(childContainer);
        }

        if (parentContainer.parentNode) {
            var followingContainer = parentContainer.nextElementSibling;
            while (followingContainer) {
                var nextContainer = followingContainer.nextElementSibling;
                this.clearChildCategories(followingContainer);
                followingContainer = nextContainer;
            }
        }

        this.containerMap.forEach(function (childSelect, childContainer) {
            if (!childContainer.previousElementSibling) {
                self.clearChildCategories(childContainer);
            }
        });
    },

    clearFollowingContainers: function (container) {
        var followingContainer = container.nextElementSibling;
        while (followingContainer) {
            var nextContainer = followingContainer.nextElementSibling;
            this.clearChildCategories(followingContainer);
            followingContainer = nextContainer;
        }

        this.containerMap.forEach(function (childSelect, childContainer) {
            if (!childContainer.previousElementSibling) {
                self.clearChildCategories(childContainer);
            }
        });
    },

    setCurrentProductType: function (option, productType) {
        this.currentProductType = productType.nick;

        const selectedProductType = $('search_popup_selected_product_type_title');
        const productTypeNotSelected = $('search_popup_product_type_not_selected');
        const productTypeResetLink = $('product_type_reset_link');
        const confirmButton = $('product_type_confirm');
        const errorContentWrapper = $('product_type_browse_error_content');

        productTypeNotSelected.hide();
        selectedProductType.show();
        selectedProductType.update(productType.title);
        productTypeResetLink.show();

        if (productType.templateId) {
            confirmButton.writeAttribute('disabled', true);

            if (errorContentWrapper) {
                const url = M2ePro.url.get(
                    'adminhtml_amazon_productTypes/edit',
                    {id: option.dataset.existProductTypeId}
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
        }
    },
});
