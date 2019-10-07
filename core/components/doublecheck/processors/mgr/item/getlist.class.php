<?php

class DoubleCheckItemGetListProcessor extends modProcessor
{
    /** @var pdoFetch $pdoFetch */
    public $pdoFetch;

    /**
     * Инициализация
     * @return bool
     */
    public function initialize()
    {
        $this->pdoFetch = $this->modx->getService('pdoFetch');


        return parent::initialize();
    }

    /**
     * Получает сырой массив категорий
     * @return array
     */
    public function getData()
    {
        $this->pdoFetch->setConfig([
            'parents' => 2,
            'select' => 'pagetitle',
            'sortby' => 'id',
            'limit' => 10,
            'depth' => 50,
            'return' => 'data',
            'where' => [
                'class_key' => 'msProduct'
            ]
        ]);
        return $this->pdoFetch->run();
    }

    public function process()
    {
        $items = $this->getData();
        $this->modx->log(1, print_r($items, true));
        $this->modx->log(1, print_r($this->getProperties(), true));
    }

    /**
     * We do a special check of permissions
     * because our objects is not an instances of modAccessibleObject
     *
     * @return boolean|string
     */
    public function beforeQuery()
    {
        if (!$this->checkPermissions()) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'name:LIKE' => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%",
            ]);
        }

        return $c;
    }


    /**
     * @param xPDOObject $object
     *
     * @return array
     */
    public function prepareRow(xPDOObject $object)
    {
        $array = $object->toArray();
        $array['actions'] = [];

        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('doublecheck_item_update'),
            //'multiple' => $this->modx->lexicon('doublecheck_items_update'),
            'action' => 'updateItem',
            'button' => true,
            'menu' => true,
        ];

        if (!$array['active']) {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-green',
                'title' => $this->modx->lexicon('doublecheck_item_enable'),
                'multiple' => $this->modx->lexicon('doublecheck_items_enable'),
                'action' => 'enableItem',
                'button' => true,
                'menu' => true,
            ];
        } else {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-gray',
                'title' => $this->modx->lexicon('doublecheck_item_disable'),
                'multiple' => $this->modx->lexicon('doublecheck_items_disable'),
                'action' => 'disableItem',
                'button' => true,
                'menu' => true,
            ];
        }

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('doublecheck_item_remove'),
            'multiple' => $this->modx->lexicon('doublecheck_items_remove'),
            'action' => 'removeItem',
            'button' => true,
            'menu' => true,
        ];

        $this->modx->log(1, print_r($array, 1));
        return $array;
    }

}

return 'DoubleCheckItemGetListProcessor';