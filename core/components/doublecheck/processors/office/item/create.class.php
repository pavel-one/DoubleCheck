<?php

class DoubleCheckOfficeItemCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'DoubleCheckItem';
    public $classKey = 'DoubleCheckItem';
    public $languageTopics = ['doublecheck'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('doublecheck_item_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('doublecheck_item_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'DoubleCheckOfficeItemCreateProcessor';