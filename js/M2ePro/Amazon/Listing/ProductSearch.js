window.AmazonListingProductSearch = Class.create(Action, {

    MATCHING_TYPE_EQUAL: 1,
    MATCHING_TYPE_VIRTUAL_AMAZON: 2,
    MATCHING_TYPE_VIRTUAL_MAGENTO: 3,

    searchData: {},

    // ---------------------------------------

    initialize: function($super,gridHandler)
    {
        var self = this;

        $super(gridHandler);

        self.searchBlock = $('productSearch_pop_up_content').outerHTML;
        $('productSearch_pop_up_content').remove();

        this.initValidators();
    },

    initMenuEvents: function()
    {
        $('productSearchMenu_cancel_button').observe('click', function() {
            popUp.close();
        });
    },

    initSearchEvents: function()
    {
        var self = this;

        $('productSearch_cancel_button').observe('click',function() {
            popUp.close();
        });

        $('productSearch_submit_button').observe('click',function(event) {
            self.searchGeneralIdManual(self.params.productId);
        });

        $('productSearch_reset_button').observe('click',function(event) {
            $('query').value = '';
            $('productSearch_grid').innerHTML = '';
        });

        $('productSearch_back_button').observe('click',function(event) {
            popUp.close();
            self.openPopUp(0, self.params.title, self.params.productId);
        });

        $('query').observe('keypress',function(event) {
            event.keyCode == Event.KEY_RETURN && self.searchGeneralIdManual(self.params.productId);
        });
    },

    // ---------------------------------------

    initValidators: function()
    {
        var self = this;

        Validation.add('M2ePro-amazon-attribute-unique-value', M2ePro.text.variation_manage_matched_attributes_error_duplicate, function(value, el) {

            var existedValues = [],
                isValid = true,
                form = el.up('form');

            form.select('select').each(function(el) {
                if (el.value != '') {
                    if(existedValues.indexOf(el.value) === -1) {
                        existedValues.push(el.value);
                    } else {
                        isValid = false;
                    }
                }
            });

            return isValid;
        });
    },

    // ---------------------------------------

    params: {autoMapErrorFlag: false},

    // ---------------------------------------

    openPopUp: function(mode, title, productId, errorMsg)
    {
        MessageObj.clearAll();

        var self = this;

        this.gridHandler.unselectAll();

        this.params = {
            mode: mode,
            title: title,
            productId: productId,
            size_menu: {
                width: 535,
                height: (typeof errorMsg == 'undefined') ? 340 : 400
            },
            size_main: {
                width: 750,
                height: 510
            },
            autoMapErrorFlag: false
        };

        popUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 70,
            width: (mode ==0) ? this.params.size_menu.width : this.params.size_main.width,
            height: (mode ==0) ? this.params.size_menu.height : this.params.size_main.height,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        popUp.options.destroyOnClose = true;

        if (mode == 0) {
            new Ajax.Request(M2ePro.url.getSearchAsinMenu, {
                method: 'post',
                parameters: {
                    product_id: productId
                },
                onSuccess: function(transport) {
                    $('modal_dialog_message').insert(transport.responseText);
                    self.initMenuEvents();
                    $('productSearchMenu_error_block').hide();
                    if (errorMsg != undefined) {
                        $('productSearchMenu_error_message').update(errorMsg);
                        $('productSearchMenu_error_block').show();
                    }

                    self.autoHeightFix();
                }
            });
        } else {
            $('modal_dialog_message').insert(self.searchBlock);
            $('productSearch_pop_up_content').show();
            $('productSearch_form').hide();
            $('productSearch_back_button').hide();
            $('productSearch_cleanSuggest_button').show();
            $('suggested_asin_grid_help_block').show();

            $('productSearch_buttons').show();
            new Ajax.Request(M2ePro.url.suggestedAsinGrid, {
                method: 'post',
                parameters: {
                    product_id: productId
                },
                onSuccess: function(transport) {
                    $('productSearch_grid').update(transport.responseText);
                    $('productSearch_cancel_button').observe('click',function() {
                        popUp.close();
                    });

                    self.autoHeightFix();
                }
            });
        };

    },

    // ---------------------------------------

    clearSearchResultsAndOpenSearchMenu: function() {
        var self = this;

        if (confirm(M2ePro.translator.translate('Are you sure?'))) {
            popUp.close();
            self.unmapFromGeneralId(self.params.productId, function() {
                self.openPopUp(0, self.params.title, self.params.productId);
            });
        }
    },

    // ---------------------------------------

    clearSearchResultsAndManualSearch: function() {
        var self = this;

        popUp.close();
        self.unmapFromGeneralId(self.params.productId, function() {
            self.showSearchManualPrompt(self.params.title, self.params.productId);
        });
    },

    // ---------------------------------------

    showSearchManualPrompt: function(title, listingProductId)
    {
        var self = this,
            title = title || self.params.title;

        if(typeof popUp != 'undefined') {
            popUp.close();
        }

        if (listingProductId) {
            this.params = {
                mode: 0,
                title: title,
                productId: listingProductId,
                size_menu: {
                    width: 535,
                    height: 400
                },
                size_main: {
                    width: 750,
                    height: 500
                },
                autoMapErrorFlag: false
            };
        }

        popUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 100,
            width: this.params.size_main.width,
            height: this.params.size_main.height,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        popUp.options.destroyOnClose = true;

        $('modal_dialog_message').insert(self.searchBlock);
        $('productSearch_pop_up_content').show();
        self.initSearchEvents();
        // search manual
        $('productSearch_form').show();
        $('productSearch_back_button').show();
        $('productSearch_buttons').show();
        $('productSearch_error_block').hide();
        $('productSearch_cleanSuggest_button').hide();
        $('suggested_asin_grid_help_block').hide();
        $('query').value = '';

        if (listingProductId) {
            $('productSearch_back_button').hide();
        }

        setTimeout(function() {$('query').focus();},250);

        self.autoHeightFix();
    },

    showSearchGeneralIdAutoPrompt: function()
    {
        if (confirm(M2ePro.translator.translate('Are you sure?'))) {
            popUp.close();
            this.searchGeneralIdAuto(this.params.productId);
        }
    },

    showUnmapFromGeneralIdPrompt: function(productId)
    {
        MessageObj.clearAll();
        var self = this;

        if (confirm(M2ePro.translator.translate('Are you sure?'))) {
            this.unmapFromGeneralId(productId);
        }
    },

    addNewGeneralId: function(listingProductIds)
    {
        var self = this;

        listingProductIds = listingProductIds || self.params.productId;

        new Ajax.Request(M2ePro.url.mapToNewAsin, {
            method: 'post',
            parameters: {
                products_ids: listingProductIds
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                if(typeof popUp != 'undefined') {
                    popUp.close();
                }

                self.gridHandler.unselectAllAndReload();
                self.flagSuccess = true;

                if(response.products_ids.length > 0) {
                    ListingGridObj.templateProductType.openPopUp(
                        0, M2ePro.text.productTypePopupTitle,
                        response.products_ids, null, response.data, 1
                    );
                } else {
                    if(response.messages.length > 0) {
                        MessageObj.clearAll();
                        response.messages.each(function(msg) {
                            MessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                        });
                    }
                }
            }
        });
    },

    // ---------------------------------------

    searchGeneralIdManual: function(productId)
    {
        var self = this;
        var query = $('query').value;

        MessageObj.clearAll();

        if (query == '') {
            $('query').focus();
            alert(M2ePro.text.enter_productSearch_query);
            return;
        }

        $('productSearch_error_block').hide();
        new Ajax.Request(M2ePro.url.searchAsinManual, {
            method: 'post',
            parameters: {
                query: query,
                product_id: productId
            },
            onSuccess: function(transport) {

                transport = transport.responseText.evalJSON();

                if(transport.result == 'success') {
                    $('productSearch_grid').update(transport.data);
                } else {
                    $('productSearch_error_message').update(transport.data);
                    $('productSearch_error_block').show();
                }
            }
        });
    },

    searchGeneralIdAuto: function(productIds)
    {
        MessageObj.clearAll();
        var self = this;

        var selectedProductsString =  productIds.toString();
        var selectedProductsArray = selectedProductsString.split(",");

        if (selectedProductsString == '' || selectedProductsArray.length == 0) {
            return;
        }

        var maxProductsInPart = 10;

        var result = new Array();
        for (var i=0;i<selectedProductsArray.length;i++) {
            if (result.length == 0 || result[result.length-1].length == maxProductsInPart) {
                result[result.length] = new Array();
            }
            result[result.length-1][result[result.length-1].length] = selectedProductsArray[i];
        }

        var selectedProductsParts = result;

        ListingProgressBarObj.reset();
        ListingProgressBarObj.show(M2ePro.text.automap_asin_progress_title);
        GridWrapperObj.lock();
        $('loading-mask').setStyle({visibility: 'hidden'});

        self.params.autoMapErrorFlag = false;

        self.sendPartsOfProducts(selectedProductsParts, selectedProductsParts.length, selectedProductsString);
    },

    sendPartsOfProducts: function(parts, totalPartsCount, selectedProductsString)
    {
        var self = this;

        if (parts.length == 0) {

            ListingProgressBarObj.setStatus(M2ePro.translator.translate('Task completed. Please wait ...'));

            GridWrapperObj.unlock();
            $('loading-mask').setStyle({visibility: 'visible'});

            self.gridHandler.unselectAllAndReload();

            if (self.params.autoMapErrorFlag == true) {
                MessageObj.addError(M2ePro.text.automap_error_message);
            }

            setTimeout(function() {
                ListingProgressBarObj.hide();
                ListingProgressBarObj.reset();
            }, 2000);

            new Ajax.Request(M2ePro.url.getProductsSearchStatus, {
                method: 'post',
                parameters: {
                    products_ids: selectedProductsString
                },
                onSuccess: function(transport) {
                    if (!transport.responseText.isJSON()) {
                        alert(transport.responseText);
                        return;
                    }

                    var response = transport.responseText.evalJSON();

                    if (response.messages) {
                        MessageObj.clearAll();
                        response.messages.each(function(msg) {
                            MessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                        });
                    }
                }
            });

            return;
        }

        var part = parts.splice(0,1);
        part = part[0];
        var partString = implode(',',part);

        var partExecuteString = part.length;
        partExecuteString += '';

        ListingProgressBarObj.setStatus(str_replace('%product_title%', partExecuteString, M2ePro.text.automap_asin_search_products));

        new Ajax.Request(M2ePro.url.searchAsinAuto, {
            method: 'post',
            parameters: {
                products_ids: partString
            },
            onSuccess: function(transport) {

                if (transport.responseText == 1) {
                    self.params.autoMapErrorFlag = true;
                }

                var percents = (100/totalPartsCount)*(totalPartsCount-parts.length);

                if (percents <= 0) {
                    ListingProgressBarObj.setPercents(0,0);
                } else if (percents >= 100) {
                    ListingProgressBarObj.setPercents(100,0);
                } else {
                    ListingProgressBarObj.setPercents(percents,1);
                }

                setTimeout(function() {
                    self.sendPartsOfProducts(parts, totalPartsCount, selectedProductsString);
                },500);
            }
        });
    },

    // ---------------------------------------

    mapToGeneralId: function(productId, generalId, optionsData)
    {
        var self = this;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        if(optionsData === undefined) {
            optionsData = '';
        }

        new Ajax.Request(M2ePro.url.mapToAsin, {
            method: 'post',
            parameters: {
                product_id: productId,
                general_id: generalId,
                options_data: decodeURIComponent(optionsData),
                search_type: $('amazon_asin_search_type').value,
                search_value: $('amazon_asin_search_value').value
            },
            onSuccess: function(transport) {
                if (transport.responseText.isJSON()) {
                    var response = transport.responseText.evalJSON();

                    if (response['vocabulary_attributes']) {
                        self.openVocabularyAttributesPopUp(response['vocabulary_attributes']);
                    }

                    if (response['vocabulary_attribute_options']) {
                        self.openVocabularyOptionsPopUp(response['vocabulary_attribute_options']);
                    }

                    self.gridHandler.unselectAllAndReload();
                    return;
                }

                if (transport.responseText == 0) {
                    self.gridHandler.unselectAllAndReload();
                } else {
                    alert(transport.responseText);
                }
            }
        });

        popUp.close();
    },

    openVocabularyAttributesPopUp: function (attributes)
    {
        var attributesHtml = '';
        $H(attributes).each(function(element) {
            attributesHtml += '<li>'+element.key+' > '+element.value+'</li>';
        });

        attributesHtml = '<ul>'+attributesHtml+'</ul>';
        var vocabularyPopUpHtml = str_replace('%attributes%', attributesHtml, $('vocabulary_attributes_pupup_template').innerHTML);

        vocabularyAttributesPopUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: 'Remember Attributes Accordance',
            top: 5,
            width: 400,
            height: 220,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        vocabularyAttributesPopUp.options.destroyOnClose = true;

        $('modal_dialog_message').update(vocabularyPopUpHtml);

        $('vocabulary_attributes_data').value = Object.toJSON(attributes);

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '630px';
        }, 50);
    },

    addAttributesToVocabulary: function(needAdd)
    {
        var self = this;

        var isRemember = $('vocabulary_attributes_remember_checkbox').checked;

        if (!needAdd && !isRemember) {
            Windows.getFocusedWindow().close();
            return;
        }

        new Ajax.Request(M2ePro.url.addAttributesToVocabulary, {
            method: 'post',
            parameters: {
                attributes : $('vocabulary_attributes_data').value,
                need_add:    needAdd ? 1 : 0,
                is_remember: isRemember ? 1 : 0
            },
            onSuccess: function (transport) {
                vocabularyAttributesPopUp.close();
            }
        });
    },

    openVocabularyOptionsPopUp: function (options)
    {
        vocabularyOptionsPopUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: 'Remember Options Accordance',
            top: 15,
            width: 400,
            height: 220,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });
        vocabularyOptionsPopUp.options.destroyOnClose = true;

        $('vocabulary_options_data').value = Object.toJSON(options);

        var optionsHtml = '';
        $H(options).each(function(element) {

            var valuesHtml = '';
            $H(element.value).each(function (value) {
                valuesHtml += value.key + ' > ' + value.value;
            });

            optionsHtml += '<li>'+element.key+': '+valuesHtml+'</li>';
        });

        optionsHtml = '<ul>'+optionsHtml+'</ul>';

        var bodyHtml = str_replace('%options%', optionsHtml, $('vocabulary_options_pupup_template').innerHTML);

        $('modal_dialog_message').update(bodyHtml);

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '500px';
        }, 50);
    },

    addOptionsToVocabulary: function(needAdd)
    {
        var self = this;

        var isRemember = $('vocabulary_options_remember_checkbox').checked;

        if (!needAdd && !isRemember) {
            Windows.getFocusedWindow().close();
            return;
        }

        new Ajax.Request(M2ePro.url.addOptionsToVocabulary, {
            method: 'post',
            parameters: {
                options_data : $('vocabulary_options_data').value,
                need_add:    needAdd ? 1 : 0,
                is_remember: isRemember ? 1 : 0
            },
            onSuccess: function (transport) {
                vocabularyOptionsPopUp.close();
            }
        });
    },

    unmapFromGeneralId: function(productIds, afterDoneFunction)
    {
        var self = this;

        this.gridHandler.unselectAll();

        self.flagSuccess = false;

        new Ajax.Request(M2ePro.url.unmapFromAsin, {
            method: 'post',
            parameters: {
                products_ids: productIds
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                self.gridHandler.unselectAllAndReload();
                self.flagSuccess = true;

                var response = transport.responseText.evalJSON();

                MessageObj.clearAll();
                MessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.message);
            },
            onComplete: function() {
                if (self.flagSuccess == true && afterDoneFunction != undefined) {
                    afterDoneFunction();
                }
            }
        });
    },

    // ---------------------------------------

    specificsChange: function(select)
    {
        var self = this;

        var idParts = explode('_', select.id);
        var id = idParts[2];
        var specifics = [];
        var selectedAsin = '';

        $(select.id) && self.hideEmptyOption($(select.id));

        self.validateSpecifics(id);

        var asins = JSON.parse(decodeHtmlentities($('asins_' + id).innerHTML));

        $('parent_asin_text_'+id).hide();
        $('map_link_error_icon_'+id).hide();

        $$('.specifics_' + id).each(function(el) {
            var specificName = explode('_', el.id);
            specificName = specificName[1];

            specifics[specificName] = el.value;
        });

        for (var spec in asins) {
            var productSpecifics = asins[spec].specifics;

            var found = true;
            for (var sName in productSpecifics) {

                if (productSpecifics[sName] != specifics[sName]) {
                    found = false;
                    break;
                }
            }

            if (found) {
                selectedAsin = spec;
                break;
            }
        }

        var asinLink = $('asin_link_' + id);

        if (selectedAsin === '') {
            $('map_link_error_icon_'+id).show();
            asinLink.innerHTML = $('parent_asin_' + id).innerHTML;
            asinLink.href = asinLink.href.slice(0, asinLink.href.lastIndexOf("/")) + '/' + $('parent_asin_' + id).innerHTML;
            $('parent_asin_text_'+id).show();
            return $('map_link_' + id).innerHTML = '<span style="color: #808080">' + M2ePro.translator.translate('Assign') + '</span>';
        }

        asinLink.innerHTML = selectedAsin;
        asinLink.href = asinLink.href.slice(0, asinLink.href.lastIndexOf("/")) + '/' + selectedAsin;

        var mapLinkTemplate = $('template_map_link_' + id).innerHTML;
        mapLinkTemplate = mapLinkTemplate.replace('%general_id%', selectedAsin);

        asins = addslashes(encodeURIComponent(JSON.stringify(asins)));

        mapLinkTemplate = mapLinkTemplate.replace('%options_data%', asins);
        $('map_link_' + id).innerHTML = mapLinkTemplate;
    },

    validateSpecifics: function(id, variations, i)
    {
        var variation = $H(variations || decodeHtmlentities($('channel_variations_tree_' + id).innerHTML).evalJSON()),
            attributes = $$('.specifics_name_' + id),
            options = $$('.specifics_' + id),
            index = i || 0;

        if (index === 0) {
            options.each(function(el) {
                el.disable();
            });
        }

        if (!attributes[index] || !options[index]) {
            return;
        }

        var attr = variation.keys()[0];

        var oldValue = decodeHtmlentities(options[index].value);
        options[index].update();
        options[index].enable();
        options[index].appendChild(new Element('option', {style: 'display: none'}));

        $H(variation.get(attr)).each(function(option) {
            options[index].appendChild(new Element('option', {value: option[0]})).insert(option[0]);

            if (option[0] == oldValue) {
                options[index].value = oldValue;
            }
        });

        if (oldValue) {
            index++;
            this.validateSpecifics(id, variation.get(attr)[oldValue], index);
        }
    },

    // ---------------------------------------

    attributesChange: function(select)
    {
        var self = this;

        var idParts = explode('_', select.id);
        var id = idParts[4];
        var optionsData = {
            matched_attributes: {},
            variations: null
        };

        $(select.id) && self.hideEmptyOption($(select.id));

        $('map_link_error_icon_'+id).hide();

        var existedValues = [];
        $$('.amazon_product_attribute_' + id).each(function(el) {
            var attributeNumber = explode('_', el.id);
            attributeNumber = attributeNumber[3];

            if(el.value != '' && existedValues.indexOf(el.value) === -1) {
                var magentoAttribute = $('magento_product_attribute_'+attributeNumber+'_'+id);
                optionsData.matched_attributes[magentoAttribute.value] = el.value;
                existedValues.push(el.value);
            } else {
                optionsData = '';
                throw $break;
            }
        });

        if (optionsData === '') {
            $('map_link_error_icon_'+id).show();
            return $('map_link_' + id).innerHTML = '<span style="color: #808080">' + M2ePro.translator.translate('Assign') + '</span>';
        }

        optionsData.variations = JSON.parse(decodeHtmlentities($('variations_' + id).innerHTML));
        optionsData = addslashes(encodeURIComponent(JSON.stringify(optionsData)));

        var mapLinkTemplate = $('template_map_link_' + id).innerHTML;
        mapLinkTemplate = mapLinkTemplate.replace('%options_data%', optionsData);
        $('map_link_' + id).innerHTML = mapLinkTemplate;
    },

    // ---------------------------------------

    renderMatchedAttributesVirtualView: function(id)
    {
        if (this.searchData[id].matchingType === this.MATCHING_TYPE_VIRTUAL_AMAZON) {
            this.renderMatchedAttributesVirtualAmazonView(id);
        }

        if (this.searchData[id].matchingType === this.MATCHING_TYPE_VIRTUAL_MAGENTO) {
            this.renderMatchedAttributesVirtualMagentoView(id);
        }
    },

    // ---------------------------------------

    renderMatchedAttributesVirtualAmazonView: function(id)
    {
        var self = this,
            form = $('matching_attributes_form_' + id),
            tHeader = form.down('.matching-attributes-table-header'),
            searchData = self.searchData[id];

        form.select('.matching-attributes-table-attribute-row').each(function(el){
            el.remove();
        });
        searchData.selectedDestinationAttributes = [];

        var prematchedAttributes = [];
        var i = 0;
        $H(searchData.matchedAttributes).each(function (attribute) {

            var tr = new Element('div', {
                    style: 'display: table-row',
                    class: 'matching-attributes-table-attribute-row'
                }),
                tdLabel = new Element('div', {
                    class: 'label',
                    style: 'display: table-cell; padding-bottom: 5px;'
                }),
                spanMagentoAttr = new Element('span', {
                    class: 'magento-attribute-name'
                }),
                inputVirtualAttribute = new Element('input', {
                    style: 'display: none',
                    value: attribute.key,
                    type: 'hidden',
                    disabled: 'disabled',
                    class: 'virtual-amazon-attribute-name-value',
                    name: 'virtual_amazon_attributes_'+i
                }),
                selectVirtualAttributeOption = new Element('select', {
                    style: 'display: none; width: 150px; font-size: 10px;',
                    disabled: 'disabled',
                    class: 'required-entry virtual-amazon-option',
                    name: 'virtual_amazon_option_'+i
                }),
                selectVirtualAttributeOptionGroup = new Element('optgroup', {
                    label: attribute.key
                }),
                spanVirtualAttributeAndOption = new Element('span', {
                    style: 'display: none',
                    class: 'virtual-amazon-attribute-and-option'
                }),
                spanLeftHelpIcon = new Element('span', {
                    style: 'display: none',
                    class: 'left-help-icon'
                }),
                tdValue = new Element('div', {
                    class: 'value',
                    style: 'display: table-cell; padding-bottom: 5px;'
                }),
                inputMagentoAttr = new Element('input', {
                    value: attribute.key,
                    type: 'hidden',
                    class: 'magento-attribute-name-value',
                    name: 'magento_attributes_'+i
                }),
                selectAmazonAttr = new Element('select', {
                    class: 'required-entry M2ePro-amazon-attribute-unique-value amazon-attribute-name',
                    name: 'amazon_attributes_'+i,
                    style: 'width: 170px; font-size: 10px;'
                }),
                spanVirtualAttribute = new Element('span', {
                    style: 'display: none',
                    class: 'virtual-amazon-attribute-name'
                }),
                spanRightHelpIcon = new Element('span', {
                    style: 'display: none',
                    class: 'right-help-icon'
                });

            var helpIconTpl = $('product_search_help_icon_tpl');

            spanLeftHelpIcon.update(helpIconTpl.innerHTML);
            spanLeftHelpIcon.down('.tool-tip-message-text').update(M2ePro.text.help_icon_magento_greater_left);
            spanRightHelpIcon.update(helpIconTpl.innerHTML);
            spanRightHelpIcon.down('.tool-tip-message-text').update(M2ePro.text.help_icon_magento_greater_right);

            var attributeStr = attribute.key;
            if (attributeStr.length > 16) {
                attributeStr = attribute.key.substr(0, 15) + '...';
                spanVirtualAttributeAndOption.title = attribute.key;
                spanVirtualAttribute.title = attribute.key;
            }

            if (attribute.key.length < 31) {
                spanMagentoAttr.update(attribute.key);
            } else {
                spanMagentoAttr.update(attribute.key.substr(0, 28) + '...');
                spanMagentoAttr.title = attribute.key;
            }

            spanVirtualAttribute.update(attributeStr+' (<span>&ndash;</span>)');
            spanVirtualAttributeAndOption.update(attributeStr+' (<a href="javascript:void(0);"></a>)');
            spanVirtualAttributeAndOption.down('a').title = '';

            spanVirtualAttributeAndOption.down('a').observe('click', function(event) {
                spanVirtualAttributeAndOption.hide();
                selectVirtualAttributeOption.show();
                selectVirtualAttributeOption.value = '';
                spanVirtualAttribute.down('span').update('&ndash;');
                spanVirtualAttribute.down('span').title = '';

                $('map_link_error_icon_'+id).show();
                $('map_link_' + id).innerHTML = '<span style="color: #808080">' + M2ePro.translator.translate('Assign') + '</span>';
            });

            var option = new Element('option', {
                value: ''
            });
            selectAmazonAttr.insert({bottom: option});

            searchData.destinationAttributes.each(function(destinationAttribute){
                var option = new Element('option', {
                    value: destinationAttribute
                });
                option.update(destinationAttribute);
                selectAmazonAttr.insert({bottom: option});

                if (attribute.value == destinationAttribute) {
                    selectAmazonAttr.value = destinationAttribute;
                    prematchedAttributes.push(selectAmazonAttr);
                }
            });
            selectAmazonAttr.prevValue = '';

            selectAmazonAttr.observe('change', function(event) {
                $('map_link_error_icon_'+id).show();
                $('map_link_' + id).innerHTML = '<span style="color: #808080">' + M2ePro.translator.translate('Assign') + '</span>';

                var result = true;
                if (selectAmazonAttr.value != '' && inputMagentoAttr.value != selectAmazonAttr.value &&
                    searchData.productAttributes.indexOf(selectAmazonAttr.value) !== -1) {
                    result = false;

                    if (attribute.value == null) {
                        alert(M2ePro.text.duplicate_amazon_attribute_error);
                    }
                    selectAmazonAttr.value = '';
                }
                attribute.value = null;

                var prevValueIndex = searchData.selectedDestinationAttributes.indexOf(selectAmazonAttr.prevValue);
                if (prevValueIndex > -1) {
                    searchData.selectedDestinationAttributes.splice(prevValueIndex, 1);
                }

                if (selectAmazonAttr.value != '') {
                    searchData.selectedDestinationAttributes.push(selectAmazonAttr.value);
                }
                selectAmazonAttr.prevValue = selectAmazonAttr.value;

                form.select('select').each(function(el){
                    result = Validation.get('M2ePro-amazon-attribute-unique-value').test($F(el), el) ? result : false;
                });

                if (result && searchData.selectedDestinationAttributes.length == searchData.destinationAttributes.length) {
                    self.showVirtualAmazonAttributes(id);
                } else {
                    self.hideVirtualAmazonAttributes(id);
                }
            });

            selectVirtualAttributeOption.observe('change', function(event) {
                var value = selectVirtualAttributeOption.value;

                spanVirtualAttributeAndOption.show();
                selectVirtualAttributeOption.hide();

                if (attributeStr.length + value.length < 28) {
                    spanVirtualAttribute.down('span').update(value);
                    spanVirtualAttribute.down('span').title = '';
                    spanVirtualAttributeAndOption.down('a').update(value);
                } else {
                    spanVirtualAttribute.down('span').update(value.substr(0, 22 - attributeStr.length) + '...');
                    spanVirtualAttribute.down('span').title = value;
                    spanVirtualAttributeAndOption.down('a').update(value.substr(0, 22 - attributeStr.length) + '...');
                }

                spanVirtualAttributeAndOption.down('a').title = M2ePro.text.change_option + ' "' + value + '"';

                var result = true;
                form.select('select').each(function(el){
                    if (Validation.isVisible(el)) {
                        el.classNames().each(function (className) {
                            var v = Validation.get(className),
                                validationResult = v.test($F(el), el);

                            result = validationResult ? result : false;

                            if (!validationResult) {
                                throw $break;
                            }
                        });
                    }
                });

                if (result) {
                    $('map_link_error_icon_'+id).hide();

                    var data = {};
                    data.virtual_matched_attributes = form.serialize(true);
                    data.variations = searchData.amazonVariation;
                    data = addslashes(encodeURIComponent(JSON.stringify(data)));

                    var mapLinkTemplate = $('template_map_link_' + id).innerHTML;
                    mapLinkTemplate = mapLinkTemplate.replace('%options_data%', data);
                    $('map_link_' + id).innerHTML = mapLinkTemplate;
                } else {
                    $('map_link_error_icon_'+id).show();
                    $('map_link_' + id).innerHTML = '<span style="color: #808080">' + M2ePro.translator.translate('Assign') + '</span>';
                }
            });

            var option = new Element('option', {
                value: ''
            });
            selectVirtualAttributeOption.insert({bottom: option});

            searchData.magentoVariationSet[attribute.key].each(function(optionValue){
                var option = new Element('option', {
                    value: optionValue
                });
                option.update(optionValue);
                selectVirtualAttributeOptionGroup.insert({bottom: option});
            });
            selectVirtualAttributeOption.insert({bottom: selectVirtualAttributeOptionGroup});

            tdLabel.insert({ bottom: spanMagentoAttr });
            tdLabel.insert({ bottom: inputVirtualAttribute });
            tdLabel.insert({ bottom: selectVirtualAttributeOption });
            tdLabel.insert({ bottom: spanVirtualAttributeAndOption });
            tdLabel.insert({ bottom: spanLeftHelpIcon });
            tdValue.insert({ bottom: inputMagentoAttr });
            tdValue.insert({ bottom: selectAmazonAttr });
            tdValue.insert({ bottom: spanVirtualAttribute });
            tdValue.insert({ bottom: spanRightHelpIcon });

            tr.insert({ bottom: tdLabel });
            tr.insert({ bottom: tdValue });

            tHeader.insert({ after: tr });

            i++;
        });

        form.select('.tool-tip-image').each(function(element) {
            element.observe('mouseover', MagentoFieldTipObj.showToolTip);
            element.observe('mouseout', MagentoFieldTipObj.onToolTipIconMouseLeave);
        });

        form.select('.tool-tip-message').each(function(element) {
            element.observe('mouseout', MagentoFieldTipObj.onToolTipMouseLeave);
            element.observe('mouseover', MagentoFieldTipObj.onToolTipMouseEnter);
        });

        prematchedAttributes.each(function(el){
            el.simulate('change');
        });
    },

    showVirtualAmazonAttributes: function(id)
    {
        var self = this,
            form = $('matching_attributes_form_' + id);

        var virtualAmazonAttr = form.select('select.amazon-attribute-name[value=""]');
        virtualAmazonAttr.each(function(el){
            el.disable().hide();

            var tr = el.up('.matching-attributes-table-attribute-row');
            tr.down('.magento-attribute-name-value').disable();
            tr.down('.virtual-amazon-attribute-name').show();
            tr.down('.magento-attribute-name').hide();
            tr.down('.virtual-amazon-attribute-name-value').enable();
            tr.down('.virtual-amazon-option').enable().show();
            tr.down('.right-help-icon').show();
            tr.down('.left-help-icon').show();
        });
    },

    hideVirtualAmazonAttributes: function(id)
    {
        var self = this,
            form = $('matching_attributes_form_' + id);

        var virtualAmazonAttr = form.select('select.amazon-attribute-name[value=""]');
        virtualAmazonAttr.each(function(el){
            el.enable().show();

            var tr = el.up('.matching-attributes-table-attribute-row');
            tr.down('.magento-attribute-name-value').enable();
            tr.down('.virtual-amazon-attribute-name').hide();
            tr.down('.magento-attribute-name').show();
            tr.down('.virtual-amazon-attribute-name-value').disable();
            tr.down('.virtual-amazon-option').disable().hide();
            tr.down('.virtual-amazon-attribute-and-option').hide();
            tr.down('.right-help-icon').hide();
            tr.down('.left-help-icon').hide();
        });
    },

    // ---------------------------------------

    renderMatchedAttributesVirtualMagentoView: function(id)
    {
        var self = this,
            form = $('matching_attributes_form_' + id),
            tHeader = form.down('.matching-attributes-table-header'),
            searchData = self.searchData[id];

        form.select('.matching-attributes-table-attribute-row').each(function(el){
            el.remove();
        });

        var prematchedAttributes = [];
        var i = 0;
        $H(searchData.matchedAttributes).each(function (attribute) {

            var tr = new Element('div', {
                    style: 'display: table-row',
                    class: 'matching-attributes-table-attribute-row'
                }),
                tdLabel = new Element('div', {
                    class: 'label',
                    style: 'display: table-cell; padding-bottom: 5px;'
                }),
                spanMagentoAttr = new Element('span'),
                tdValue = new Element('div', {
                    class: 'value',
                    style: 'display: table-cell; padding-bottom: 5px;'
                }),
                inputMagentoAttr = new Element('input', {
                    value: attribute.key,
                    type: 'hidden',
                    name: 'magento_attributes_'+i
                }),
                selectAmazonAttr = new Element('select', {
                    class: 'required-entry M2ePro-amazon-attribute-unique-value amazon-attribute-name',
                    name: 'amazon_attributes_'+i,
                    style: 'width: 170px; font-size: 10px;'
                });

            if (attribute.key.length < 31) {
                spanMagentoAttr.update(attribute.key);
            } else {
                spanMagentoAttr.update(attribute.key.substr(0, 28) + '...');
                spanMagentoAttr.title = attribute.key;
            }

            var option = new Element('option', {
                value: ''
            });
            selectAmazonAttr.insert({bottom: option});

            searchData.destinationAttributes.each(function(destinationAttribute){
                var option = new Element('option', {
                    value: destinationAttribute
                });
                option.update(destinationAttribute);
                selectAmazonAttr.insert({bottom: option});

                if (attribute.value == destinationAttribute) {
                    selectAmazonAttr.value = destinationAttribute;
                    prematchedAttributes.push(selectAmazonAttr);
                }
            });
            selectAmazonAttr.prevValue = '';

            selectAmazonAttr.observe('change', function(event) {
                $('map_link_error_icon_'+id).show();
                $('map_link_' + id).innerHTML = '<span style="color: #808080">' + M2ePro.translator.translate('Assign') + '</span>';

                var result = true;
                if (selectAmazonAttr.value != '' && inputMagentoAttr.value != selectAmazonAttr.value &&
                    searchData.destinationAttributes.indexOf(inputMagentoAttr.value) !== -1) {
                    result = false;

                    if (attribute.value == null) {
                        alert(M2ePro.text.duplicate_magento_attribute_error);
                    }
                    selectAmazonAttr.value = '';
                }
                attribute.value = null;

                form.select('select.amazon-attribute-name').each(function(el){
                    el.classNames().each(function (className) {
                        var v = Validation.get(className),
                            validationResult = v.test($F(el), el);

                        result = validationResult ? result : false;

                        if (!validationResult) {
                            throw $break;
                        }
                    });
                });

                if (result) {
                    self.showVirtualMagentoAttributes(id,i);
                } else {
                    self.hideVirtualMagentoAttributes(id);
                }
            });

            tdLabel.insert({ bottom: spanMagentoAttr });
            tdValue.insert({ bottom: inputMagentoAttr });
            tdValue.insert({ bottom: selectAmazonAttr });

            tr.insert({ bottom: tdLabel });
            tr.insert({ bottom: tdValue });

            tHeader.insert({ after: tr });

            i++;
        });

        prematchedAttributes.each(function(el){
            el.simulate('change');
        });
    },

    showVirtualMagentoAttributes: function(id, lastAttributeIndex)
    {
        var self = this,
            form = $('matching_attributes_form_' + id),
            tBody = form.down('.matching-attributes-table'),
            searchData = self.searchData[id];

        form.select('div.virtual-attribute').each(function(el){
            el.remove();
        });

        var selectedValues = [];
        form.select('select.amazon-attribute-name').each(function(el){
            selectedValues.push(el.value);
        });

        var i = lastAttributeIndex;
        searchData.destinationAttributes.each(function(attribute){
            if (selectedValues.indexOf(attribute) !== -1) {
                return true;
            }
            var tr = new Element('div', {
                    style: 'display: table-row',
                    class: 'matching-attributes-table-attribute-row virtual-attribute'
                }),
                tdLabel = new Element('div', {
                    class: 'label',
                    style: 'display: table-cell; padding-bottom: 5px;'
                }),
                spanMagentoAttr = new Element('span'),
                spanLeftHelpIcon = new Element('span', {
                    class: 'left-help-icon'
                }),
                tdValue = new Element('div', {
                    class: 'value',
                    style: 'display: table-cell; padding-bottom: 5px;'
                }),
                inputMagentoAttr = new Element('input', {
                    value: attribute,
                    type: 'hidden',
                    name: 'virtual_magento_attributes_'+i
                }),
                spanVirtualAttribute = new Element('span', {
                    style: 'display: none'
                }),
                selectVirtualAttrOption = new Element('select', {
                    style: 'width: 150px; font-size: 10px;',
                    class: 'required-entry virtual-magento-option',
                    name: 'virtual_magento_option_'+i
                }),
                virtualAttrOptionGroup = new Element('optgroup', {
                    label: attribute
                }),
                spanRightHelpIcon = new Element('span', {
                    class: 'right-help-icon'
                });

            var helpIconTpl = $('product_search_help_icon_tpl');

            spanLeftHelpIcon.update(helpIconTpl.innerHTML);
            spanLeftHelpIcon.down('.tool-tip-message-text').update(M2ePro.text.help_icon_amazon_greater_left);
            spanRightHelpIcon.update(helpIconTpl.innerHTML);
            spanRightHelpIcon.down('.tool-tip-message-text').update(M2ePro.text.help_icon_amazon_greater_right);

            var attributeStr = attribute;
            if (attributeStr.length > 13) {
                attributeStr = attribute.substr(0, 12) + '...';
                spanMagentoAttr.title = attribute;
                spanVirtualAttribute.title = attribute;
            }

            spanMagentoAttr.update(attributeStr+' (<span>&ndash;</span>)');
            spanVirtualAttribute.update(attributeStr+' (<a href="javascript:void(0);"></a>)');
            spanVirtualAttribute.down('a').title = '';

            spanVirtualAttribute.down('a').observe('click', function(event) {
                spanVirtualAttribute.hide();
                selectVirtualAttrOption.show();
                selectVirtualAttrOption.value = '';
                spanMagentoAttr.down('span').update('&ndash;');
                spanMagentoAttr.down('span').title = '';

                $('map_link_error_icon_'+id).show();
                $('map_link_' + id).innerHTML = '<span style="color: #808080">' + M2ePro.translator.translate('Assign') + '</span>';
            });

            var option = new Element('option', {
                value: ''
            });
            selectVirtualAttrOption.insert({bottom: option});

            searchData.amazonVariation.set[attribute].each(function(optionValue){
                var option = new Element('option', {
                    value: optionValue
                });
                option.update(optionValue);
                virtualAttrOptionGroup.insert({bottom: option});
            });
            selectVirtualAttrOption.insert({bottom: virtualAttrOptionGroup});

            selectVirtualAttrOption.observe('change', function(event) {
                var value = selectVirtualAttrOption.value;

                spanVirtualAttribute.show();
                selectVirtualAttrOption.hide();

                if (attributeStr.length + value.length < 28) {
                    spanMagentoAttr.down('span').update(value);
                    spanMagentoAttr.down('span').title = '';
                    spanVirtualAttribute.down('a').update(value);
                } else {
                    spanMagentoAttr.down('span').update(value.substr(0, 27 - attributeStr.length) + '...');
                    spanMagentoAttr.down('span').title = value;
                    spanVirtualAttribute.down('a').update(value.substr(0, 27 - attributeStr.length) + '...');
                }

                spanVirtualAttribute.down('a').title = M2ePro.text.change_option + ' "' + value + '"';

                var result = true;
                form.select('select').each(function(el){
                    el.classNames().each(function (className) {
                        var v = Validation.get(className),
                            validationResult = v.test($F(el), el);

                        result = validationResult ? result : false;

                        if (!validationResult) {
                            throw $break;
                        }
                    });
                });

                if (result) {
                    $('map_link_error_icon_'+id).hide();

                    var data = {};
                    data.virtual_matched_attributes = form.serialize(true);
                    data.variations = searchData.amazonVariation;
                    data = addslashes(encodeURIComponent(JSON.stringify(data)));

                    var mapLinkTemplate = $('template_map_link_' + id).innerHTML;
                    mapLinkTemplate = mapLinkTemplate.replace('%options_data%', data);
                    $('map_link_' + id).innerHTML = mapLinkTemplate;
                } else {
                    $('map_link_error_icon_'+id).show();
                    $('map_link_' + id).innerHTML = '<span style="color: #808080">' + M2ePro.translator.translate('Assign') + '</span>';
                }
            });

            tdLabel.insert({bottom: spanMagentoAttr});
            tdLabel.insert({bottom: spanLeftHelpIcon});
            tdValue.insert({bottom: inputMagentoAttr});
            tdValue.insert({bottom: spanVirtualAttribute});
            tdValue.insert({bottom: selectVirtualAttrOption});
            tdValue.insert({bottom: spanRightHelpIcon});

            tr.insert({bottom: tdLabel});
            tr.insert({bottom: tdValue});

            tBody.insert({ bottom: tr });

            i++;
        });

        tBody.select('.tool-tip-image').each(function(element) {
            element.observe('mouseover', MagentoFieldTipObj.showToolTip);
            element.observe('mouseout', MagentoFieldTipObj.onToolTipIconMouseLeave);
        });

        tBody.select('.tool-tip-message').each(function(element) {
            element.observe('mouseout', MagentoFieldTipObj.onToolTipMouseLeave);
            element.observe('mouseover', MagentoFieldTipObj.onToolTipMouseEnter);
        });
    },

    hideVirtualMagentoAttributes: function(id)
    {
        var self = this,
            form = $('matching_attributes_form_' + id);

        form.select('div.virtual-attribute').each(function(el){
            el.remove();
        });
    }

    // ---------------------------------------
});
