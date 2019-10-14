DoubleCheck.grid.Items = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'doublecheck-grid-items';
    }
    Ext.applyIf(config, {
        url: DoubleCheck.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/item/getlist'
        },
        listeners: {
        },
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: 0,
            getRowClass: function (rec) {
                return !rec.data.active
                    ? 'doublecheck-grid-row-disabled'
                    : '';
            }
        },
        paging: true,
        remoteSort: true,
        autoHeight: true,
    });
    DoubleCheck.grid.Items.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(DoubleCheck.grid.Items, MODx.grid.Grid, {
    windows: {},

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = DoubleCheck.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    createItem: function (btn, e) {
        var w = MODx.load({
            xtype: 'doublecheck-item-window-create',
            id: Ext.id(),
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.reset();
        w.setValues({active: true});
        w.show(e.target);
    },

    showDoubles: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        else if (!this.menu.record) {
            return false;
        }
        let id = this.menu.record.id;

        let w = MODx.load({
            xtype: 'doublecheck-item-window-update',
            id: Ext.id(),
            record: {
                id: id
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.reset();
        w.show(e.target);
    },

    combineItem: function(btn, e) {
        const id = this.menu.record.id,
            pagetitle = this.menu.record.pagetitle;
        MODx.msg.confirm({
            title: 'Объединение',
            text: `Вы уверены что хотите выполнить объединение товаров? Главным товаром будет взят товар с id <b>${id}</b>`,
            url: this.config.url,
            params: {
                action: 'mgr/item/combine',
                id: id,
                pagetitle: pagetitle,
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
    },

    getFields: function () {
        return ['id', 'pagetitle', 'parent', 'count', 'actions'];
    },

    getColumns: function () {
        return [{
            header: _('doublecheck_item_id'),
            dataIndex: 'id',
            sortable: true,
            width: 70
        }, {
            header: _('doublecheck_item_pagetitle'),
            dataIndex: 'pagetitle',
            sortable: true,
            width: 250,
        }, {
            header: _('doublecheck_item_parent'),
            dataIndex: 'parent',
            sortable: true,
            width: 70,
        },{
            header: _('doublecheck_item_count'),
            dataIndex: 'count',
            sortable: false,
            width: 70,
        }, {
            header: _('doublecheck_grid_actions'),
            dataIndex: 'actions',
            renderer: DoubleCheck.utils.renderActions,
            sortable: false,
            width: 100,
            id: 'actions'
        }];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-magic"></i>&nbsp; Автоматически',
            handler: this.createItem,
            scope: this
        }];
    },

    onClick: function (e) {
        var elem = e.getTarget();
        if (elem.nodeName == 'BUTTON') {
            var row = this.getSelectionModel().getSelected();
            if (typeof(row) != 'undefined') {
                var action = elem.getAttribute('action');
                if (action == 'showMenu') {
                    var ri = this.getStore().find('id', row.id);
                    return this._showMenu(this, ri, e);
                }
                else if (typeof this[action] === 'function') {
                    this.menu.record = row.data;
                    return this[action](this, e);
                }
            }
        }
        return this.processEvent('click', e);
    },

    _getSelectedIds: function () {
        var ids = [];
        var selected = this.getSelectionModel().getSelections();

        for (var i in selected) {
            if (!selected.hasOwnProperty(i)) {
                continue;
            }
            ids.push(selected[i]['id']);
        }

        return ids;
    },
});
Ext.reg('doublecheck-grid-items', DoubleCheck.grid.Items);


DoubleCheck.grid.ItemsUpdateGrid = function (config) {
    config = config || {};
    let id = config.record.id;

    if (!config.id) {
        config.id = 'doublecheck-grid-itemsupdategrid';
    }
    Ext.applyIf(config, {
        url: DoubleCheck.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/item/update',
            id: id,
        },
        listeners: {
        },

        paging: true,
        remoteSort: true,
        autoHeight: true,
    });
    DoubleCheck.grid.ItemsUpdateGrid.superclass.constructor.call(this, config);
};
Ext.extend(DoubleCheck.grid.ItemsUpdateGrid, MODx.grid.Grid, {
    windows: {},

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = DoubleCheck.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },


    getFields: function () {
        return ['id', 'pagetitle', 'parent', 'cats'];
    },

    getColumns: function () {
        return [{
            header: 'ID',
            dataIndex: 'id',
            sortable: true,
            width: 70
        }, {
            header: 'Название',
            dataIndex: 'pagetitle',
            sortable: true,
            width: 200,
        }, {
            header: 'ID Категории',
            dataIndex: 'parent',
            sortable: true,
            width: 70,
        },{
            header: 'Дерево',
            dataIndex: 'cats',
            sortable: false,
            width: 200,
        }];
    },

});
Ext.reg('doublecheck-grid-itemsupdategrid', DoubleCheck.grid.ItemsUpdateGrid);
