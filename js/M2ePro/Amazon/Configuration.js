window.AmazonConfiguration = Class.create(Common, {
    generalIdModeChange: function()
    {
        const self = AmazonConfigurationObj;
        const generalIdAttribute = $('general_id_custom_attribute');

        generalIdAttribute.value = '';

        if (parseInt(this.value)) {
            self.updateHiddenValue(this, generalIdAttribute);
        }
    },

    worldwideIdModeChange: function()
    {
        const self = AmazonConfigurationObj;
        const generalIdAttribute = $('worldwide_id_custom_attribute');

        generalIdAttribute.value = '';

        if (parseInt(this.value)) {
            self.updateHiddenValue(this, generalIdAttribute);
        }
    },
});