window.WalmartListingAction = Class.create(ListingAction, {

    // ---------------------------------------

    deleteAndRemoveAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(
            M2ePro.text.deleting_and_removing_selected_items_message,
            M2ePro.url.runDeleteAndRemoveProducts,
            selectedProductsParts,
            {"is_realtime": true}
        );
    },

    resetProductsAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(
            M2ePro.text.reset_blocked_products_message,
            M2ePro.url.runResetProducts,
            selectedProductsParts,
            {"is_realtime": true}
        );
    }

    // ---------------------------------------
});