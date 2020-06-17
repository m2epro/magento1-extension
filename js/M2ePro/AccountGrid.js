window.AccountGrid = Class.create(Grid, {

    // ---------------------------------------

    prepareActions: function()
    {
        this.accountHandler = new Account();

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