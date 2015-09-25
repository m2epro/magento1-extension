var PhpHandler = Class.create({

    //----------------------------------

    constants: {},

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    setConstants: function(constants, className)
    {
        var prefix = className ? (className + '::') : '';

        constants.each((function(constant) {
            this.constants[prefix + constant[0]] = constant[1];
        }).bind(this));

        return this;
    },

    constant: function(name)
    {
        if (typeof this.constants[name] == 'undefined') {
            return alert('Constant "'+ name +'" not found');
        }

        return this.constants[name];
    }

    //----------------------------------
});