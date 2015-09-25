CommonListingSettingsHandler = Class.create();
CommonListingSettingsHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    storeId: null,
    marketplaceId: null,

    //----------------------------------

    initialize: function() {

        this.setValidationCheckRepetitionValue('M2ePro-listing-title',
                                                M2ePro.text.title_not_unique_error,
                                                'Listing', 'title', 'id',
                                                M2ePro.formData.id,
                                                M2ePro.php.constant('Ess_M2ePro_Helper_Component::NICK'));

        Validation.add('M2ePro-input-datetime', M2ePro.text.wrong_date_time_format_error, function(value,el) {

            if ($(el).up('tr').visible()) {
                return value.match(/^\d{4}-\d{2}-\d{1,2}\s\d{2}:\d{2}:\d{2}$/g);
            }

            return true;
        });
    },

    //----------------------------------

    save_click: function(url)
    {
        if (typeof categories_selected_items != 'undefined') {
            array_unique(categories_selected_items);

            var selectedCategories = implode(',',categories_selected_items);

            $('selected_categories').value = selectedCategories;
        }

        if (typeof url == 'undefined' || url == '') {
            url = M2ePro.url.formSubmit + 'back/'+base64_encode('list')+'/';
        }

        this.submitForm(url);
    },

    save_and_edit_click: function(url, lastActiveTab)
    {
        if (typeof categories_selected_items != 'undefined') {
            array_unique(categories_selected_items);

            var selectedCategories = implode(',',categories_selected_items);

            $('selected_categories').value = selectedCategories;
        }

        if (lastActiveTab && url) {
            var tabsUrl = '|tab=' + window[M2ePro.php.constant('Ess_M2ePro_Helper_Component::NICK') + 'ListingEditTabsJsTabs'].activeTab.id.split('_').pop();
            url = url + 'back/'+base64_encode('edit'+tabsUrl) + '/';
        }

        this.submitForm(url);
    },

    reloadSellingFormatTemplates: function()
    {
        CommonListingSettingsHandlerObj.reload(M2ePro.url.getSellingFormatTemplates, 'template_selling_format_id');
    },

    reloadSynchronizationTemplates: function()
    {
        CommonListingSettingsHandlerObj.reload(M2ePro.url.getSynchronizationTemplates, 'template_synchronization_id');
    },

    //----------------------------------

    initSellingFormatTemplateAutocomplete: function()
    {
        $('template_selling_format_autocomplete').remove();

        var newInput = new Element('input', {
            'id'         : 'template_selling_format_autocomplete',
            'class'      : 'input-text',
            'selected_id': '',
            'style'      : 'width: 275px; color: gray;',
            'value'      : M2ePro.text.typeTemplateNameHere,
            'onblur'     : 'if (this.value.trim().length == 0) { this.value = M2ePro.text.typeTemplateNameHere; this.style.color = "gray"; }',
            'onfocus'    : 'if (this.value == M2ePro.text.typeTemplateNameHere) { this.value = ""; this.style.color = ""}'
        });

        if (M2ePro.formData.template_selling_format_id > 0) {
            newInput.setStyle({color: 'initial'});
        }

        $('template_selling_format_cell').insert({top: newInput});

        AutoCompleteHandler.bind(
            "template_selling_format_autocomplete",
            M2ePro.autoCompleteData.url.getSellingFormatTemplates,
            M2ePro.formData.template_selling_format_id > 0 ? M2ePro.formData.template_selling_format_id : '',
            M2ePro.formData.template_selling_format_title,
            CommonListingSettingsHandlerObj.selling_format_template_id_change.bind(CommonListingSettingsHandlerObj)
        );

        M2ePro.formData.template_selling_format_id = 0;
        M2ePro.formData.template_selling_format_title = '';

        $('template_selling_format_id').value = $('template_selling_format_autocomplete').readAttribute('selected_id');
    },

    //----------------------------------

    selling_format_template_id_simulate_change: function()
    {
        var intervalRestartLimit = 20;
        var intervalRestartCount = 0;

        var intervalId = setInterval(function simulateSellingFormatTemplateChange() {
            intervalRestartCount++;

            if (intervalRestartCount >= intervalRestartLimit || Ajax.activeRequestCount == 0) {
                $('template_selling_format_id').value && $('template_selling_format_id').simulate('change');

                clearInterval(intervalId);
            }
        }, 250);
    },

    selling_format_template_id_change: function(autoCompleteId)
    {
        if (parseInt(autoCompleteId) > 0) {
            $('template_selling_format_id').value = autoCompleteId;
        }

        CommonListingSettingsHandlerObj.checkMessages();
        CommonListingSettingsHandlerObj.hideEmptyOption(this);
    },

    //----------------------------------

    checkMessages: function()
    {
        if (CommonListingSettingsHandlerObj.storeId === null || CommonListingSettingsHandlerObj.marketplaceId === null) {
            return;
        }

        var id = $('template_selling_format_id').value,
            nick = 'selling_format',
            storeId = CommonListingSettingsHandlerObj.storeId,
            marketplaceId = CommonListingSettingsHandlerObj.marketplaceId,
            checkAttributesAvailability = false,
            container = 'template_selling_format_messages',
            callback = function() {
                var refresh = $(container).down('a.refresh-messages');
                if (refresh) {
                    refresh.observe('click', function() {
                        this.checkMessages();
                    }.bind(this))
                }
            }.bind(this);

        TemplateHandlerObj.checkMessages(
            id,
            nick,
            '',
            storeId,
            marketplaceId,
            checkAttributesAvailability,
            container,
            callback
        );
    },

    //----------------------------------

    synchronization_template_id_change: function()
    {
        CommonListingSettingsHandlerObj.hideEmptyOption(this);
    },

    //----------------------------------

    reload: function(url, id)
    {
        new Ajax.Request(url, {
            asynchronous: false,
            onSuccess: function(transport) {

                var data = transport.responseText.evalJSON(true);

                var options = '';

                var firstItemValue = '';
                var currentValue = $(id).value;

                data.each(function(paris) {
                    var key = (typeof paris.key != 'undefined') ? paris.key : paris.id;
                    var val = (typeof paris.value != 'undefined') ? paris.value : paris.title;
                    options += '<option value="' + key + '">' + val + '</option>\n';

                    if (firstItemValue == '') {
                        firstItemValue = key;
                    }
                });

                $(id).update();
                $(id).insert(options);

                if (currentValue != '') {
                    $(id).value = currentValue;
                } else {
                    if (M2ePro.formData[id] > 0) {
                        $(id).value = M2ePro.formData[id];
                    } else {
                        $(id).value = firstItemValue;
                    }
                }
                $(id).simulate('change');
            }
        });
    },

    //----------------------------------

    addNewTemplate: function(url, callback)
    {
        var win = window.open(url);

        var intervalId = setInterval(function() {

            if (!win.closed) {
                return;
            }

            clearInterval(intervalId);

            callback && callback();

        }, 1000);
    },

    //----------------------------------

    newSellingFormatTemplateCallback: function()
    {
        var noteEl = $('template_selling_format_note');

        CommonListingSettingsHandlerObj.reloadSellingFormatTemplates();
        if ($('template_selling_format_id').children.length > 0) {
            $('template_selling_format_id').show();
            noteEl && $('template_selling_format_note').show();
            $('template_selling_format_label').hide();
        } else {
            $('template_selling_format_id').hide();
            noteEl && $('template_selling_format_note').hide();
            $('template_selling_format_label').show();
        }
    },

    //----------------------------------

    newSynchronizationTemplateCallback: function()
    {
        var noteEl = $('template_synchronization_note');

        CommonListingSettingsHandlerObj.reloadSynchronizationTemplates();
        if ($('template_synchronization_id').children.length > 0) {
            $('template_synchronization_id').show();
            noteEl &&  $('template_synchronization_note').show();
            $('template_synchronization_label').hide();
        } else {
            $('template_synchronization_id').hide();
            noteEl &&  $('template_synchronization_note').hide();
            $('template_synchronization_label').show();
        }
    }

    //----------------------------------
});