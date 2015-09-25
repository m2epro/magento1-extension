EbayListingTransferringBreadcrumbHandler = Class.create();
EbayListingTransferringBreadcrumbHandler.prototype = {

    // --------------------------------

    initialize: function() {},

    // --------------------------------

    clear: function() {},

    // --------------------------------

    highlightStep: function(stepName)
    {
        $$('div[id^=step_]').each(function(el) {el.removeClassName('selected');});
        $('step_' + stepName) && $('step_' + stepName).addClassName('selected');
        $('breadcrumb_container') && $('breadcrumb_container').show();
    },

    hideAll: function()
    {
        $('breadcrumb_container') && $('breadcrumb_container').hide();
    },

    showSteps: function(steps)
    {
        var selectorSeparator = '.left .space';
        ['destination', 'policy', 'translation', 'categories'].forEach(function(el) {
            var breadcrumb = $('step_' + el);
            if (breadcrumb) {
                if (steps.indexOf(el) != -1) {
                    breadcrumb.show();
                    if (breadcrumb.previous(selectorSeparator)) {
                        breadcrumb.previous(selectorSeparator).show();
                    } else if (breadcrumb.next(selectorSeparator)) {
                        breadcrumb.next(selectorSeparator).show();
                    }
                } else {
                    breadcrumb.hide();
                    if (breadcrumb.previous(selectorSeparator)) {
                        breadcrumb.previous(selectorSeparator).hide();
                    } else if (breadcrumb.next(selectorSeparator)) {
                        breadcrumb.next(selectorSeparator).hide();
                    }
                }
            }
        });
        this.setWidthStep();
    },

    setWidthStep: function()
    {
        var showedBreadcrumbSteps = $$('div[id^=step_]').findAll(function(el) {return el.visible();});
        var showedBreadcrumbSeparators = $$('.left .space').findAll(function(el) {return el.visible();});
        var stepWidth = (100 - showedBreadcrumbSeparators.length * 0.3) / showedBreadcrumbSteps.length;
        var length = showedBreadcrumbSteps.length;
        for (var i = 0; i < length; i++) {
            showedBreadcrumbSteps[i].setStyle({width : stepWidth + '%'});
            showedBreadcrumbSteps[i].select('.transferring_step_number')[0].innerHTML = '' + (i + 1);
        }
    }

    // --------------------------------
};