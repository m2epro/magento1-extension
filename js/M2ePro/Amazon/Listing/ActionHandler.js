AmazonListingActionHandler = Class.create(ListingActionHandler, {

    // ---------------------------------------

    deleteAndRemoveAction: function()
    {
        var selectedProductsParts = this.gridHandler.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startActions(
            this.options.text.deleting_and_removing_selected_items_message,
            this.options.url.runDeleteAndRemoveProducts,selectedProductsParts
        );
    }

    // ---------------------------------------
});