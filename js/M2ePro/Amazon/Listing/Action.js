window.AmazonListingAction = Class.create(ListingAction, {

    // ---------------------------------------

    deleteAndRemoveAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(
            M2ePro.text.deleting_and_removing_selected_items_message,
            M2ePro.url.runDeleteAndRemoveProducts,selectedProductsParts
        );
    }

    // ---------------------------------------
});