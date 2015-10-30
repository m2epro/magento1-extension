EbayTemplateEditHandler = Class.create(CommonHandler, {

    // ---------------------------------------

    templateNick: null,

    showConfirmMsg: true,

    // ---------------------------------------

    initialize: function()
    {
        Validation.add('validate-title-uniqueness', M2ePro.translator.translate('Policy Title is not unique.'), function(value, el) {

            var unique = false;

            new Ajax.Request(M2ePro.url.get('adminhtml_ebay_template/isTitleUnique'), {
                method: 'post',
                asynchronous: false,
                parameters: {
                    id_value: $$('input[name="'+EbayTemplateEditHandlerObj.templateNick+'[id]"]')[0].value,
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

        $$('input[name="'+EbayTemplateEditHandlerObj.templateNick+'[title]"]')[0].value = $('title').value;

        return validationResult;
    },

    // ---------------------------------------

    confirm: function(templateNick, confirmText, okCallback)
    {
        var skipConfirmation = getCookie('ebay_template_'+templateNick+'_skip_save_confirmation');

        if (!confirmText || skipConfirmation) {
            okCallback();
            return;
        }

        var template = $('dialog_confirm_container');

        template.down('.dialog_confirm_content').innerHTML = '<div class="magento-message">'+confirmText+'</div>' +
            '<div style="position: absolute; bottom: 0; left: 0; padding: 10px;">' +
                '<input type="checkbox" id="do_not_show_again" name="do_not_show_again">&nbsp;' +
                M2ePro.translator.translate('Do not show any more') +
            '</div>';

        var me = this;
        if(!me.isCreatedDialog) {
            me.isCreatedDialog = true;
            Dialog._openDialog(template.innerHTML, {
                draggable: true,
                resizable: true,
                closable: true,
                className: "magento",
                title: 'Save Policy',
                height: 80,
                width: 650,
                zIndex: 2100,
                destroyOnClose: true,
                hideEffect: Element.hide,
                showEffect: Element.show,
                id: "save-template",
                buttonClass: "form-button button",
                ok: function() {
                    if ($('do_not_show_again').checked) {
                        setCookie('ebay_template_'+templateNick+'_skip_save_confirmation', 1, 3*365, '/');
                    }

                    okCallback();
                },
                cancel: function() {},
                onClose: function() {
                    me.isCreatedDialog = false;
                }
            });
        }
    },

    save_click: function($super, url, confirmText, templateNick)
    {
        if (!this.validateForm()) {
            return;
        }

        if (confirmText && this.showConfirmMsg) {
            this.confirm(templateNick, confirmText, function() { $super(url); });
            return;
        }

        $super(url);
    },

    save_and_edit_click: function($super, url, tabsId, confirmText, templateNick)
    {
        if (!this.validateForm()) {
            return;
        }

        if (confirmText && this.showConfirmMsg) {
            this.confirm(templateNick, confirmText, function() { $super(url); });
            return;
        }

        $super(url, tabsId);
    },

    duplicate_click: function($super, headId, chapter_when_duplicate_text, templateNick)
    {
        this.showConfirmMsg = false;
        $$('input[name="'+templateNick+'[id]"]')[0].value = '';

        // we don't need it here, but parent method requires the formSubmitNew url to be defined
        M2ePro.url.add({'formSubmitNew': ' '});

        $super(headId, chapter_when_duplicate_text);
    }

    // ---------------------------------------
});