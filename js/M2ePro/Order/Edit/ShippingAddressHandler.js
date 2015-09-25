OrderEditShippingAddressHandler = Class.create();
OrderEditShippingAddressHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(countryElementId, regionContainerElementId, regionElementName)
    {
        this.countryElementId = countryElementId;
        this.regionContainerElementId = regionContainerElementId;
        this.regionElementName = regionElementName;
    },

    //----------------------------------

    countryCodeChange: function()
    {
        var self = OrderEditShippingAddressHandlerObj;

        new Ajax.Request(M2ePro.url.get('adminhtml_order/getCountryRegions'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                country: $(self.countryElementId).value
            },
            onSuccess: function(transport) {
                self.renderRegions(transport.responseText.evalJSON(true));
            }
        });
    },

    renderRegions: function(data)
    {
        var self = OrderEditShippingAddressHandlerObj,
            regionContainer = $(self.regionContainerElementId),
            html = '';

        if (data.length == 0) {
            html = '<input type="text" name="%name%" class="input-text" value="%value%" />'
                .replace(/%name%/, self.regionElementName)
                .replace(/%value%/, M2ePro.formData.region);
        } else {
            html += '<select name="'+self.regionElementName+'">';
            data.each(function(item) {
                var selected = (item.value == M2ePro.formData.region) ? 'selected="selected"' : '';
                html += '<option value="'+item.value+'" '+selected+'>'+item.label+'</option>';
            });
            html += '</select>';
        }

        regionContainer.innerHTML = html;
    }

    //----------------------------------
});