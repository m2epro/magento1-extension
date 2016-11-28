CommonAmazonRepricingHandler = Class.create(ActionHandler, {

    // ---------------------------------------

    initialize: function ($super, gridHandler) {
        var self = this;
        $super(gridHandler);
    },

    // ---------------------------------------

    options: {},

    setOptions: function (options) {
        this.options = Object.extend(this.options, options);
        return this;
    },

    // ---------------------------------------

    openManagement: function () {
        window.open(M2ePro.url.get('adminhtml_common_amazon_listing_repricing/openManagement'));
    },

    // ---------------------------------------

    addToRepricing: function (productsIds)
    {
        return this.postForm(M2ePro.url.get('adminhtml_common_amazon_listing_repricing/openAddProducts'), {'products_ids': productsIds});
    },

    showDetails: function (productsIds)
    {
        return this.postForm(M2ePro.url.get('adminhtml_common_amazon_listing_repricing/openShowDetails'), {'products_ids': productsIds});
    },

    editRepricing: function (productsIds)
    {
        return this.postForm(M2ePro.url.get('adminhtml_common_amazon_listing_repricing/openEditProducts'), {'products_ids': productsIds});
    },

    removeFromRepricing: function (productsIds)
    {
        return this.postForm(M2ePro.url.get('adminhtml_common_amazon_listing_repricing/openRemoveProducts'), {'products_ids': productsIds});
    }

    // ---------------------------------------
});