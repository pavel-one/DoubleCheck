Ext.onReady(function () {
    DoubleCheck.config.connector_url = OfficeConfig.actionUrl;

    var grid = new DoubleCheck.panel.Home();
    grid.render('office-doublecheck-wrapper');

    var preloader = document.getElementById('office-preloader');
    if (preloader) {
        preloader.parentNode.removeChild(preloader);
    }
});