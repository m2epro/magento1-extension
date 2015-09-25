MagentoBlock = Class.create();
MagentoBlock.prototype = {

    // --------------------------------

    initialize: function() {},

    // --------------------------------

    getHashedCookie: function(id)
    {
        var hashedCookieKey = 'm2e_mb_' + md5(id).substr(0, 10);
        var notHashedCookie = getCookie(id);
        var resultCookie = null;

        if (notHashedCookie !== "") {
            deleteCookie(id, '/', '');
            this.setHashedCookie(id);
            resultCookie = notHashedCookie;
        } else {
            resultCookie = getCookie(hashedCookieKey);
        }

        return resultCookie;
    },

    setHashedCookie: function(id)
    {
        var hashedCookieKey = 'm2e_mb_' + md5(id).substr(0, 10);
        setCookie(hashedCookieKey, 1, 3*365, '/');
    },

    deleteHashedCookie: function(id)
    {
        var hashedCookieKey = 'm2e_mb_' + md5(id).substr(0, 10);

        deleteCookie(hashedCookieKey, '/', '');
        deleteCookie(id, '/', '');
    },

    // --------------------------------

    show: function(blockClass,init)
    {
        blockClass = blockClass || '';
        if (blockClass == '') {
            return false;
        }

        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right div.block_visibility_changer').each(function(o) {
            o.remove();
        });
        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right div.block_tips_changer').each(function(o) {
            o.show();
        });

        var tempObj = $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-left')[0];
        tempObj.writeAttribute("onclick", "MagentoBlockObj.hide('"+blockClass+"','0');");

        var tempHtml = $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right')[0].innerHTML;
        var tempHtml2 = '<div class="block_visibility_changer collapseable" style="float: right; color: white; font-size: 11px; margin-left: 20px;">';
        tempHtml2 += '<a href="javascript:void(0);" onclick="MagentoBlockObj.hide(\''+blockClass+'\',\'0\');" style="width: 20px; border: 0px;" class="open">&nbsp;</a>';
        tempHtml2 += '</div>';
        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right')[0].innerHTML = tempHtml2 + tempHtml;

        this.deleteHashedCookie(blockClass);

        if (init == '0') {
            $$('div.'+blockClass+' div.fieldset')[0].show();
        } else {
            $$('div.'+blockClass+' div.fieldset')[0].show();
        }

        $$('div.'+blockClass+' div.entry-edit-head')[0].setStyle({marginBottom: '0px'});
        $$('div.'+blockClass+' div.fieldset')[0].setStyle({marginBottom: '15px'});

        return true;
    },

    hide: function(blockClass,init)
    {
        blockClass = blockClass || '';
        if (blockClass == '') {
            return false;
        }

        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right div.block_visibility_changer').each(function(o) {
            o.remove();
        });
        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right div.block_tips_changer').each(function(o) {
            o.hide();
        });

        var tempObj = $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-left')[0];
        tempObj.writeAttribute("onclick", "MagentoBlockObj.show('"+blockClass+"','0');");

        var tempHtml = $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right')[0].innerHTML;
        var tempHtml2 = '<div class="block_visibility_changer collapseable" style="float: right; color: white; font-size: 11px; margin-left: 20px;">';
        tempHtml2 += '<a href="javascript:void(0);" onclick="MagentoBlockObj.show(\''+blockClass+'\',\'0\');" style="width: 20px; border: 0px;">&nbsp;</a>';
        tempHtml2 += '</div>';
        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right')[0].innerHTML = tempHtml2 + tempHtml;

        this.setHashedCookie(blockClass);

        if (init == '0') {
            $$('div.'+blockClass+' div.fieldset')[0].hide();
        } else {
            $$('div.'+blockClass+' div.fieldset')[0].hide();
        }

        $$('div.'+blockClass+' div.entry-edit-head')[0].setStyle({marginBottom: '15px'});
        $$('div.'+blockClass+' div.fieldset')[0].setStyle({marginBottom: '0px'});

        return true;
    },

    // --------------------------------

    observePrepareStart: function(blockObj)
    {
        var self = this;

        var tempCollapseable = blockObj.readAttribute('collapseable');
        if (typeof tempCollapseable == 'string' && tempCollapseable == 'no') {
            return;
        }

        var tempId = blockObj.readAttribute('id');
        if (typeof tempId != 'string') {
            tempId = 'magento_block_md5_' + md5(blockObj.innerHTML.replace(/[^A-Za-z]/g,''));
            blockObj.writeAttribute("id",tempId);
        }

        var blockClass = tempId + '_hide';
        blockObj.addClassName(blockClass);

        var tempObj = blockObj.select('div.entry-edit-head div.entry-edit-head-left')[0];
        tempObj.setStyle({cursor: 'pointer'});

        var isClosed = this.getHashedCookie(blockClass);

        if (isClosed == '' || isClosed == '0') {
            self.show(blockClass,'1');
        } else {
            self.hide(blockClass,'1');
        }
    }

    // --------------------------------
}