window.ListingProductGrid = Class.create(Common, {

    // ---------------------------------------

    initialize: function(AddProductObj)
    {
        this.addProductObj = AddProductObj || null;
    },

    // ---------------------------------------

    save_click: function(back)
    {
        var selected = this.getSelectedProducts();
        if (selected) {
            this.addProductObj.add(selected, false, back, '');
        }
    },

    // ---------------------------------------

    save_and_list_click: function(back)
    {
        if (this.getSelectedProducts()) {
            this.addProductObj.add(this.getSelectedProducts(), back, 'yes');
        }
    },

    // ---------------------------------------

    setFilter: function(event)
    {
        if (event != undefined) {
            Event.stop(event);
        }

        var filters = $$('#'+this.containerId+' .filter input', '#'+this.containerId+' .filter select');
        var elements = [];
        for(var i in filters) {
            if(filters[i].value && filters[i].value.length) elements.push(filters[i]);
        }
        if (!this.doFilterCallback || (this.doFilterCallback && this.doFilterCallback())) {
            var ruleParams = $('rule_form').serialize(true);

            var numParams = 0;
            for (var param in ruleParams) {
                numParams++;
            }

            this.reloadParams = this.reloadParams || {};

            for (var reloadParam in this.reloadParams) {
                reloadParam.match('^rule|^hide') && delete this.reloadParams[reloadParam];
            }

            if (numParams > 5) {
                this.reloadParams = Object.extend(this.reloadParams, ruleParams);
            } else {

                if (ruleParams['hide_products_others_listings'] == 0) {
                    this.reloadParams.hide_products_others_listings = 0;
                }

                this.reloadParams.rule = "";
            }

            ProductGridObj.clearUrlFromFilter();

            this.reload(this.addVarToUrl(this.filterVar, encode_base64(Form.serializeElements(elements))));
        }
    },

    resetFilter: function()
    {
        if (!this.reloadParams) {
            this.reloadParams = Object.extend({});
        }

        for (var reloadParam in this.reloadParams) {
            reloadParam.match('^rule|^hide') && delete this.reloadParams[reloadParam];
        }
        this.reloadParams.rule = "";

        ProductGridObj.clearUrlFromFilter();

        this.reload(this.addVarToUrl(this.filterVar, ''));
    },

    advancedFilterToggle: function()
    {
        if ($('listing_product_rules').visible()) {
            $('listing_product_rules').hide();
            if ($$('#advanced_filter_button span span span').length > 0) {
                $$('#advanced_filter_button span span span')[0].innerHTML = M2ePro.translator.translate('Show Advanced Filter');
            } else {
                $$('#advanced_filter_button span')[0].innerHTML = M2ePro.translator.translate('Show Advanced Filter');
            }
        } else {
            $('listing_product_rules').show();
            if ($$('#advanced_filter_button span span span').length > 0) {
                $$('#advanced_filter_button span span span')[0].innerHTML = M2ePro.translator.translate('Hide Advanced Filter');
            } else {
                $$('#advanced_filter_button span')[0].innerHTML = M2ePro.translator.translate('Hide Advanced Filter');
            }
        }
    },

    // ---------------------------------------

    setGridId:  function(id)
    {
        this.gridId = id;
    },

    getGridId:  function()
    {
        return this.gridId;
    },

    // ---------------------------------------

    getSelectedProducts: function()
    {
        var selectedProducts = window[this.getGridId() + '_massactionJsObject'].checkedString;

        if (!selectedProducts) {
            alert(M2ePro.translator.translate('Please select the Products you want to perform the Action on.'));
            return false;
        }
        return selectedProducts;
    },

    clearUrlFromFilter: function () {
        var url = window.location.href;
        var urlParts = url.split('/');
        var index = urlParts.indexOf('filter');

        if (index !== -1) {
            urlParts.splice(index, 2);
            url = urlParts.join('/');
            window.history.pushState("", "", url);
        }
    }
});
