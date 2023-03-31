window.AmazonAccountCreate = Class.create(Common, {

    initialize: function() {
        this.initValidators();
    },

    initValidators: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-account-title',
            M2ePro.translator.translate('The specified Title is already used for other Account. Account Title must be unique.'),
            'Account', 'title', 'id',
            '',
            M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon::NICK'));
    },

    continueClick: function()
    {
        var url = M2ePro.url.urls['adminhtml_amazon_account/create'];

        MessageObj.clear();

        var editForm = new varienForm('edit_form');
        if (!editForm.validate()) {
            return;
        }

        new Ajax.Request(url, {
            method: 'post',
            parameters: Form.serialize($('edit_form')),
            onSuccess: function(transport) {
                transport = transport.responseText.evalJSON();

                if (transport.success) {
                    window.location = transport.url;
                } else {
                    MessageObj.addError(transport.message);
                }
            }
        });
    },

});