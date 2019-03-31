AccountGridHandler = Class.create(GridHandler, {

    // ---------------------------------------

    prepareActions: function()
    {
        this.accountHandler = new AccountHandler();

        this.actions = {
            deleteAction: function(id) {
                var ids = id ? id : this.getSelectedProductsString();
                this.accountHandler.on_delete_popup(ids);
            }.bind(this)
        };
    },

    // ---------------------------------------

    confirm: function()
    {
        return true;
    }

    // ---------------------------------------
});