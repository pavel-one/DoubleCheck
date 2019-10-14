DoubleCheck.window.UpdateItem = function (config) {
    config = config || {};

    let id = config.record.id;

    if (!config.id) {
        config.id = 'doublecheck-item-window-update';
    }
    Ext.apply(config, {
        title: 'Дубли товаров',
        width: 600,


        items: [{
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            items: [{
                title: 'Дубли товаров',
                layout: 'anchor',
                items: [{
                    html: 'Тут показаны дубли текущего товара (включая этот самый товар)',
                    cls: 'panel-desc',
                }, {
                    xtype: 'doublecheck-grid-itemsupdategrid',
                    cls: 'main-wrapper',
                    record: {
                        id: id
                    }
                }]
            }]
        }]
    });
    DoubleCheck.window.UpdateItem.superclass.constructor.call(this, config);
};
Ext.extend(DoubleCheck.window.UpdateItem, MODx.Window, {

});
Ext.reg('doublecheck-item-window-update', DoubleCheck.window.UpdateItem);