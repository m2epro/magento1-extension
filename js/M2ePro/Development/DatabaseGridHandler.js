DevelopmentDatabaseGridHandler = Class.create(GridHandler, {

    // ---------------------------------------

    mergeModeCookieKey: 'database_tables_merge_mode_cookie_key',

    // ---------------------------------------

    prepareActions: function()
    {
        this.actions = {
            deleteTableRowsAction: function(id) { this.deleteTableRows(id); }.bind(this),
            updateTableCellsAction: function() { this.openTableCellsPopup('update'); }.bind(this)
        }
    },

    // ---------------------------------------

    switchMergeMode: function()
    {
        this.isMergeModeEnabled() ? this.setMergeMode('0') : this.setMergeMode('1');
        window.location = M2ePro.url.get('adminhtml_development_database/manageTable');
    },

    isMergeModeEnabled: function()
    {
        var cookieValue = getCookie(this.mergeModeCookieKey);
        return cookieValue != null && cookieValue != '0';
    },

    setMergeMode: function(value)
    {
        setCookie(this.mergeModeCookieKey, value, 3*365, '/');
    },

    // ---------------------------------------

    mergeParentTable: function(component)
    {
        this.setMergeMode('1');
        window.location = M2ePro.url.get('adminhtml_development_database/manageTable', {component: component});
    },

    // ---------------------------------------

    deleteTableRows: function(id)
    {
        var selectedIds = id ? id : DevelopmentDatabaseGridHandlerObj.getSelectedProductsString();

        if (id && !confirm('Are you sure?')) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_development_database/deleteTableRows'), {
            method:'post',
            parameters: {
                ids: selectedIds
            },
            onSuccess: function(transport) {
                DevelopmentDatabaseGridHandlerObj.getGridObj().reload()
            }
        });
    },

    openTableCellsPopup: function(mode)
    {
        var self = DevelopmentDatabaseGridHandlerObj;

        var popupTitle = mode == 'update' ? 'Edit Table Records' : 'Add Table Row';

        var popup = new Window({
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: popupTitle,
            top: 50,
            width: 635,
            minHeight: 200,
            maxHeight: 445,
            zIndex: 100,
            destroyOnClose: true,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        new Ajax.Request(M2ePro.url.get('adminhtml_development_database/getTableCellsPopupHtml'), {
            method: 'post',
            parameters: {
                ids: self.getSelectedProductsString(),
                mode: mode
            },
            onSuccess: function(transport) {
                popup.getContent().insert(transport.responseText);
            }
        });

        popup.showCenter(true);
    },

    confirmUpdateCells: function()
    {
        if (!DevelopmentDatabaseGridHandlerObj.isAnySwitcherEnabled()) {
            alert('You should select columns.');
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_development_database/updateTableCells'), {
            method: 'post',
            asynchronous: false,
            parameters: Form.serialize($('development_tabs_database_table_cells_popup_form')),
            onSuccess: function(transport) {
                DevelopmentDatabaseGridHandlerObj.unselectAllAndReload();
                Windows.getFocusedWindow().close();
            }
        });
    },

    confirmAddRow: function()
    {
        if (!DevelopmentDatabaseGridHandlerObj.isAnySwitcherEnabled()) {
            alert('You should select columns.');
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_development_database/addTableRow'), {
            method: 'post',
            asynchronous: false,
            parameters: Form.serialize($('development_tabs_database_table_cells_popup_form')),
            onSuccess: function(transport) {
                DevelopmentDatabaseGridHandlerObj.getGridObj().reload();
                Windows.getFocusedWindow().close();
            }
        });
    },

    // ---------------------------------------

    mouseOverCell: function(cellId)
    {
        if ($(cellId + '_save_link').getStyle('display') != 'none') {
            return;
        }

        $(cellId + '_edit_link').show();
        $(cellId + '_view_link').hide();
        $(cellId + '_save_link').hide();
    },

    mouseOutCell: function(cellId)
    {
        if ($(cellId + '_save_link').getStyle('display') != 'none') {
            return;
        }

        $(cellId + '_edit_link').hide();
        $(cellId + '_view_link').hide();
        $(cellId + '_save_link').hide();
    },

    // ---------------------------------------

    switchCellToView: function(cellId)
    {
        $(cellId + '_edit_link').show();
        $(cellId + '_view_link').hide();
        $(cellId + '_save_link').hide();

        $(cellId + '_edit_container').hide();
        $(cellId + '_view_container').show();
    },

    switchCellToEdit: function(cellId)
    {
        $(cellId + '_edit_link').hide();
        $(cellId + '_view_link').show();
        $(cellId + '_save_link').show();

        $(cellId + '_edit_container').show();
        $(cellId + '_view_container').hide();
    },

    saveTableCell: function(rowId, columnName)
    {
        var params = {
            ids: rowId,
            cells: columnName
        };

        var cellId = 'table_row_cell_' + columnName + '_' + rowId;
        params['value_'+ columnName] = $(cellId + '_edit_input').value;

        new Ajax.Request(M2ePro.url.get('adminhtml_development_database/updateTableCells'), {
            method: 'post',
            asynchronous: false,
            parameters: params,
            onSuccess: function(transport) {
                DevelopmentDatabaseGridHandlerObj.switchCellToView(cellId);
                DevelopmentDatabaseGridHandlerObj.getGridObj().reload();
            }
        });
    },

    onKeyDownEdit: function(rowId, columnName, event)
    {
        if (event.keyCode != 13) {
            return false;
        }

        DevelopmentDatabaseGridHandlerObj.saveTableCell(rowId, columnName);
        return false;
    },

    // ---------------------------------------

    switcherStateChange: function()
    {
        var inputElement = $(this.id.replace('switcher','input'));

        inputElement.removeAttribute('disabled');

        if (!this.checked) {
            inputElement.value = '';
            inputElement.setAttribute('disabled', 'disabled');
        }
    },

    isAnySwitcherEnabled: function()
    {
        var result = false;

        $$('#development_tabs_database_table_cells_popup .input_switcher').each(function(el) {
            if (el.checked) {
                result = true;
                return true;
            }
        });

        return result;
    }

    // ---------------------------------------
});