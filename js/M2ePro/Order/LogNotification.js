window.LogNotification = Class.create({

    // ---------------------------------------

    initialize: function() {
    },

    // ---------------------------------------

    skipLogToCurrentDate: function(url) {

        if (!confirm('Are you sure?')) {
            return;
        }

        new Ajax.Request(url, {
            method: 'post',
            parameters: {},
            onSuccess: function() {
                location.reload();
            }
        });

    }

    // ---------------------------------------
});