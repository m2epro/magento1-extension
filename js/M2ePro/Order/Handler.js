OrderHandler = Class.create();
OrderHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(gridIds)
    {
        this.gridIds = gridIds ? eval(gridIds) : [];
    },

    initializeGrids: function()
    {
        var self = OrderHandlerObj;

        for (var i = 0; i < self.gridIds.length; i++) {
            var currentGridId = self.gridIds[i];

            var tempGrid = window[currentGridId + 'JsObject'];
            if (!(tempGrid instanceof varienGrid)) {
                continue;
            }

            if (typeof self[currentGridId] != 'undefined') {
                // already initialized
                continue;
            }

            self[currentGridId] = tempGrid.rowClickCallback;
            tempGrid.rowClickCallback = self.gridRowClickCallback;
        }
    },

    disableGridCallback: function(gridId)
    {
        var tempGrid = window[gridId + 'JsObject'];

        if (!(tempGrid instanceof varienGrid)) {
            return;
        }

        tempGrid.rowClickCallback = '';
    },

    restoreGridCallback: function(gridId)
    {
        var self = OrderHandlerObj;
        var tempGrid = window[gridId + 'JsObject'];

        if (!(tempGrid instanceof varienGrid)) {
            return;
        }

        tempGrid.rowClickCallback = self.gridRowClickCallback;
    },

    gridRowClickCallback: function(grid, event)
    {
        if(['a', 'select', 'option'].indexOf(Event.element(event).tagName.toLowerCase())!=-1) {
            return;
        }

        var self = OrderHandlerObj;
        var trElement = Event.findElement(event, 'tr');
        var tdElement = Event.findElement(event, 'td');

        if ($(tdElement).down('input')) {
            self[grid.containerId](grid, event);
        } else {
            setLocation(trElement.title);
        }
    },

    //----------------------------------

    viewOrderHelp: function(rowId, data)
    {
        var row = $('grid_help_icon_open_' + rowId).up('tr');
        var grid = row.up('table');
        var gridId = grid.id.replace('_table', '');

        OrderHandlerObj.disableGridCallback(gridId);

        $('grid_help_icon_open_'+rowId).hide();
        $('grid_help_icon_close_'+rowId).show();

        if ($('grid_help_content_'+rowId) !== null) {
            $('grid_help_content_'+rowId).show();

            // Restore grid callback
            // ------------------------------
            setTimeout(function() {
                OrderHandlerObj.restoreGridCallback(gridId);
            },150);
            // ------------------------------
            return;
        }

        var html = OrderHandlerObj.createHelpTitleHtml(rowId);

        data = eval(base64_decode(data));
        for (var i=0;i<data.length;i++) {
            html += OrderHandlerObj.createHelpActionHtml(data[i]);
        }

        html += OrderHandlerObj.createHelpViewAllLogHtml(rowId, gridId);

        row.insert({
            after: '<tr id="grid_help_content_'+rowId+'"><td class="help_line" colspan="'+($(row).childElements().length)+'">'+html+'</td></tr>'
        });

        setTimeout(function() {
            OrderHandlerObj.restoreGridCallback(gridId);
        },150);
    },

    hideOrderHelp: function(rowId)
    {
        var row = $('grid_help_icon_open_' + rowId).up('tr');
        var grid = row.up('table');
        var gridId = grid.id.replace('_table', '');

        OrderHandlerObj.disableGridCallback(gridId);

        if ($('grid_help_content_'+rowId) != null) {
            $('grid_help_content_'+rowId).hide();
        }

        $('grid_help_icon_open_'+rowId).show();
        $('grid_help_icon_close_'+rowId).hide();

        setTimeout(function() {
            OrderHandlerObj.restoreGridCallback(gridId);
        },150);
    },

    createHelpTitleHtml: function(rowId)
    {
        var nativeOrderNumber = $('grid_help_icon_open_' + rowId).up('td').next().innerHTML;
        var orderTitle = nativeOrderNumber.replace(/<[^>]+>/g, '');
        var closeHtml = '<a href="javascript:void(0);" onclick="OrderHandlerObj.hideOrderHelp('+rowId+');" title="'+ M2ePro.translator.translate('Close')+'"><span class="hl_close">&times;</span></a>';

        return '<div class="hl_header"><span class="hl_title">'+orderTitle+'</span>'+closeHtml+'</div>';
    },

    createHelpActionHtml: function(action)
    {
        var classContainer = 'hl_container';
        if (action.type == M2ePro.php.constant('Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS')) {
            classContainer += ' hl_container_success';
        } else if (action.type == M2ePro.php.constant('Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING')) {
            classContainer += ' hl_container_warning';
        } else if (action.type == M2ePro.php.constant('Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE')) {
            classContainer += ' hl_container_notice';
        } else if (action.type == M2ePro.php.constant('Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR')) {
            classContainer += ' hl_container_error';
        }

        var type = '<span style="color: green;">'+ M2ePro.translator.translate('Success')+'</span>';
        if (action.type == M2ePro.php.constant('Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE')) {
            type = '<span style="color: blue;">'+ M2ePro.translator.translate('Notice')+'</span>';
        } else if (action.type == M2ePro.php.constant('Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING')) {
            type = '<span style="color: orange;">'+ M2ePro.translator.translate('Warning')+'</span>';
        } else if (action.type == M2ePro.php.constant('Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR')) {
            type = '<span style="color: red;">'+ M2ePro.translator.translate('Error')+'</span>';
        }

        var html = '<div class="'+classContainer+'">';

        html += '<div class="hl_date">'+action.date+'</div>';

        if (action.initiator != '') {
            html += '<div class="hl_action">' +
                '<strong style="color: gray;">'+action.initiator+'</strong>&nbsp;&nbsp;' +
                '</div>';
        }

        html += '<div style="clear: both"></div>';

        html += '<div style="padding-top: 3px;"><div style="margin-top: 7px;">';
        html += '<div class="hl_messages_type">'+type+'</div><div class="hl_messages_text">'+action.text+'</div>';
        html += '</div></div>';

        html += '</div>';

        return html;
    },

    createHelpViewAllLogHtml: function(rowId, gridId)
    {
        var url = '';
        if (gridId.match(/ebay/i)) {
            url = M2ePro.url.get('adminhtml_ebay_log/order', {order_id: rowId});
        } else {
            url = M2ePro.url.get('adminhtml_common_log/order', {order_id: rowId});
        }

        return '<div class="hl_footer"><a target="_blank" href="'+url+'">'+ M2ePro.translator.translate('View All Order Logs.')+'</a></div>';
    }

    //----------------------------------
});