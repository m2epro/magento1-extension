CommonAmazonTemplateDescriptionCategoryChooserHandler = Class.create();
CommonAmazonTemplateDescriptionCategoryChooserHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    specificHandler: null,

    selectedCategoryIdBefore     : null,
    selectedCategoryId           : null,
    preloadedSelectedCategoryObj : null,

    variationThemes: {},

    // ---------------------------------------

    initialize: function()
    {
        this.categoryNodeIdHiddenInput = $('browsenode_id');
        this.categoryPathHiddenInput   = $('category_path');
    },

    // ---------------------------------------

    setSpecificHandler: function(object)
    {
        this.specificHandler = object;
    },

    // ---------------------------------------

    showEditCategoryPopUp: function()
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_template_description/getCategoryChooserHtml'), {
            method: 'post',
            parameters: {
                marketplace_id: $('marketplace_id').value,
                browsenode_id:  self.categoryNodeIdHiddenInput.value,
                category_path:  self.categoryPathHiddenInput.value
            },
            onSuccess: function(transport) {

                if (typeof self.popUp != 'undefined') {
                    self.popUp.close();
                }

                var html = transport.responseText;
                var callback = function(transport) {

                    var categoryInfo = transport.responseText.evalJSON();

                    self.selectedCategoryId           = null;
                    self.selectedCategoryIdBefore     = null;
                    self.preloadedSelectedCategoryObj = null;

                    if (typeof categoryInfo.category_id != 'undefined') {

                        self.selectedCategoryId           = categoryInfo.category_id;
                        self.selectedCategoryIdBefore     = categoryInfo.category_id;
                        self.preloadedSelectedCategoryObj = categoryInfo;
                    }
                };

                self.openPopUp(html);

                if (self.categoryNodeIdHiddenInput.value && self.categoryPathHiddenInput.value) {
                    self.getCategoryInfoFromDictionaryBrowseNodeId(self.categoryNodeIdHiddenInput.value,
                                                                   self.categoryPathHiddenInput.value,
                                                                   callback);
                }
            }
        });
    },

    openPopUp: function(html)
    {
        var self = this;

        self.popUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.translator.translate('Change Category'),
            top: 100,
            width: 850,
            height: 420,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        $('modal_dialog_message').style.paddingTop = '20px';
        $('modal_dialog_message').insert(html);
        $('modal_dialog_message').innerHTML.evalScripts();
    },

    cancelPopUp: function()
    {
        var self = this;
        self.popUp.close();
    },

    // ---------------------------------------

    renderTopLevelCategories: function(containerId)
    {
        this.prepareDomStructure(null, $(containerId));
        this.renderChildCategories(null);
    },

    renderChildCategories: function(parentCategoryId)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_template_description/getChildCategories'), {
            method: 'post',
            parameters: {
                marketplace_id     : $('marketplace_id').value,
                parent_category_id : parentCategoryId
            },
            onSuccess: function(transport) {

                if (transport.responseText.length <= 2) {
                    return;
                }

                var categories = transport.responseText.evalJSON();
                self.tempCategories = categories;

                var selectEl = $(self.getCategoriesSelectElementId(parentCategoryId));

                categories.each(function(category) {

                    var option = new Element('option', {value: category.category_id});
                    option.observe('click', function() {
                        self.browseClickCategory.call(self, selectEl, category.category_id);
                    });

                    var arrowString = category.is_leaf == 0 ? ' > ' : '';
                    selectEl.appendChild(option).update(category.title + arrowString);
                });

                selectEl.style.display = 'inline-block';
                $('chooser_browser').scrollLeft = $('chooser_browser').scrollWidth;
            }
        });
    },

    // ---------------------------------------

    browseClickCategory: function(selectObj, categoryId)
    {
        var callback = function(transport) {

            var categoryInfo = transport.responseText.evalJSON();

            if (categoryInfo.is_leaf == 1) {

                this.selectedCategoryId           = categoryInfo.category_id;
                this.preloadedSelectedCategoryObj = categoryInfo;

                this.updateCurrentlySelectedCategorySpan(categoryInfo);
            }

            var parentCategoryId = selectObj.id.replace(this.getCategoriesSelectElementId(''), '');
            var parentDiv = $(this.getCategoryChildrenElementId(parentCategoryId));
            parentDiv.innerHTML = '';

            this.prepareDomStructure(categoryId, parentDiv);
            this.renderChildCategories(categoryId);
        };

        this.getCategoryInfoFromDictionaryByCategoryId(categoryId, callback);
    },

    prepareDomStructure: function(categoryId, parentDiv)
    {
        var childrenSelect = new Element('select', {
            id: this.getCategoriesSelectElementId(categoryId),
            style: 'min-width: 200px; display: none;',
            size: '10'
        });
        parentDiv.appendChild(childrenSelect);

        var childrenDiv = new Element('div', {
            id    : this.getCategoryChildrenElementId(categoryId),
            class : 'category-children-block'
        });
        parentDiv.appendChild(childrenDiv);
    },

    getCategoriesSelectElementId: function(categoryId)
    {
        if (categoryId === null) categoryId = 0;
        return 'category_chooser_select_' + categoryId;
    },

    getCategoryChildrenElementId: function(categoryId)
    {
        if (categoryId === null) categoryId = 0;
        return 'category_chooser_children_' + categoryId;
    },

    // ---------------------------------------

    confirmCategory: function()
    {
        var self = AmazonTemplateDescriptionCategoryChooserHandlerObj;

        self.popUp.close();

        if (self.selectedCategoryId == self.selectedCategoryIdBefore) {
            return;
        }

        AmazonTemplateDescriptionHandlerObj.resetCategory();
        self.specificHandler.resetFormDataSpecifics();

        if (self.preloadedSelectedCategoryObj) {
            AmazonTemplateDescriptionHandlerObj.setCategory(self.preloadedSelectedCategoryObj);
            self.saveRecentCategory();
        }
    },

    unSelectCategory: function()
    {
        var self = AmazonTemplateDescriptionCategoryChooserHandlerObj;

        self.selectedCategoryId           = null;
        self.preloadedSelectedCategoryObj = null;

        $('category_reset_link').hide();
        $('selected_category_path').innerHTML = '<span style="color: grey; font-style: italic">' + M2ePro.translator.translate('Not Selected') + '</span>';
    },

    // ---------------------------------------

    refreshAmazonCategories: function()
    {
        var self = AmazonTemplateDescriptionCategoryChooserHandlerObj;
        var win = window.open(M2ePro.url.get('adminhtml_common_marketplace/index'));

        var intervalId = setInterval(function() {

            if (!win.closed) {
                return;
            }

            clearInterval(intervalId);

            self.renderTopLevelCategories('chooser_browser');

            if ($('query').value.length != 0) {
                self.search();
            }

        }, 1000);
    },

    saveRecentCategory: function()
    {
        if (this.categoryNodeIdHiddenInput.value == '' || this.categoryPathHiddenInput.value == '') {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_template_description/saveRecentCategory'), {
            method     : 'post',
            parameters : {
                marketplace_id : $('marketplace_id').value,
                browsenode_id  : this.categoryNodeIdHiddenInput.value,
                category_path  : this.categoryPathHiddenInput.value
            }
        });
    },

    // ---------------------------------------

    search: function()
    {
        var self = AmazonTemplateDescriptionCategoryChooserHandlerObj;

        var query = $('query').value;
        if (query.length < 3) {
            return;
        }

        self.beforeSearch();

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_template_description/searchCategory'), {
            method: 'post',
            parameters: {
                marketplace_id: $('marketplace_id').value,
                query:          query.trim()
            },
            onSuccess: function(transport) {

                if (transport.responseText.length <= 2) {

                    $('search_results_no_results_tr').show();
                    $('search_results_update_sites_data_tr').show();
                    return;
                }

                var categories = transport.responseText.evalJSON();
                var resultsTable = $('search_results_table');

                categories.each(function(categoryInfo) {

                    var tr = new Element('tr', { class: 'search_results_results_tr' });
                    tr.appendChild(new Element('td').update(AmazonTemplateDescriptionHandlerObj.getInterfaceCategoryPath(categoryInfo, true)));

                    var link = new Element('a', {href: '#'}).update(M2ePro.translator.translate('Select'));
                    link.observe('click', function() {
                        var categoryPath = AmazonTemplateDescriptionHandlerObj.getInterfaceCategoryPath(categoryInfo);
                        self.searchClickCategory.call(self, categoryInfo.browsenode_id, categoryPath);
                    });

                    tr.appendChild(new Element('td')).appendChild(link);
                    resultsTable.down('tbody').appendChild(tr);
                });
            }
        });
    },

    beforeSearch: function()
    {
        var self = AmazonTemplateDescriptionCategoryChooserHandlerObj;

        $('search_results_no_results_tr').hide();
        $('search_results_update_sites_data_tr').hide();

        $$('.search_results_results_tr').invoke('remove');
    },

    searchReset: function()
    {
        var self = AmazonTemplateDescriptionCategoryChooserHandlerObj;

        self.beforeSearch();
        $('query').value = '';
        $('query').focus();
    },

    searchClickCategory: function(browseNodeId, categoryPath)
    {
        this.selectAndLoadCategory(browseNodeId, categoryPath);
    },

    keyPressQuery: function(event)
    {
        var self = AmazonTemplateDescriptionCategoryChooserHandlerObj;

        if (event.keyCode == 13) {
            self.search();
        }
    },

    // ---------------------------------------

    recentClickCategory: function(browseNodeId, categoryPath)
    {
        this.selectAndLoadCategory(browseNodeId, categoryPath);
    },

    // ---------------------------------------

    selectAndLoadCategory: function(browseNodeId, categoryPath)
    {
        var callback = function(transport) {

            var categoryInfo = transport.responseText.evalJSON();

            this.selectedCategoryId           = categoryInfo.category_id;
            this.preloadedSelectedCategoryObj = categoryInfo;

            this.updateCurrentlySelectedCategorySpan(categoryInfo);
        };

        this.getCategoryInfoFromDictionaryBrowseNodeId(browseNodeId, categoryPath, callback);
    },

    // ---------------------------------------

    getCategoryInfoFromDictionaryByCategoryId: function(categoryId, callback)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_template_description/getCategoryInfoByCategoryId'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                marketplace_id : $('marketplace_id').value,
                category_id    : categoryId
            },
            onSuccess: function(transport) {
                callback.call(self, transport);
            }
        });
    },

    getCategoryInfoFromDictionaryBrowseNodeId: function(browsenodeId, categoryPath, callback)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_template_description/getCategoryInfoByBrowseNodeId'), {
            method: 'post',
            asynchronous: true,
            parameters: {
                marketplace_id     : $('marketplace_id').value,
                browsenode_id      : browsenodeId,
                category_path      : categoryPath
            },
            onSuccess: function(transport) {
                callback.call(self, transport);
            }
        });
    },

    // ---------------------------------------

    updateCurrentlySelectedCategorySpan: function(categoryInfo)
    {
        var path = AmazonTemplateDescriptionHandlerObj.getInterfaceCategoryPath(categoryInfo, true);
        $('selected_category_path').innerHTML = path;

        $('category_reset_link').show();
    }

    // ---------------------------------------
});