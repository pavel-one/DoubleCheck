var DoubleCheck = function (config) {
    config = config || {};
    DoubleCheck.superclass.constructor.call(this, config);
};
Ext.extend(DoubleCheck, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('doublecheck', DoubleCheck);

DoubleCheck = new DoubleCheck();