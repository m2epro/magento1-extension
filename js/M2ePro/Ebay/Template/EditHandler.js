EbayTemplateEditHandler = Class.create(TemplateEditHandler, {

    // ---------------------------------------

    templateNick: null,

    // ---------------------------------------

    initialize: function()
    {
        Validation.add('validate-title-uniqueness', M2ePro.translator.translate('Policy Title is not unique.'), function(value, el) {

            var unique = false,
                idInput = $$('input[name="'+EbayTemplateEditHandlerObj.templateNick+'[id]"]')[0],
                idValue = '';

            if (idInput) {
                idValue = idInput.value;
            }

            new Ajax.Request(M2ePro.url.get('adminhtml_ebay_template/isTitleUnique'), {
                method: 'post',
                asynchronous: false,
                parameters: {
                    id_value: idValue,
                    title: $('title').value
                },
                onSuccess: function(transport)
                {
                    unique = transport.responseText.evalJSON()['unique'];
                }
            });

            return unique;
        });
    },

    // ---------------------------------------

    getComponent: function()
    {
        return 'ebay';
    },

    // ---------------------------------------

    loadTemplateData: function()
    {
        var marketplaceId = $('marketplace_id') ? $('marketplace_id').value : null;

        new Ajax.Request(M2ePro.url.get('adminhtml_ebay_template/getTemplateHtml'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                marketplace_id: marketplaceId
            },
            onSuccess: function(transport) {

                var editFormData = $('edit_form_data');
                if (!editFormData) {
                    editFormData = document.createElement('div');
                    editFormData.id = 'edit_form_data';

                    $('edit_form').appendChild(editFormData);
                }

                editFormData.innerHTML = transport.responseText;
                editFormData.innerHTML.extractScripts()
                    .map(function(script) {
                        try {
                            eval(script);
                        } catch(e) {}
                    });

                var titleInput = $$('input[name="'+this.templateNick+'[title]"]')[0];
                var marketplaceIdInput = $$('input[name="'+this.templateNick+'[marketplace_id]"]')[0];

                if ($('title').value.trim() == '') {
                    $('title').value = titleInput.value;
                }

                if (marketplaceIdInput) {
                    marketplaceIdInput.value = marketplaceId;
                }
            }.bind(this)
        });
    },

    // ---------------------------------------

    validateForm: function()
    {
        var validationResult = true;

        validationResult &= editForm.validate();
        validationResult &= Validation.validate($('title'));

        if ($('marketplace_id')) {
            validationResult &= Validation.validate($('marketplace_id'));
        }

        if ($('ebay_template_synchronization_edit_form_container')) {
            EbayTemplateSynchronizationHandlerObj.checkVirtualTabValidation();
        }

        var titleInput = $$('input[name="'+EbayTemplateEditHandlerObj.templateNick+'[title]"]')[0];

        if (titleInput) {
            titleInput.value = $('title').value;
        }

        return validationResult;
    },

    // ---------------------------------------

    duplicate_click: function($super, headId, chapter_when_duplicate_text, templateNick)
    {
        $$('input[name="'+templateNick+'[id]"]')[0].value = '';

        // we don't need it here, but parent method requires the formSubmitNew url to be defined
        M2ePro.url.add({'formSubmitNew': ' '});

        $super(headId, chapter_when_duplicate_text);
    }

    // ---------------------------------------
});