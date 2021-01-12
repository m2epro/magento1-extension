window.TemplateEdit = Class.create(Common, {

    // ---------------------------------------

    showConfirmMsg: true,
    skipSaveConfirmationPostFix: '_skip_save_confirmation',

    // ---------------------------------------

    getComponent: function() {
        alert('abstract getComponent');
    },

    // ---------------------------------------

    confirm: function(templateNick, confirmText, okCallback) {
        var self = this,
            skipConfirmationId = this.getComponent() + '_template_' + templateNick + self.skipSaveConfirmationPostFix;

        if (!confirmText || LocalStorageObj.get(skipConfirmationId)) {
            okCallback();
            return;
        }

        var template = $('dialog_confirm_container');

        template.down('.dialog_confirm_content').innerHTML =
            '<div class="magento-message" style="position: absolute; left: 0; padding-left: 10px;"">' + confirmText + '</div>' +
            '<div style="position: absolute; bottom: 0; left: 0; padding: 10px;">' +
            '<input type="checkbox" id="do_not_show_again" name="do_not_show_again">&nbsp;' +
            M2ePro.translator.translate('Do not show any more') +
            '</div>';

        var me = this;
        if (!me.isCreatedDialog) {
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
                        LocalStorageObj.set(skipConfirmationId, 1);
                    }

                    okCallback();
                },
                cancel: function() {
                },
                onClose: function() {
                    me.isCreatedDialog = false;
                }
            });
        }
    },

    // ---------------------------------------

    save_click: function($super, url, confirmText, templateNick) {
        if (!this.validateForm()) {
            return;
        }

        if (confirmText && this.showConfirmMsg) {
            this.confirm(templateNick, confirmText, function() {
                $super(url);
            });
            return;
        }

        $super(url);
    },

    save_and_edit_click: function($super, url, tabsId, confirmText, templateNick) {
        if (!this.validateForm()) {
            return;
        }

        if (confirmText && this.showConfirmMsg) {
            this.confirm(templateNick, confirmText, function() {
                $super(url);
            });
            return;
        }

        $super(url, tabsId);
    },

    saveAndClose: function(url) {
        if (!this.validateForm()) {
            return;
        }

        new Ajax.Request(url, {
            method: 'post',
            parameters: Form.serialize($(editForm.formId)),
            onSuccess: function() {
                window.close();
            }
        });
    },

    duplicate_click: function($super, $headId, chapter_when_duplicate_text) {
        this.showConfirmMsg = false;

        $super($headId, chapter_when_duplicate_text);
    },

    // ---------------------------------------

    validateForm: function() {
        return editForm.validate();
    },

    // ---------------------------------------

    forgetSkipSaveConfirmation: function() {
        LocalStorageObj.removeAllByPostfix(this.skipSaveConfirmationPostFix);
    }

    // ---------------------------------------
});
