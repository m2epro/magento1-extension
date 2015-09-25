CommonListingProductVariationHandler = Class.create();
CommonListingProductVariationHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(M2ePro,gridHandler)
    {
        this.M2ePro = M2ePro;
        this.gridHandler = gridHandler;
    },

    setListingProductId: function(listingProductId)
    {
        this.listingProductId = listingProductId;
        return this;
    },

    setNeededVariationData: function(variationAttributes, variationsTree)
    {
        this.variationAttributes = variationAttributes;
        this.variationsTree = variationsTree;

        return this;
    },

    //###############################################

    showEditPopup: function(popupTitle)
    {
        var self = this;

        MagentoMessageObj.clearAll();

        new Ajax.Request(this.M2ePro.url.get_variation_edit_popup, {
            method: 'get',
            parameters: {
                component: this.M2ePro.customData.componentMode,
                listing_product_id: this.listingProductId
            },
            onSuccess: (function(transport) {

                try {
                    var response = transport.responseText.evalJSON();

                    if (response.type == 'error') {
                        MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.message);
                        return;
                    }

                    this.editPopup = Dialog.info(null, {
                        draggable: true,
                        resizable: true,
                        closable: true,
                        className: "magento",
                        windowClassName: "popup-window",
                        title: popupTitle,
                        width: 600,
                        height: 405,
                        zIndex: 100,
                        hideEffect: Element.hide,
                        showEffect: Element.show
                    });

                    $('modal_dialog_message').insert(response.text);

                    self.autoHeightFix();

                } catch (e) {
                    this.editPopup.close();
                    MagentoMessageObj.addError('Internal Error.');
                }
            }).bind(this)
        });
    },

    //----------------------------------

    editPopupInit: function(currentVariation)
    {
        var container = $('variation_edit_container');

        var filters = {};

        this.variationAttributes.each((function(attribute,i) {

            var tr = container.appendChild(new Element('tr'));
            tr.appendChild(new Element('td', {class: 'label'}))
              .insert(attribute + ': <span class="required">*</span>');

            var select = tr
                .appendChild(new Element('td', {class: 'value'}))
                .appendChild(new Element('select', {
                    name: 'variation_data[' + attribute + ']',
                    class:'required-entry',
                    index: i
                }));

            select
                .appendChild(new Element('option', {value: currentVariation[attribute]}))
                .insert(currentVariation[attribute]);

            this.eachAttributeHandler(
                select,
                i,
                function() {
                    return container.select('select[index]').filter(function(select) {
                        return select.readAttribute('index') > i;
                    });
                },
                filters
            );

        }).bind(this));

        container.select('select[index]').each(function(select) {
            select.simulate('change');
        });

        var variationForm = new varienForm('variation_edit_form','');

        $('variation_edit_confirm').observe('click',(function() {
            if (!variationForm.validate()) {
                return;
            }

            var variationData = {};
            Form.getElements($(variationForm.formId)).each(function(selectElement) {
                var attribute = selectElement.readAttribute('name');
                selectElement.value && (variationData[attribute] = selectElement.value);
            });

            this.editAction(variationData);
        }).bind(this));
    },

    //----------------------------------

    resetListingProductVariation: function()
    {
        MagentoMessageObj.clearAll();

        new Ajax.Request(this.M2ePro.url.variation_reset_action, {
            method: 'get',
            parameters: {
                component: this.M2ePro.customData.componentMode,
                listing_product_id: this.listingProductId
            },
            onSuccess: (function(transport) {

                try {
                    var response = transport.responseText.evalJSON();

                    MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.message);

                    this.gridHandler.unselectAllAndReload();
                } catch (e) {
                    this.editPopup.close();
                    MagentoMessageObj.addError('Internal Error.');
                }
            }).bind(this)
        });
    },

    //###############################################

    showSwitchToIndividualModePopUp: function(title)
    {
        var self = this;

        self.switchToIndividualModePopUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.text.switch_to_individual_mode_popup_title,
            width: 530,
            height: 200,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        self.switchToIndividualModePopUp.options.destroyOnClose = true;

        $('modal_dialog_message').insert($('switch_to_individual_popup').innerHTML);

        $('modal_dialog_message').select('.switch-to-individual-btn')[0].observe('click', function() {

            if ($('switch_to_individual_remember_checkbox').checked) {
                new Ajax.Request(self.M2ePro.url.save_listing_additional_data, {
                    method: 'post',
                    parameters: {
                        param_name: 'hide_switch_to_individual_confirm',
                        param_value: 1
                    },
                    onSuccess: function(transport) {
                        self.gridHandler.unselectAllAndReload();
                    }
                });
            }

            self.switchToIndividualModePopUp.close();
            self.showManagePopup(title);
        });

        $('modal_dialog_message').select('.switch-to-individual-popup-close')[0].observe('click', function() {
            self.switchToIndividualModePopUp.close();
        });
    },

    showSwitchToParentModePopUp: function()
    {
        var self = this;

        self.switchToParentModePopUp = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.text.switch_to_parent_mode_popup_title,
            width: 530,
            height: 200,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        self.switchToParentModePopUp.options.destroyOnClose = true;

        $('modal_dialog_message').insert($('switch_to_parent_popup').innerHTML);

        $('modal_dialog_message').select('.switch-to-parent-btn')[0].observe('click', function() {

            if ($('switch_to_parent_remember_checkbox').checked) {
                new Ajax.Request(self.M2ePro.url.save_listing_additional_data, {
                    method: 'post',
                    parameters: {
                        param_name: 'hide_switch_to_parent_confirm',
                        param_value: 1
                    }
                });
            }

            self.switchToParentModePopUp.close();
            self.resetListingProductVariation();
        });

        $('modal_dialog_message').select('.switch-to-parent-popup-close')[0].observe('click', function() {
            self.switchToParentModePopUp.close();
        });
    },

    //###############################################

    showManagePopup: function(popupTitle)
    {
        var self = this;

        MagentoMessageObj.clearAll();

        new Ajax.Request(this.M2ePro.url.get_variation_manage_popup, {
            method: 'get',
            parameters: {
                component: this.M2ePro.customData.componentMode,
                listing_product_id: this.listingProductId
            },
            onSuccess: (function(transport) {

                try {
                    var response = transport.responseText.evalJSON();

                    if (response.type == 'error') {
                        MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.message);
                        return;
                    }

                    this.managePopup = Dialog.info(null, {
                        draggable: true,
                        resizable: true,
                        closable: true,
                        className: "magento",
                        windowClassName: "popup-window",
                        title: popupTitle,
                        width: 700,
                        height: 460,
                        zIndex: 100,
                        hideEffect: Element.hide,
                        showEffect: Element.show
                    });

                    $('modal_dialog_message').insert(response.text);

                    self.autoHeightFix();

                } catch (e) {
                    this.managePopup.close();
                    MagentoMessageObj.addError('Internal Error.');
                }
            }).bind(this)
        });
    },

    //-----------------------------------------------

    managePopupInit: function()
    {
        var variationForm = new varienForm('variation_manage_form','');

        $('add_more_variation_button')
            .observe('click',(function() {
                if (!variationForm.validate()) {
                    return;
                }
                this.manageAddRow();
            }).bind(this))
            .simulate('click');

        $('variation_manage_confirm').observe('click',(function() {
            if (!variationForm.validate()) {
                return;
            }

            var variationData = {};
            Form.getElements($(variationForm.formId)).each(function(selectElement) {
                var attribute = selectElement.readAttribute('name');
                selectElement.value && (variationData[attribute] = selectElement.value);
            });

            this.manageAction(variationData);
        }).bind(this));
    },

    //-----------------------------------------------

    manageAddRow: function()
    {
        var container = $('variation_manage_tbody');

        var lastTr = container.select('tr').last();
        var index = lastTr ? parseInt(lastTr.readAttribute('index')) + 1 : 1;

        var tr = container.appendChild(new Element('tr', {index: index}));

        var filters = {};

        this.variationAttributes.each((function(attribute,i) {

            var select = tr
                .appendChild(new Element('td', {style: 'vertical-align: top; padding: 2px 4px'}))
                .appendChild(new Element('select', {
                    name: 'variation_data['+index+'][' + attribute + ']',
                    class:'required-entry',
                    style: 'width: 100%',
                    index: i,
                    disabled: true
                }));

            this.eachAttributeHandler(
                select,
                i,
                function() {
                    return tr.select('select[index]').filter(function(select) {
                        return select.readAttribute('index') > i;
                    });
                },
                filters
            );

        }).bind(this));

        tr.appendChild(new Element('td', {style: 'vertical-align: top; padding: 2px 4px'}))
            .appendChild(new Element('button', {type:'button',class: 'scalable delete'})).insert('<span></span>')
            .observe('click', function() {
                if (container.select('tr').length > 1) {
                    tr.remove();
                }

                if (container.select('tr').length == 1) {
                    container.select('button.delete').each(function(btn){
                        btn.hide();
                    });
                }
            });

        container.select('button.delete').each(function(btn){
            btn.hide();
        });

        if (container.select('tr').length > 1) {
            container.select('button.delete').each(function(btn){
                btn.show();
            });
        }
    },

    //###############################################

    eachAttributeHandler: function(select,i,getNextSelects,filters)
    {
        var attribute = this.variationAttributes[i];

        if (!i) {
            select.disabled = false;
            this.renderAttributeValues(select,attribute);
        }

        select.observe('change', (function() {
            filters[attribute] = select.value;

            var nextSelects = getNextSelects.call(this);

            if (nextSelects.length < 1) {
                return;
            }

            nextSelects.each(function(select) {
                select.disabled = true;
            });

            var nextSelect = nextSelects[0];

            nextSelect.disabled = false;

            this.renderAttributeValues(
                nextSelect, this.variationAttributes[i+1],filters
            );
        }).bind(this));
    },

    //-----------------------------------------------

    renderAttributeValues: function(container,attribute,filters)
    {
        filters = filters || {};

        var values = this.getAttributeValues(attribute,this.variationsTree,filters);

        var oldValue = container.value;
        container.update();

        container.appendChild(new Element('option', {style: 'display: none'}));

        if(typeof values != 'undefined') {

            values.each(function(value) {
                container.appendChild(new Element('option', {value: value})).insert(value);

                if (value == oldValue) {
                    container.value = oldValue;
                    container.simulate('change');
                }
            });
        }
    },

    //----------------------------------

    getAttributeValues: function(attribute,attributesTree,filters)
    {
        for (var treeAttribute in attributesTree) {

            if (attribute == treeAttribute) {

                var values = [];
                for (var value in attributesTree[treeAttribute]) {
                    value && values.push(value);
                }

                return values;
            }

            for (var filterAttribute in filters) {

                if (filterAttribute == treeAttribute) {
                    return this.getAttributeValues(
                        attribute,
                        attributesTree[treeAttribute][filters[filterAttribute]],
                        filters
                    )
                }
            }
        }
    },

    //###############################################

    editAction: function(variationData)
    {
        MagentoMessageObj.clearAll();

        var parameters = Object.extend(
            {
                component: this.M2ePro.customData.componentMode,
                listing_product_id: this.listingProductId
            },
            variationData
        );

        new Ajax.Request(this.M2ePro.url.variation_edit_action, {
            method: 'post',
            parameters: parameters,
            onSuccess: (function(transport) {

                try {
                    this.editPopup.close();

                    var response = transport.responseText.evalJSON();

                    MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.message);

                    if (response.type == 'error') {
                        this.scroll_page_to_top();
                    } else {
                        this.gridHandler.unselectAllAndReload();
                    }

                } catch (e) {
                    console.log(e.stack);
                    this.scroll_page_to_top();
                    this.editPopup.close();
                    MagentoMessageObj.addError('Internal Error.');
                }
            }).bind(this)
        });
    },

    //----------------------------------

    manageAction: function(variationData)
    {
        MagentoMessageObj.clearAll();

        var parameters = Object.extend(
            {
                component: this.M2ePro.customData.componentMode,
                listing_product_id: this.listingProductId
            },
            variationData
        );

        new Ajax.Request(this.M2ePro.url.variation_manage_action, {
            method: 'post',
            parameters: parameters,
            onSuccess: (function(transport) {

                try {
                    this.managePopup.close();

                    var response = transport.responseText.evalJSON();

                    MagentoMessageObj['add' + response.type[0].toUpperCase() + response.type.slice(1)](response.message);

                    if (response.type == 'error') {
                        this.scroll_page_to_top();
                    } else {
                        this.gridHandler.unselectAllAndReload();
                    }

                } catch (e) {
                    this.scroll_page_to_top();
                    this.managePopup.close();
                    MagentoMessageObj.addError('Internal Error.');
                }
            }).bind(this)
        });
    },

    //----------------------------------------------

    manageGenerateAction: function(unique)
    {
        var attributesIndexes = {};

        $('variation_manage').select('th.attribute').each(function(el,i) {
            attributesIndexes[el.readAttribute('attribute').toLowerCase()] = i;
        });

        new Ajax.Request(this.M2ePro.url.variation_manage_generate_action, {
            method: 'post',
            parameters: {
                component: this.M2ePro.customData.componentMode,
                listing_product_id: this.listingProductId,
                unique: +(unique)
            },
            onSuccess: (function(transport) {

                try {
                    var response = transport.responseText.evalJSON();

                    if (response.type == 'error') {
                        MagentoMessageObj.addError(response.message);
                        this.managePopup.close();
                        return this.scroll_page_to_top();
                    }

                    if (response.text.length < 1 && Boolean(unique)) {
                        return alert(this.M2ePro.text.no_variations_left);
                    }

                    $('variation_manage_tbody').select('tr').invoke('remove');

                    response.text.each((function(attributes) {

                        this.manageAddRow();

                        var tr = $('variation_manage_tbody').select('tr').last();

                        var temp = [];

                        attributes.each(function(attribute) {
                            var index = attributesIndexes[attribute.attribute.toLowerCase()];
                            var select = tr.down('select[index=' + index + ']');
                            temp[index] = {select: select, value: attribute.option};
                        });

                        temp.each(function(obj) {
                            obj.select.value = obj.value;
                            obj.select.simulate('change');
                        });
                    }).bind(this));
                } catch (e) {
                    this.scroll_page_to_top();
                    this.managePopup.close();
                    MagentoMessageObj.addError('Internal Error.');
                }
            }).bind(this)
        });
    }

    //----------------------------------
});