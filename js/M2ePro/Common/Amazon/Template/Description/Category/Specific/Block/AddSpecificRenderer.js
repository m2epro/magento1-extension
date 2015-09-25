CommonAmazonTemplateDescriptionCategorySpecificBlockGridAddSpecificRenderer = Class.create(CommonAmazonTemplateDescriptionCategorySpecificRenderer, {

    //----------------------------------

    childAllSpecifics  : [],
    childRowsSpecifics : [],

    selectedSpecifics : [],

    // --------------------------------

    process: function()
    {
        if (!this.load()) {
            return '';
        }

        this.prepareDomStructure();
        this.tuneStyles();
    },

    // --------------------------------

    prepareDomStructure: function()
    {
        this.getContainer().appendChild(this.getTemplate());

        $(this.indexedXPath + '_add_row').observe('child-specific-rendered' , this.onChildSpecificRendered.bind(this));
        $(this.indexedXPath).observe('child-specific-rendered', this.onChildSpecificRendered.bind(this));

        var addSpecificBlockButtonObj = $(this.indexedXPath + '_add_button');
        addSpecificBlockButtonObj.observe('click', this.addSpecificAction.bind(this));
    },

    // --------------------------------

    onChildSpecificRendered: function()
    {
        this.tuneStyles();

        if (this.indexedXPath == this.getRootIndexedXpath()) {
            return;
        }

        var myEvent = new CustomEvent('child-specific-rendered');

        $(this.getParentIndexedXpath() + '_add_row') && $(this.getParentIndexedXpath() + '_add_row').dispatchEvent(myEvent);
        $(this.getParentIndexedXpath()) && $(this.getParentIndexedXpath()).dispatchEvent(myEvent);
    },

    // --------------------------------

    tuneStyles: function()
    {
        var addSpecificsRowObj        = $(this.indexedXPath + '_add_row'),
            addSpecificBlockButtonObj = $(this.indexedXPath + '_add_button');

        // --
        addSpecificsRowObj && addSpecificsRowObj.show();

        if ((this.getRootIndexedXpath() != this.indexedXPath && this.isAnyOfChildSpecificsRendered()) ||
            this.isAllOfSpecificsRendered())
        {
            addSpecificsRowObj && addSpecificsRowObj.hide();
        }
        // --

        //--
        addSpecificBlockButtonObj && addSpecificBlockButtonObj.show();

        if (this.getRootIndexedXpath() == this.indexedXPath || this.isAllOfSpecificsRendered() || !this.isAnyOfChildSpecificsRendered()) {
            addSpecificBlockButtonObj && addSpecificBlockButtonObj.hide();
        }
        //--
    },

    isAnyOfChildSpecificsRendered: function()
    {
        var countOfRenderedSpecifics = 0;
        var realRenderedXpathes = this.specificHandler.getRealXpathesOfRenderedSpecifics();

        this.childAllSpecifics.each(function(sp) {
            if (realRenderedXpathes.indexOf(sp.xpath) >= 0) countOfRenderedSpecifics++;
        });

        return countOfRenderedSpecifics > 0;
    },

    isAllOfSpecificsRendered: function()
    {
        var countOfRenderedSpecifics = 0;

        var realRenderedXpathes = this.specificHandler.getRealXpathesOfRenderedSpecifics(),
            allChildSpecifics   = this.specificHandler.dictionaryHelper.getAllChildSpecifics(this.specific);

        allChildSpecifics.each(function(sp) {
            if (realRenderedXpathes.indexOf(sp.xpath) >= 0) countOfRenderedSpecifics++;
        });

        return countOfRenderedSpecifics == allChildSpecifics.length;
    },

    //###################################

    isAlreadyRendered: function()
    {
        return $(this.indexedXPath + '_add_row') == null;
    },

    getTemplate: function()
    {
        var template = $('specifics_add_row_template').down('table').cloneNode(true);

        template.down('button.add_custom_specific_button').observe('click', this.addSpecificAction.bind(this));
        template.setAttribute('id', this.indexedXPath + '_add_row');

        return template;
    },

    getContainer: function()
    {
        return $(this.indexedXPath);
    },

    // POPUP
    //###################################

    addSpecificAction: function(event)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_template_description/getAddSpecificsHtml'), {
            method: 'post',
            parameters: {
                marketplace_id        : $('marketplace_id').value,
                product_data_nick     : $('product_data_nick').value,
                current_indexed_xpath : self.indexedXPath,
                rendered_specifics    : Object.toJSON(self.specificHandler.renderedSpecifics)
            },
            onSuccess: function(transport) {

                if (typeof self.popUp != 'undefined') {
                    self.popUp.close();
                }

                self.selectedSpecifics = [];
                self.openPopUp(transport.responseText);
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
            title: M2ePro.translator.translate('Add Specifics'),
            width: 850,
            height: 500,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        var modalDialogContainer = $('modal_dialog_message');

        modalDialogContainer.insert(html);
        modalDialogContainer.innerHTML.evalScripts();

        self.observePopupButtonsActions();
        self.observePopupGridRowsActions();
        self.tunePopupStyles();
    },

    observePopupButtonsActions: function()
    {
        var modalDialogContainer = $('modal_dialog_message');

        modalDialogContainer.down('button.specifics_done_button').observe('click', this.specificsDoneButton.bind(this));
        modalDialogContainer.down('a.specifics_cancel_button').observe('click', this.specificsCancelButton.bind(this));

        modalDialogContainer.down('button.specifics_filter_button').observe('click', this.specificsFilterButton.bind(this));
        modalDialogContainer.down('a.specifics_reset_filter_button').observe('click', this.specificsResetFilterButton.bind(this));
        modalDialogContainer.down('a.specifics_reset_selected_button').observe('click', this.specificsResetSelectedButton.bind(this));

        modalDialogContainer.down('#query').observe('keypress', this.specificsKeyPressQuery.bind(this));
    },

    observePopupGridRowsActions: function()
    {
        var self = this;

        $$('#specifics_grid_container a.specific_search_result_row').each(function(el) {
            el.observe('click', self.specificsSelectRow.bind(self));
        });
    },

    tunePopupStyles: function()
    {
        $$('#amazonTemplateDescriptionCategorySpecificAddGrid div.grid th').each(function(el) {
            el.style.padding = '1px 1px';
        });

        $$('#amazonTemplateDescriptionCategorySpecificAddGrid div.grid td').each(function(el) {
            el.style['padding'] = '1px 1px';
            el.style['vertical-align'] = 'middle';
        });
    },

    //###################################

    specificsDoneButton: function(event)
    {
        var self = this;

        self.selectedSpecifics.each(function(indexedXpath) {
            self.specificHandler.renderSpecific(indexedXpath);
        });

        self.specificsCancelButton(event);
    },

    specificsCancelButton: function(event)
    {
        this.popUp.close();
    },

    specificsFilterButton: function(event)
    {
        this.reloadSearchingGrid($('query').value, $('only_desired').value);
    },

    specificsKeyPressQuery: function(event)
    {
        if (event.keyCode == 13) {
            this.specificsFilterButton(event);
        }
    },

    specificsResetFilterButton: function(event)
    {
        $('query').value = '';
        $('query').focus();
        $('only_desired').value = '0';

        this.reloadSearchingGrid('', '');
    },

    specificsResetSelectedButton: function(event)
    {
        var selectedSpecificsBox = $('selected_specifics_box');

        selectedSpecificsBox.update('');
        $('selected_specifics_container').hide();

        $('specifics_grid_container').style.height = '370px';

        this.selectedSpecifics = [];
        this.specificsFilterButton(event);
    },

    specificsSelectRow: function(event)
    {
        var selectedSpecificsBox = $('selected_specifics_box'),
            newIndexedXpath     = this.getNewSpecificXpath(event.target.getAttribute('xpath'));

        selectedSpecificsBox.appendChild(new Element('span', {
                                class   : 'selected-specific-box-item',
                                xml_tag : event.target.getAttribute('xml_tag'),
                                xpath   : newIndexedXpath
                            }))
                            .update(event.target.getAttribute('xml_tag'))
                            .appendChild(new Element('a', {
                                href  : 'javascript:void(0);',
                                class : 'remove-link-button',
                                align : 'center',
                                title : M2ePro.translator.translate('Remove this specific')
                            }))
                            .update('x')
                            .observe('click', this.specificsUnSelectRow.bind(this));

        $('selected_specifics_container').show();
        $('specifics_grid_container').style.height = (370 - $('selected_specifics_container').offsetHeight - 6) + 'px';

        this.selectedSpecifics.push(newIndexedXpath);
        this.specificsFilterButton(event);
    },

    specificsUnSelectRow: function(event)
    {
        var newPreparedXpath = event.target.up('span').getAttribute('xpath');

        event.target.up('span').remove();

        var index = this.selectedSpecifics.indexOf(newPreparedXpath);
        index >= 0 && this.selectedSpecifics.splice(index, 1);

        if (this.selectedSpecifics.length <= 0) {
            return this.specificsResetSelectedButton(event);
        }

        this.specificsFilterButton(event);
    },

    // --------------------------------

    reloadSearchingGrid: function(query, onlyDesired)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.get('adminhtml_common_amazon_template_description/getAddSpecificsGridHtml'), {
            method: 'post',
            parameters: {
                marketplace_id        : $('marketplace_id').value,
                product_data_nick     : $('product_data_nick').value,
                current_indexed_xpath : self.indexedXPath,
                rendered_specifics    : Object.toJSON(self.specificHandler.renderedSpecifics),
                selected_specifics    : Object.toJSON(self.selectedSpecifics),
                only_desired          : onlyDesired,
                query                 : query
            },
            onSuccess: function(transport) {
                $('specifics_grid_container').down('div.grid-wrapper').update(transport.responseText);
                self.tunePopupStyles();
                self.observePopupGridRowsActions();
            }
        });
    },

    //###################################

    getNewSpecificXpath: function(dictionaryXpath)
    {
        var currentRealXpath = this.indexedXPath.replace(/-\d+/g, '');
        var newIndexedXpath  = '';

        var temp = dictionaryXpath.replace(currentRealXpath + '/', '');

        temp.split('/').each(function(pathPart) {
            newIndexedXpath += '/' + pathPart + '-1';
        });

        return this.indexedXPath + newIndexedXpath;
    }

    // --------------------------------
});