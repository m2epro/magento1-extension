window.ListingOtherGrid = Class.create(Grid, {

    // ---------------------------------------

    productTitleCellIndex: 2,

    // ---------------------------------------

    prepareActions: function()
    {
        this.movingHandler      = new ListingMoving(this);
        this.autoMapping = new ListingOtherAutoMapping(this);
        this.removingHandler    = new ListingOtherRemoving(this);
        this.unmappingHandler   = new ListingOtherUnmapping(this);

        this.actions = {
            movingAction: this.movingHandler.run.bind(this.movingHandler),
            autoMappingAction: this.autoMapping.run.bind(this.autoMapping),
            removingAction: this.removingHandler.run.bind(this.removingHandler),
            unmappingAction: this.unmappingHandler.run.bind(this.unmappingHandler)
        };
    }

    // ---------------------------------------
});