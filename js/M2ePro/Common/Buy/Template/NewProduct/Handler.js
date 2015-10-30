CommonBuyTemplateNewProductHandler = Class.create();
CommonBuyTemplateNewProductHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    popups: [],

    // ---------------------------------------

    initialize: function(attributesHandler, attributeHandler)
    {
        // ugly hack
        if (version_compare(Prototype.Version,'1.7') < 0) {
            for (var property in Selector.xpath.operators) {
                Selector.xpath.operators[property] = Selector.xpath.operators[property].split('#{3}').join('#{4}');
            }
            Selector.patterns['attr'] = /\[\s*((?:[\w\u00c0-\uFFFF-]|\\.)+)\s*(?:(\S?=)\s*(['"]*)(.*?)\3|)\s*\](?![^\[]*\])(?![^\(]*\))/;
        }
        // ---------------------------------------

        var self = this;

        self.attributesHandler = attributesHandler;
        self.attributesHandler.categoryHandler = self;
        self.attributeHandler = attributeHandler;

        self.setValidationCheckRepetitionValue('M2ePro-new-sku-template-title',
                                                M2ePro.translator.translate('The specified Title is already used for another New SKU Policy.'),
                                                'Buy_Template_NewProduct', 'title', 'category_id',
                                                M2ePro.formData.category.category_id);

        self.initPopUp('search_category',850,550, M2ePro.translator.translate('Search For Category'));
        self.initPopUp('browse_category',600,500, M2ePro.translator.translate('Search For Category'));

        self.categoriesTr  = $('categories_tr');
        self.changeButton  = $('category_change_button_container');
        self.confirmButton = $('category_confirm_button_container');
        self.nodeTitleEl   = $('node_title');
        self.categoriesContainer = $('categories_container');

        self.searchCategoryButton = $('search_category_button');
        self.browseCategoryButton = $('browse_category_button');

        self.searchKeywordsInput = $('search_keywords');

        self.categoryPathHiddenInput = new Element('input', {
            'type': 'hidden',
            'name': "category[path]",
            'class': 'required-entry'
        });

        self.notes = [];
        self.notes.image  = [
            '- ' + M2ePro.translator.translate('Must be at least 500 pixels in width or height'),
            '- ' + M2ePro.translator.translate('No more than 5 megabytes (MB) in size'),
            '- ' + M2ePro.translator.translate('In the JPG/JPEG format'),
            '- ' + M2ePro.translator.translate('Only contain the Product being sold'),
            '- ' + M2ePro.translator.translate('Be in focus with realistic color with a pure white background'),
            '- ' + M2ePro.translator.translate('Cannot contain additional text, graphics or inset images')
        ];
        self.notes.description = [
            M2ePro.translator.translate('Text to describe the Product. Should be one block of text or a single paragraph. Do not use special characters and html tags')
        ];

        self.changeButton.insert({
            after: self.categoryPathHiddenInput
        });

        if (M2ePro.formData.category.category_path) {
            self.categoryPathHiddenInput.value = M2ePro.formData.category.category_path;
            self.showSpanWithCategoryPath(M2ePro.formData.category.category_path);
        }

        $('window_browse_category_close').observe(
            'click', function() { self.closeBrowseCategoryPopup(); }
        );

        $('window_search_category_close').observe(
            'click', function() { self.closeSearchCategoryPopup(); }
        );
    },

    // ---------------------------------------

    showSpanWithCategoryPath: function(path)
    {
        var spanEl = new Element('span', {'class': 'nobr','style': 'font-weight: bold'});
        this.changeButton.insert({'before': spanEl});
        spanEl.insert(path);
    },

    // ---------------------------------------

    checkAttributesReady: function()
    {
        if ($('rakuten_native_id').value == 0) {
            alert(M2ePro.translator.translate('Select Category first.'));
            buyTemplateNewProductEditTabsJsTabs.showTabContent($('buyTemplateNewProductEditTabs_general'));
        }
    },

    // ---------------------------------------

    rakuten_category_change: function()
    {
        if (this.value != "") {
            BuyTemplateNewProductHandlerObj.attributesHandler.showAttributes(this.value);
        }
    },

    node_title_change: function(element)
    {
        this.parentId = element.down('option[value="' + element.value + '"]').getAttribute('id_node_key');

        this.categoriesTr.show();
        this.categoriesContainer.show();
        this.categoriesContainer.update();
        this.removeSpanWithCategoryPath();
        this.changeButton.hide();
        this.confirmButton.hide();

        this.getCategories(element.down('option[value="' + element.value + '"]').getAttribute('node_id'),function(transport) {
            this.categories = transport.responseText.evalJSON();
            this.showCategories(this.categoriesContainer);
        });
    },

    // ---------------------------------------

    getCategories: function(nodeId,callback)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_common_buy_template_newProduct/getCategories'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                node_id: nodeId
            },
            onSuccess: function(transport) {
                callback.call(self,transport);
            }
        });
    },

    // ---------------------------------------

    showCategories: function(container)
    {
        var self       = this;
        var categories = [];
        self.categoryPathHiddenInput.value = '';
        self.confirmButton.hide();
        self.removeContainers(container.parentNode,container);
        var selectEl = container.appendChild(new Element('select'));
        selectEl.appendChild(new Element('option', {'value': 'empty','style': 'display: none'}));

        self.categories.each(function(category) {
            category.parent_category_id === self.parentId && categories.push(category)
        });

        if (categories.length == 0 || self.getCategoryInfo('category_id',self.parentId).is_leaf == 1) {
            self.confirmButton.show();
            if (categories.length == 0) {
                selectEl.hide();
                return '';
            }
        }

        categories.each(function(category) {
            selectEl.appendChild(new Element('option', {'value': category.category_id}))
                .update(category.title);
        });

        selectEl.observe('change',function(event) {
            self.parentId = this.value;
            self.showCategories(container.appendChild(new Element('div')));
        });
    },

    // ---------------------------------------

    getCategoryInfo: function(key,value)
    {
        var info = {};
        this.categories.each(function(category) {
            if (value == category[key]) {
                return info = category;
            }
        });
        return info;
    },

    removeContainers: function(container,doNotDeleteContainer)
    {
        container.childElements().each(function(child) {
            child.localName == 'div' && child != doNotDeleteContainer && container.removeChild(child)
        });
    },

    // ---------------------------------------

    confirmCategory: function()
    {
        var self = BuyTemplateNewProductHandlerObj;

        this.categoriesContainer.hide();
        this.confirmButton.hide();

        var categoryInfo = this.getCategoryInfo('category_id',this.parentId);
        var categoryPath = categoryInfo.path.replace(/->/g,' > ');

        this.categoryPathHiddenInput.value = categoryPath;

        this.changeButton.show();
        this.categoriesTr.hide();
        $$('input[name="category[node_title]"]').shift().value = this.nodeTitleEl.value;

        this.nodeTitleEl.value = '';

        this.browse_category.close();
        this.searchCategoryButton.parentNode.parentNode.hide();

        this.showSpanWithCategoryPath(categoryPath);

        // render Attributes
        $('rakuten_native_id').value = categoryInfo.native_id;
        self.attributesHandler.showAttributes(categoryInfo.native_id);
    },

    // ---------------------------------------

    changeCategory: function()
    {
        this.changeButton.hide();
        this.confirmButton.show();
        this.removeSpanWithCategoryPath();
        this.attributesHandler.showAttributes(0);
        this.categoriesContainer.show();
        this.searchCategoryButton.parentNode.parentNode.show();
        this.categoryPathHiddenInput.value = '';
        $('rakuten_native_id').value = '';
    },

    // ---------------------------------------

    removeSpanWithCategoryPath: function()
    {
        var span = this.changeButton.previous('span');
        span && span.remove();
    },

    // ---------------------------------------

    initPopUp: function(contentId,width,height,title)
    {
        this[contentId] = new Window({
            id: 'window_'+contentId,
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            top: 100,
            title: title,
            width: width,
            height: height,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        this[contentId].getContent().update($(contentId));
    },

    // ---------------------------------------

    searchClick: function()
    {
        $('error_block').hide();

        var keywords = this.searchKeywordsInput.value.trim();

        new Ajax.Request(M2ePro.url.get('adminhtml_common_buy_template_newProduct/searchCategory'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                keywords: keywords
            },
            onSuccess: (function(transport) {

                var response = transport.responseText;
                if (response.length == 0) {
                    this.resetSearchClick();
                    return $('error_block').show() && $('error_message').update(M2ePro.translator.translate('Internal Error. Please try again later.'));
                }
                if (response.isJSON() && response.evalJSON().result == 'error') {
                    this.resetSearchClick();
                    return $('error_block').show() && $('error_message').update(response.evalJSON().message);
                }
                $('reset_category_popup_button').show();
                $('searchGrid').update(response);

            }).bind(this)
        });
    },

    resetSearchClick: function()
    {
        var self = BuyTemplateNewProductHandlerObj;
        this.searchKeywordsInput.value = '';
        this.searchKeywordsInput.simulate('blur');
        $('searchGrid').update();
        $('reset_category_popup_button').hide();
    },

    confirmSearchClick: function(categoryInfo)
    {
        this.changeButton.show();
        this.categoriesContainer.hide();
        this.confirmButton.hide();

        var categoryPath = categoryInfo.path.replace(/->/g,' > ');

        this.categoryPathHiddenInput.value = categoryPath;

        this.categoriesTr.hide();

        $$('input[name="category[node_title]"]').shift().value = this.nodeTitleEl.select('option[node_id="'+ categoryInfo.node_id +'"]').shift().getAttribute('value');

        this.search_category.close();

        this.searchCategoryButton.parentNode.parentNode.hide();
        this.showSpanWithCategoryPath(categoryPath);

        // render Attributes
        $('rakuten_native_id').value = categoryInfo.native_id;
        BuyTemplateNewProductHandlerObj.attributesHandler.showAttributes(categoryInfo.native_id);
        this.resetSearchClick();
    },

    // ---------------------------------------

    gtin_mode_change: function()
    {
        this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::GTIN_MODE_NONE')
            ? $('gtin_custom_attribute_tr').hide()
            : $('gtin_custom_attribute_tr').show();
    },

    isbn_mode_change: function()
    {
        var self = BuyTemplateNewProductHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::ISBN_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('isbn_custom_attribute'));
        } else {
            $('isbn_custom_attribute').value = '';
        }
    },

    mfg_part_number_mode_change: function()
    {
        var self = BuyTemplateNewProductHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::MFG_PART_NUMBER_MODE_CUSTOM_VALUE')) {
            $('mfg_part_number_custom_value_tr').show();
            $('mfg_part_number_custom_attribute').value = '';
        } else {
            $('mfg_part_number_custom_value_tr').hide();
            self.updateHiddenValue(this, $('mfg_part_number_custom_attribute'));
        }
    },

    product_set_id_mode_change: function()
    {
        var self = BuyTemplateNewProductHandlerObj,
            customValueTr = $('product_set_id_custom_value_tr'),
            customAttribute = $('product_set_id_custom_attribute');

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::PRODUCT_SET_ID_MODE_NONE')) {
            customValueTr.hide();
            customAttribute.value = '';
        } else if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::PRODUCT_SET_ID_MODE_CUSTOM_VALUE')) {
            customValueTr.show();
            customAttribute.value = '';
        } else {
            customValueTr.hide();
            self.updateHiddenValue(this, customAttribute);
        }
    },

    // ---------------------------------------

    title_mode_change: function()
    {
        var self = BuyTemplateNewProductHandlerObj;
        var handlers = {};

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::TITLE_MODE_PRODUCT_NAME')] = function() {
            $('custom_title_tr').hide();
        };

        handlers[M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::TITLE_MODE_CUSTOM_TEMPLATE')] = function() {
            $('custom_title_tr').show();
        };

        if (this.value != "") {
            handlers[this.value].call(self);
        }
    },

    description_mode_change: function()
    {
        var self = BuyTemplateNewProductHandlerObj;

        $$('.c-custom_description_tr').invoke('hide');

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::DESCRIPTION_MODE_CUSTOM_TEMPLATE')) {
            $$('.c-custom_description_tr').invoke('show');
        }
    },

    closeBrowseCategoryPopup: function()
    {
        this.browse_category.close();
        this.categoriesTr.hide();
        this.nodeTitleEl.value = '';
        this.confirmButton.hide();
    },

    closeSearchCategoryPopup: function()
    {
        this.search_category.close();
        this.resetSearchClick();
    },

    main_image_mode_change: function()
    {
        var self = BuyTemplateNewProductHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::IMAGE_MAIN_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('main_image_attribute'));
        } else {
            $('main_image_attribute').value = '';
        }
    },

    additional_images_mode_change: function()
    {
        var self = BuyTemplateNewProductHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::ADDITIONAL_IMAGES_MODE_PRODUCT')) {
            self.updateHiddenValue(this, $('additional_images_limit'));
            $('additional_images_attribute').value = '';
        } else if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::ADDITIONAL_IMAGES_MODE_CUSTOM_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('additional_images_attribute'));
            $('additional_images_limit').value = '';
        }
    },

    keywords_mode_change: function()
    {
        var self = BuyTemplateNewProductHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::KEYWORDS_MODE_NONE')) {
            $('keywords_custom_value_tr').hide();
            $('keywords_custom_attribute').value = '';
        } else if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::KEYWORDS_MODE_CUSTOM_ATTRIBUTE')) {
            $('keywords_custom_value_tr').hide();
            self.updateHiddenValue(this, $('keywords_custom_attribute'));
        } else {
            $('keywords_custom_value_tr').show();
            $('keywords_custom_attribute').value = '';
        }
    },

    features_mode_change: function()
    {
        var self = BuyTemplateNewProductHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::FEATURES_MODE_NONE')) {
            $$('.features_tr').invoke('hide');
            $$('input[name="category[features_template][]"]').each(function(obj) {
                obj.value = '';
            });
            $('features_actions_tr').hide();
        } else {
            var visibleElementsCounter = 0;

            $$('.features_tr').each(function(obj) {
                if (visibleElementsCounter == 0 || $(obj).select('input[name="category[features_template][]"]')[0].value != '') {
                    if ($(obj).select('input[name="category[features_template][]"]')[0].value == '') {
                        $('show_features_action').addClassName('action-disabled');
                    }
                    $('show_features_action').removeClassName('action-disabled');
                    $(obj).show();
                    visibleElementsCounter++;
                }
            });

            var emptyVisibleFeaturesExist = $$('.features_tr').any(function(obj) {
                return $(obj).visible() && $(obj).select('input[name="category[features_template][]"]')[0].value == '';
            });
            $('features_actions_tr').show();

            if (visibleElementsCounter > M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::FEATURES_COUNT') - 1 || emptyVisibleFeaturesExist) {
                $('show_features_action').addClassName('action-disabled');
            }
        }
    },

    weight_mode_change:function()
    {
        var self = BuyTemplateNewProductHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::WEIGHT_MODE_CUSTOM_VALUE')) {
            $('weight_custom_value_tr').show();
            $('weight_custom_attribute').value = '';
        } else {
            $('weight_custom_value_tr').hide();
            self.updateHiddenValue(this, $('weight_custom_attribute'));
        }
    },

    // ---------------------------------------

    showFeature: function()
    {
        var emptyVisibleFeaturesExist = $$('.features_tr').any(function(obj) {
            return $(obj).visible() && $(obj).select('input[name="category[features_template][]"]')[0].value == '';
        });

        if (emptyVisibleFeaturesExist) {
            $('show_features_action').addClassName('action-disabled');
            return;
        }

        var hiddenFeatures = $$('.features_tr').findAll(function(obj) {
            return !$(obj).visible();
        });

        if (hiddenFeatures.size() == 0) {
            return;
        }

        hiddenFeatures.shift().show();
        $('hide_features_action').removeClassName('action-disabled');
        $('show_features_action').addClassName('action-disabled');
    },

    hideFeature: function()
    {
        var visibleElements = $$('.features_tr').findAll(Element.visible);

        if (visibleElements.size() == 1) {
            var featuresModeElement = $('features_mode');
            featuresModeElement.value = M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::FEATURES_MODE_NONE');
            featuresModeElement.simulate('change');
        }

        if (visibleElements.size() > 1) {
            var lastVisibleFeaure = visibleElements.pop();
            lastVisibleFeaure.select('input[name="category[features_template][]"]')[0].value = '';
            lastVisibleFeaure.hide();
        }

        var emptyVisibleFeaturesExist = $$('.features_tr').any(function(obj) {
            return $(obj).visible() && $(obj).select('input[name="category[features_template][]"]')[0].value == '';
        });

        if (visibleElements.size() != M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::FEATURES_COUNT') &&
            !emptyVisibleFeaturesExist) {

            $('show_features_action').removeClassName('action-disabled');
        }
    },

    allowAddFeature: function(elem)
    {
        var visibleElements= $$('.features_tr').findAll(Element.visible);
        var emptyVisibleFeaturesExist = $$('.features_tr').any(function(obj) {
            return $(obj).visible() && $(obj).select('input[name="category[features_template][]"]')[0].value == '';
        });

        if (visibleElements.size() != M2ePro.php.constant('Ess_M2ePro_Model_Buy_Template_NewProduct_Core::FEATURES_COUNT') &&
            !emptyVisibleFeaturesExist) {

            $('show_features_action').removeClassName('action-disabled');
        }
    },

    // ---------------------------------------

    renderHelpIcon: function(param, notes) {

        var self = this;
        var win;

        winContent = new Element('ul', {'style': 'text-align: left; margin-top: 10px'});

        notes.forEach(function(element, index) {
            winContent.insert('<li><p>' + element + '</p></li>');
        });

        $(param).observe('click',function() {

            var position = param.positionedOffset()

            win = win || new Window({
                className: "magento",
                title: M2ePro.translator.translate('All of your Product Images should meet the following Rules:'),
                width: 400,
                height: 180,
                zIndex: 100,
                top: position.top - 100,
                left: position.left + 100
            });

            win.setHTMLContent(winContent.outerHTML);

            if (win.visible) {
                win.hide();
            } else {
                self.popups.each(function(popup) {
                    popup.close();
                });
                win.show();
            }

            self.popups = [win];
        });
    }

    // ---------------------------------------
});