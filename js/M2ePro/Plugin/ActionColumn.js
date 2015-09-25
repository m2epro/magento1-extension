ActionColumn = Class.create();
ActionColumn.prototype = {

    //----------------------------------

    initialize: function(containerId) {},

    callAction: function(select, id)
    {
        if(!select.value || !select.value.isJSON()) {
            return;
        }

        var config = select.value.evalJSON();
        if (config.onclick_action) {
            var method = config.onclick_action + '(';
            if (id) {
                method = method + id;
            }
            method = method + ')';
            eval(method);
        } else {
            varienGridAction.execute(select);
        }
    }

    //----------------------------------
}

ActionColumnObj = new ActionColumn();