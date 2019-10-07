DoubleCheck.panel.Home = function (config) {
    config = config || {};
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        /*
         stateful: true,
         stateId: 'doublecheck-panel-home',
         stateEvents: ['tabchange'],
         getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};},
         */
        hideMode: 'offsets',
        items: [{
            html: '<h2>' + _('doublecheck') + '</h2>',
            cls: '',
            style: {margin: '15px 0'}
        }, {
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            border: true,
            hideMode: 'offsets',
            items: [{
                title: _('doublecheck_items'),
                layout: 'anchor',
                items: [{
                    html: _('doublecheck_intro_msg'),
                    cls: 'panel-desc',
                }, {
                    xtype: 'doublecheck-grid-items',
                    cls: 'main-wrapper',
                }]
            }]
        }]
    });
    DoubleCheck.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(DoubleCheck.panel.Home, MODx.Panel);
Ext.reg('doublecheck-panel-home', DoubleCheck.panel.Home);
