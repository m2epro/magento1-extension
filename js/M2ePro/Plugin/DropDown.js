DropDown = Class.create();
DropDown.prototype = {

    // --------------------------------

    initialize: function()
    {
        $$('.tab-item-link').each(function(el) {
            el.observe('click', this.hideItems);
        }.bind(this));
    },

    // --------------------------------

    observe: function()
    {
        var self = this;
        $$('.drop_down').each(function(node) {
            self.prepare(node);
        });
    },

    // --------------------------------

    prepare: function(node)
    {
        // we can't use just $(node) here, as we can have multiple elements with the same ID on the page because of floating toolbar
        // and we have to put the drop down only for that element, which is under the #anchor-content element
        var realNode = $$('#anchor-content #' + node.id)[0];

        var ulObj = $(node).select('ul')[0];
        if (typeof ulObj == 'undefined') {
            return;
        }

        var tempHtml = '<ul>';
        var tempStyle = ulObj.readAttribute('style') || '';

        $(ulObj).childElements().each(function(object) {
            tempHtml += '<li><a href="'+$(object).readAttribute('href')+'"';
            if ($(object).readAttribute('target') != null) {
                tempHtml += ' target="'+$(object).readAttribute('target')+'"';
            }
            if ($(object).readAttribute('onclick') != null) {
                tempHtml += ' onclick="'+$(object).readAttribute('onclick')+'"';
            }
            tempHtml +=  '><span>'+$(object).innerHTML+'</span></a></li>';
        });
        tempHtml += '</ul>';

        $(ulObj).remove();

        if (tempHtml != '') {
            tempHtml = '<div id="'+node.id+'_drop_down" class="drop_down_menu" style="'+tempStyle+'">' + tempHtml + '</div>';
            $(realNode).insert({ after: tempHtml });
        }

        $(realNode).observe('click', DropDownObj.toggleItems);
    },

    // --------------------------------

    toggleItems: function(event)
    {
        var tempId = this.id + '_drop_down';
        if ($(tempId).getStyle('display') == 'none') {
            var offset = $(this).cumulativeOffset();
            var x = offset.left;
            var y = offset.top + $(this).getHeight();

            $(tempId).setStyle({
                left    : x + 'px',
                top     : y + 'px',
                display : 'block'
            });

            var relatedObjectWidth = $(this).getWidth();
            var dropDownWidth = $(tempId).getWidth();

            if (dropDownWidth < relatedObjectWidth) {
                $(tempId).setStyle({
                    width: relatedObjectWidth + 'px'
                });
            }

            setTimeout(function() {
                $(document).observe('click', DropDownObj.hideItems);
            }, 100);

            Event.stop(event);
        } else {
            $(tempId).hide();
            $(document).stopObserving('click', DropDownObj.hideItems);
        }
    },

    hideItems: function()
    {
        $$('.drop_down_menu').each(Element.hide);

        $(document).stopObserving('click', DropDownObj.hideItems);
    }

    // --------------------------------
}