<?php

/**
 * The home manager controller for DoubleCheck.
 *
 */
class DoubleCheckHomeManagerController extends modExtraManagerController
{
    /** @var DoubleCheck $DoubleCheck */
    public $DoubleCheck;


    /**
     *
     */
    public function initialize()
    {
        $this->DoubleCheck = $this->modx->getService('DoubleCheck', 'DoubleCheck', MODX_CORE_PATH . 'components/doublecheck/model/');
        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['doublecheck:default'];
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('doublecheck');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->DoubleCheck->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->DoubleCheck->config['jsUrl'] . 'mgr/doublecheck.js');
        $this->addJavascript($this->DoubleCheck->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->DoubleCheck->config['jsUrl'] . 'mgr/misc/combo.js');
        $this->addJavascript($this->DoubleCheck->config['jsUrl'] . 'mgr/widgets/items.grid.js');
        $this->addJavascript($this->DoubleCheck->config['jsUrl'] . 'mgr/widgets/items.windows.js');
        $this->addJavascript($this->DoubleCheck->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->DoubleCheck->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addHtml('<script type="text/javascript">
        DoubleCheck.config = ' . json_encode($this->DoubleCheck->config) . ';
        DoubleCheck.config.connector_url = "' . $this->DoubleCheck->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "doublecheck-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .= '<div id="doublecheck-panel-home-div"></div>';

        return '';
    }
}