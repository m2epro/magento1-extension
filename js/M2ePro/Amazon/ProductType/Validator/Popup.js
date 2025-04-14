window.ProductTypeValidatorPopupClass = Class.create({
    closePopupCallback: undefined,
    closePopupCallbackArguments: [],

    openForProductType: function (isNewProductType, productTypeTemplateId, productIds = '') {
        var self = this;

        if (isNewProductType === '0') {
            var form = document.createElement('FORM');
            form.method = 'POST';
            form.action = M2ePro.url.get('product_type_validation_view_result');
            form.target = 'newWindow';
            form.insert(new Element('input', {'name': 'product_type_id', 'value': productTypeTemplateId, 'type': 'hidden'}));
            form.insert(new Element('input', {'name': 'listing_product_ids', 'value': productIds, 'type': 'hidden'}));
            form.insert(new Element('input', {'name': 'form_key', 'value': FORM_KEY, 'type': 'hidden'}));
            document.body.appendChild(form);
            var win = window.open(
                '',
                'newWindow',
            );
            form.submit();

            var intervalId = setInterval(function() {
                if (!win.closed) {
                    return;
                }

                clearInterval(intervalId);

                self.executeClosePopupCallback();
            }, 1000);
        } else {
            self.executeClosePopupCallback();
        }
    },

    executeClosePopupCallback: function () {
        var self = this;
        if (typeof this.closePopupCallback !== 'undefined') {
            setTimeout(function () {
                self.closePopupCallback(...self.closePopupCallbackArguments);
            }, 1)
        }
    }
});

window.ProductTypeValidatorPopup = new ProductTypeValidatorPopupClass();
