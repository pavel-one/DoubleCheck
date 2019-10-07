DoubleCheck.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'doublecheck-panel-home',
            renderTo: 'doublecheck-panel-home-div'
        }]
    });
    DoubleCheck.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(DoubleCheck.page.Home, MODx.Component);
Ext.reg('doublecheck-page-home', DoubleCheck.page.Home);