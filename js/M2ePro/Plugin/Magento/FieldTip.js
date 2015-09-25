MagentoFieldTip = Class.create();
MagentoFieldTip.prototype = {

    // --------------------------------

    initialize: function()
    {
        this.isHideToolTip = false;
    },

    // --------------------------------

    onToolTipMouseLeave: function()
    {
        var self = MagentoFieldTipObj;
        var element = this;

        self.isHideToolTip = true;

        setTimeout(function() {
            self.isHideToolTip && element.hide();
        }, 1000);
    },

    onToolTipMouseEnter: function()
    {
        var self = MagentoFieldTipObj;
        self.isHideToolTip = false;
    },

    onToolTipIconMouseLeave: function()
    {
        var self = MagentoFieldTipObj;
        var element = this.up().select('.tool-tip-message')[0];

        self.isHideToolTip = true;

        setTimeout(function() {
            self.isHideToolTip && element.hide();
        }, 1000);
    },

    // --------------------------------

    showToolTip: function()
    {
        var self = MagentoFieldTipObj;

        self.isHideToolTip = false;

        $$('.tool-tip-message').each(function(element) {
            element.hide();
        });

        if (this.up().select('.tool-tip-message').length > 0) {
            self.changeToolTipPosition(this);
            this.up().select('.tool-tip-message')[0].show();

            return;
        }

        var isShowLeft = false;
        if (this.up().previous('td').select('p.note')[0].hasClassName('show-left')) {
            isShowLeft = true;
        }

        var tipText = this.up().previous('td').select('p.note')[0].innerHTML;
        var tipWidth = this.up().previous('td').select('p.note')[0].getWidth();
        if (tipWidth > 500) {
            tipWidth = 500;
        }

        var additionalClassName = 'tip-right';
        if (isShowLeft) {
            additionalClassName = 'tip-left';
        }

        var toolTipSpan = new Element('span', {
            'class': 'tool-tip-message ' + additionalClassName
        }).update(tipText).hide();

        if (isShowLeft) {
            toolTipSpan.style.width = tipWidth + 'px';
        }

        var imgUrl = M2ePro.url.get('m2epro_skin_url') + '/images/help.png';
        var toolTipImg = new Element('img', {
            'src': imgUrl
        });

        toolTipSpan.insert({top: toolTipImg});
        this.insert({after: toolTipSpan});

        self.changeToolTipPosition(this);

        toolTipSpan.show();

        toolTipSpan.observe('mouseout', self.onToolTipMouseLeave);
        toolTipSpan.observe('mouseover', self.onToolTipMouseEnter);
    },

    // --------------------------------

    changeToolTipPosition: function(element)
    {
        var toolTip = element.up().select('.tool-tip-message')[0];

        var settings = {
            setHeight: false,
            setWidth: false,
            setLeft: true,
            offsetTop: 25,
            offsetLeft: 0
        };

        if (element.up().getStyle('float') == 'right') {
            settings.offsetLeft += 18;
        }
        if (element.up().match('span')) {
            settings.offsetLeft += 15;
        }

        toolTip.clonePosition(element, settings);

        if (toolTip.hasClassName('tip-left')) {
            toolTip.style.left = (parseInt(toolTip.style.left) - toolTip.getWidth() - 10) + 'px';
        }
    }

    // --------------------------------
}