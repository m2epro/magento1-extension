window.BlockNotice = Class.create({

    storageKeys: {
        prefix: 'm2e_bn_',
        shown: '_was_shown',
        closed: '_closed',
        hiddenContent: '_hidden_content',
        expandedContent: '_expanded_content'
    },

    storageObj: null,

    // ---------------------------------------

    initialize: function(type)
    {
        this.type = type;
        this.isHideToolTip = false;
    },

    // ---------------------------------------

    getHashedStorage: function(id)
    {
        var hashedStorageKey = this.storageKeys.prefix + md5(id).substr(0, 10);
        var resultStorage = LocalStorageObj.get(hashedStorageKey);

        if (resultStorage === null) {
            return '';
        }

        return resultStorage;
    },

    setHashedStorage: function(id)
    {
        var hashedStorageKey = this.storageKeys.prefix + md5(id).substr(0, 10);
        LocalStorageObj.set(hashedStorageKey, 1);
    },

    deleteHashedStorage: function(id)
    {
        var hashedStorageKey = this.storageKeys.prefix + md5(id).substr(0, 10);

        LocalStorageObj.remove(hashedStorageKey);
        LocalStorageObj.remove(id);
    },

    deleteAllHashedStorage: function()
    {
        LocalStorageObj.removeAllByPrefix(this.storageKeys.prefix);
    },

    // ---------------------------------------

    show: function(id)
    {
        id = id || '';
        if (id == '') {
            return false;
        }
        $(id).show();
        return true;
    },

    hide: function(id)
    {
        id = id || '';
        if (id == '') {
            return false;
        }
        $(id).hide();
        return true;
    },

    // ---------------------------------------

    toggleContent: function(id)
    {
        return this.isClosedContent(id) ? this.showContent(id) : this.hideContent(id);
    },

    showContent: function(id)
    {
        var self = this;

        id = id || '';
        if (id == '') {
            return false;
        }

        $$('#'+id+' div.block_notices_content').each(function(object) {
            if (!object.visible()) {
                Effect.SlideDown(object, {duration:0.7});
            }
        });
        $$('#'+id+' div.block_notices_header div.block_notices_header_left span.arrow').each(function(object) {
            object.innerHTML = '&uarr;';
        });
        $$('#'+id+' div.block_notices_header div.block_notices_header_left a').each(function(object) {
            object.writeAttribute("onclick",self.type+'NoticeObj.hideContent(\'' + id + '\')');
        });

        this.deleteHashedStorage(id + this.storageKeys.closed);

        return true;
    },

    hideContent: function(id)
    {
        var self = this;

        id = id || '';
        if (id == '') {
            return false;
        }

        $$('#'+id+' div.block_notices_content').each(function(object) {
            if (object.visible()) {
                Effect.SlideUp(object, {duration:0.7});
            }
        });
        $$('#'+id+' div.block_notices_header div.block_notices_header_left span.arrow').each(function(object) {
            object.innerHTML = '&darr;';
        });
        $$('#'+id+' div.block_notices_header div.block_notices_header_left a').each(function(object) {
            object.writeAttribute("onclick",self.type+'NoticeObj.showContent(\'' + id + '\')');
        });

        this.setHashedStorage(id + this.storageKeys.closed);

        return true;
    },

    // ---------------------------------------

    showBlock: function(id)
    {
        id = id || '';
        if (id == '') {
            return false;
        }
        $(id).show();
        this.deleteHashedStorage(id + this.storageKeys.hiddenContent);
        return true;
    },

    hideBlock: function(id)
    {
        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return false;
        }

        id = id || '';
        if (id == '') {
            return false;
        }
        $(id).remove();
        this.setHashedStorage(id + this.storageKeys.hiddenContent);
        return true;
    },

    // ---------------------------------------

    remove: function(id)
    {
        id = id || '';
        if (id == '') {
            return false;
        }
        $(id).remove();
        return true;
    },

    clear: function(id)
    {
        id = id || '';
        if (id == '') {
            return false;
        }
        $(id).innerHTML = '';
        return true;
    },

    // ---------------------------------------

    getPreparedId: function(object)
    {
        var id = object.readAttribute('id');
        if (typeof id != 'string') {
            id = 'block_notice_md5_' + md5(object.innerHTML.replace(/[^A-Za-z]/g,''));
            object.writeAttribute('id',id);
        }
        return id;
    },

    getPreparedTitle: function(object)
    {
        var title = object.readAttribute('title');
        if (typeof title != 'string') {
            title = '';
        }
        object.writeAttribute('title','');
        return title;
    },

    getPreparedSubTitle: function(object)
    {
        var subtitle = object.readAttribute('subtitle');
        if (typeof subtitle != 'string') {
            subtitle = '['+M2ePro.translator.translate('Help')+']';
        }
        object.writeAttribute('subtitle','');
        return subtitle;
    },

    getPreparedContent: function(object)
    {
        var content = object.readAttribute('content');
        if (typeof content != 'string') {
            content = '';
        }
        object.writeAttribute('content','');
        return content;
    },

    getPreparedCollapseable: function(object)
    {
        var collapseable = object.readAttribute('collapseable');
        object.writeAttribute('collapseable','');

        if (typeof collapseable != 'string') {
            return true;
        }

        return collapseable != 'no';
    },

    getPreparedHideBlock: function(object)
    {
        var hideblock = object.readAttribute('hideblock');
        object.writeAttribute('hideblock','');

        if (typeof hideblock != 'string') {
            return true;
        }

        return hideblock != 'no';
    },

    getPreparedAlwaysShow: function(object)
    {
        var alwaysShow = object.readAttribute('always_show');
        object.writeAttribute('always_show','');

        if (typeof alwaysShow != 'string') {
            return false;
        }

        return alwaysShow != 'no';
    },

    // ---------------------------------------

    getHeaderHtml: function(id,title,subtitle,collapseable,hideblock)
    {
        var isClosedContent = this.getHashedStorage(id + this.storageKeys.closed);
        if (BLOCK_NOTICES_DISABLE_COLLAPSE) {
            isClosedContent = 0;
        }

        var titleHtml = '';
        if (title != '') {
            titleHtml = '<span class="title">'+title+'</span>';
        }

        var subtitleHtml = '';
        if (subtitle != '') {
            subtitleHtml = '<span class="subtitle">'+subtitle+'</span>';
        }

        var arrowHtml = '';
        if (collapseable) {
            if (isClosedContent == '1') {
                arrowHtml = '<span class="arrow">&darr;</span>';
            } else {
                arrowHtml = '<span class="arrow">&uarr;</span>';
            }
        }

        var hideBlockHtml = '';
        if (hideblock) {
            var tempOnClick = this.type+'NoticeObj.hideBlock(\'' + id + '\')';
            hideBlockHtml = '<a href="javascript:void(0);" onclick="' + tempOnClick + '" title="'+M2ePro.translator.translate('Hide Block')+'"><span class="hideblock">&times;</span></a>';
        }

        if (titleHtml == '' && subtitleHtml == '' && arrowHtml == '' && hideBlockHtml == '') {
            return '';
        }

        var leftHtml = titleHtml + '&nbsp;&nbsp;' + subtitleHtml + '&nbsp;&nbsp;' + arrowHtml;
        if (collapseable) {
            var tempOnClick = this.type+'NoticeObj.hideContent(\'' + id + '\')';
            if (isClosedContent == '1') {
                tempOnClick = this.type+'NoticeObj.showContent(\'' + id + '\')';
            }
            leftHtml = '<a href="javascript:void(0);" onclick="' + tempOnClick + '">' + leftHtml + '</a>';
        }

        var rightHtml = hideBlockHtml;

        return '<div class="block_notices_header">' +
                    '<div class="block_notices_header_left">' +
                        leftHtml +
                    '</div>' +
                    '<div class="block_notices_header_right">' +
                        rightHtml +
                    '</div>' +
                    '<div style="clear: both;"></div>' +
                '</div>';
    },

    getContentHtml: function(id,content,collapseable)
    {
        var isClosedContent = this.getHashedStorage(id + this.storageKeys.closed);
        if (BLOCK_NOTICES_DISABLE_COLLAPSE) {
            isClosedContent = 0;
        }

        var contentHtml = '';
        if (collapseable && isClosedContent == '1') {
            contentHtml = '<div class="block_notices_content" style="display: none;">';
        } else {
            contentHtml = '<div class="block_notices_content">';
        }
        contentHtml = contentHtml + '<div>' + content + '</div></div>';

        return contentHtml;
    },

    getFinalHtml: function(headerHtml,contentHtml)
    {
        if (headerHtml == '') {
            return contentHtml;
        }

        var search = '<div class="block_notices_content" style="';
        var replace = '<div class="block_notices_content" style="margin-top: 5px;';

        var tempBefore = contentHtml;
        contentHtml = contentHtml.replace(search,replace);
        var tempAfter = contentHtml;

        if (tempBefore == tempAfter) {
            search = '<div class="block_notices_content"';
            replace = '<div class="block_notices_content" style="margin-top: 5px;"';
            contentHtml = contentHtml.replace(search,replace);
        }

        return headerHtml + '<div style="clear: both;"></div>' + contentHtml;
    },

    setObjectState: function(object)
    {
        var id = object.id;
        var isHideBlock     = this.getHashedStorage(id + this.storageKeys.hiddenContent) == 1;
        var isClosedContent = this.getHashedStorage(id + this.storageKeys.closed) == 1;

        if (!isHideBlock && !isClosedContent) {
            this.setHashedStorage(id + this.storageKeys.closed);
        }
    },

    // ---------------------------------------

    showNoticeToolTip: function(element)
    {
        $$('.tool-tip-message').invoke('hide');

        var settings = {
            setHeight: false,
            setWidth: false,
            setLeft: true,
            offsetTop: 20,
            offsetLeft: 10
        };

        var toolTipMessage = element.next();
        toolTipMessage.clonePosition(element, settings);
        toolTipMessage.show();
    },

    // ---------------------------------------

    onClickNoticeToolTip: function(event)
    {
        Event.stop(event);
    },

    onToolTipIconMouseEnter: function(element)
    {
        var self = ModuleNoticeObj;
        self.isHideToolTip = false;

        self.showNoticeToolTip(element);
    },

    onToolTipIconMouseLeave: function(element)
    {
        var self = ModuleNoticeObj;
        self.isHideToolTip = true;

        setTimeout(function() {
            self.isHideToolTip && element.next().hide();
        }, 1000);
    },

    onToolTipMouseEnter: function()
    {
        var self = ModuleNoticeObj;
        self.isHideToolTip = false;
    },

    onToolTipMouseLeave: function(element)
    {
        var self = ModuleNoticeObj;
        self.isHideToolTip = true;

        setTimeout(function() {
            self.isHideToolTip && element.hide();
        }, 1000);
    },

    // ---------------------------------------

    collapseHelpBlockIntoIcon: function(object)
    {
        if (this.getHashedStorage(object.id + this.storageKeys.closed) != 1 || object.hasClassName('no-icon')) {
            return false;
        }

        if ($(object.id + '_tooltip_icon')) {
            return true;
        }

        var parentContainer = object;
        while (!parentContainer.hasClassName('entry-edit')
               && parentContainer.id != 'page:main-container'
               && !parentContainer.hasClassName('popup-window')) {

            parentContainer = parentContainer.up();

            try {
                parentContainer.hasClassName('entry-edit');
            } catch (e) {
                return false;
            }
        }

        if ((parentContainer.id == 'page:main-container' && !$(object.getAttribute('help_icon_dest_id')))
            || parentContainer.hasClassName('popup-window')) {

            return false;
        }

        var toolTipIconSpan = new Element('span', {
            'id': object.id + '_tooltip_icon',
            'class': 'notice-tool-tip-icon',
            'onmouseover': 'ModuleNoticeObj.onToolTipIconMouseEnter(this);',
            'onmouseout': 'ModuleNoticeObj.onToolTipIconMouseLeave(this);'
        });

        var toolTipMessageSpan = new Element('span', {
            'class': 'tool-tip-message',
            'onclick': 'ModuleNoticeObj.onClickNoticeToolTip(event);',
            'onmouseover': 'ModuleNoticeObj.onToolTipMouseEnter(this);',
            'onmouseout': 'ModuleNoticeObj.onToolTipMouseLeave(this);'
        }).update(object.innerHTML);
        toolTipMessageSpan.hide();

        var imgUrl = M2ePro.url.get('m2epro_skin_url') + '/images/help.png';
        var toolTipImg = new Element('img', {
            'src': imgUrl
        });

        toolTipMessageSpan.insert({top: toolTipImg});

        if ($(object.getAttribute('help_icon_dest_id'))) {
            $(object.getAttribute('help_icon_dest_id')).insert({bottom: toolTipIconSpan});
            return true;
        }

        if (!parentContainer.hasClassName('entry-edit')) {
            return true;
        }

        if (parentContainer.select('.icon-head').length > 0) {
            parentContainer.select('.icon-head')[0].insert({after: toolTipIconSpan});
            toolTipIconSpan.insert({after: toolTipMessageSpan});
            return true;
        }

        return false;
    },

    // ---------------------------------------

    observeModulePrepareStart: function(object)
    {
        if (object.hasClassName('is_prepared')) {
            return;
        }

        object.addClassName('is_prepared');

        var id           = this.getPreparedId(object);
        var title        = this.getPreparedTitle(object);
        var subtitle     = this.getPreparedSubTitle(object);
        var collapseable = this.getPreparedCollapseable(object);
        var hideblock    = this.getPreparedHideBlock(object);
        var alwaysShow   = this.getPreparedAlwaysShow(object);

        if ((!alwaysShow && !BLOCK_NOTICES_SHOW) || (hideblock && this.getHashedStorage(id + this.storageKeys.hiddenContent) == '1')) {
            object.remove();
            return;
        }

        if ((IS_VIEW_INTEGRATION || IS_VIEW_WIZARD || IS_VIEW_CONFIGURATION) && !BLOCK_NOTICES_DISABLE_COLLAPSE
            && !alwaysShow && this.collapseHelpBlockIntoIcon(object)) {

            object.remove();
            return;
        }

        var headerHtml   = this.getHeaderHtml(id,title,subtitle,collapseable,hideblock);
        var contentHtml  = this.getContentHtml(id,object.innerHTML,collapseable);
        object.innerHTML = this.getFinalHtml(headerHtml,contentHtml);

        object.removeClassName('block_notices_module');
        object.addClassName('block_notices');

        if (this.getHashedStorage(id + this.storageKeys.expandedContent) != 1) {
            this.setObjectState(object);
            this.setHashedStorage(id + this.storageKeys.expandedContent);
        }
    },

    // ---------------------------------------

    isClosedContent: function(id)
    {
        return this.getHashedStorage(id + this.storageKeys.closed) == '1';
    },

    isHiddenBlock: function(id)
    {
        return this.getHashedStorage(id + this.storageKeys.hiddenContent) == '1';
    },

    // ---------------------------------------

    prepareCollapseableArrow: function(id)
    {
        var arrow = this.isClosedContent(id) ? '&darr;' : '&uarr;';
        $$('#'+id+' div.block_notices_header div.block_notices_header_left span.arrow').each(function(object) {
            object.innerHTML = arrow;
        });
    }

    // ---------------------------------------
});