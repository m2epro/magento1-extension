OrderEditItemHandler = Class.create();
OrderEditItemHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function()
    {
        this.popUp = null;
        this.gridId = null;
        this.orderItemId = null;
    },

    //----------------------------------

    openPopUp: function(title, content, customConfig)
    {
        var self = this;

        var config = {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            top: 50,
            maxHeight: 500,
            width: 600,
            zIndex: 100,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show,
            closeCallback: function() {
                self.reloadGrid();
                self.orderItemId = null;
                self.gridId = null;
                self.popUp = null;

                return true;
            }
        };

        for (var param in customConfig) {
            config[param] = customConfig[param];
        }

        if (!this.popUp) {
            this.popUp = Dialog.info(content, config);
            MagentoMessageObj.clearAll();
        } else {
            $('modal_dialog_message').innerHTML = content;
            var newDimensions = $('modal_dialog_message').getDimensions();

            this.popUp.setTitle(title);
            this.popUp.setSize(config.width, newDimensions.height);
            this.popUp._recenter();
        }

        $('modal_dialog_message').innerHTML.evalScripts();

        return this.popUp;
    },

    closePopUp: function()
    {
        if (this.popUp) {
            this.popUp.close();
        }
    },

    reloadGrid: function()
    {
        var grid = window[this.gridId + 'JsObject'];

        if (grid) {
            grid.doFilter();
        }
    },

    edit: function(gridId, orderItemId)
    {
        var self = this;

        self.gridId = gridId;
        self.orderItemId = orderItemId;

        self.getItemEditHtml(orderItemId, function(transport) {
            var response = transport.responseText.evalJSON();

            if (response.error) {
                if (self.popUp) {
                    alert(response.error);
                    self.closePopUp();
                } else {
                    MagentoMessageObj.addError(response.error);
                }

                return;
            }

            var title = response.title;
            var content = response.html;
            var popUpConfig = response.pop_up_config || {};

            self.openPopUp(title, content, popUpConfig);
        });
    },

    getItemEditHtml: function(itemId, callback)
    {
        new Ajax.Request(M2ePro.url.get('adminhtml_order/editItem'), {
            method: 'get',
            parameters: {
                item_id: itemId
            },
            onSuccess: function(transport) {
                if (typeof callback == 'function') {
                    callback(transport);
                }
            }
        });
    },

    afterActionCallback: function(transport)
    {
        var self = this;
        var response = transport.responseText.evalJSON();

        if (response.error) {
            alert(response.error);
            return;
        }

        if (response.continue) {
            self.edit(self.gridId, self.orderItemId);
            return;
        }

        if (response.success) {
            self.closePopUp();
            self.scroll_page_to_top();
            MagentoMessageObj.addSuccess(response.success);
        }
    },

    //----------------------------------

    assignProduct: function(id, productSku)
    {
        var self = this;
        var productId = +id || '';
        var sku = productSku || '';
        var orderItemId = self.orderItemId;

        MagentoMessageObj.clearAll();

        if (orderItemId == '' || (/^\s*(\d)*\s*$/i).test(orderItemId) == false) {
            return;
        }

        if (sku == '' && productId == '') {
            alert(M2ePro.translator.translate('Please enter correct Product ID or SKU.'));
            return;
        }

        if (((/^\s*(\d)*\s*$/i).test(productId) == false)) {
            alert(M2ePro.translator.translate('Please enter correct Product ID.'));
            return;
        }

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_order/assignProduct'), {
            method: 'post',
            parameters: {
                product_id: productId,
                sku: sku,
                order_item_id: orderItemId
            },
            onSuccess: self.afterActionCallback.bind(self)
        });
    },

    //----------------------------------

    assignProductDetails: function()
    {
        var self = this;
        var validationResult = $$('.form-element').collect(Validation.validate);

        if (validationResult.indexOf(false) != -1) {
            return;
        }

        if ($('save_repair') && $('save_repair').checked && !confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_order/assignProductDetails'), {
            method: 'post',
            parameters: Form.serialize('modal_dialog_message'),
            onSuccess: self.afterActionCallback.bind(self)
        });
    },

    //----------------------------------

    unassignProduct: function(gridId, orderItemId)
    {
        var self = this;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        self.gridId = gridId;
        self.orderItemId = orderItemId;

        new Ajax.Request(M2ePro.url.get('adminhtml_order/unassignProduct'), {
            method: 'post',
            parameters: {
                order_item_id: orderItemId
            },
            onSuccess: function(transport) {
                self.afterActionCallback(transport);
                self.reloadGrid();
                self.gridId = null;
                self.orderItemId = null;
            }
        });
    }

    //----------------------------------
});
