ListingOtherGridHandler = Class.create(GridHandler, {

    //----------------------------------

    productTitleCellIndex: 2,

    //----------------------------------

    prepareActions: function()
    {
        this.movingHandler      = new ListingMovingHandler(this);
        this.autoMappingHandler = new ListingOtherAutoMappingHandler(this);
        this.removingHandler    = new ListingOtherRemovingHandler(this);
        this.unmappingHandler   = new ListingOtherUnmappingHandler(this);

        this.actions = {
            movingAction: this.movingHandler.run.bind(this.movingHandler),
            autoMappingAction: this.autoMappingHandler.run.bind(this.autoMappingHandler),
            removingAction: this.removingHandler.run.bind(this.removingHandler),
            unmappingAction: this.unmappingHandler.run.bind(this.unmappingHandler)
        };
    }

    //----------------------------------
});