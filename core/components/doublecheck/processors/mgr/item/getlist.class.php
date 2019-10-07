<?php

class DoubleCheckItemGetListProcessor extends modProcessor
{
    /** @var pdoFetch $pdoFetch */
    public $pdoFetch;
    public $parents;
    public $depth;

    public $start;
    public $limit;

    /**
     * Инициализация
     * @return bool
     */
    public function initialize()
    {
        $this->pdoFetch = $this->modx->getService('pdoFetch');
        $this->parents = 2; /** TODO: Сделать из опций */
        $this->depth = 50; /** TODO: Сделать из опций */

        $this->start = (int)$this->getProperty('start');
        $this->limit = (int)$this->getProperty('limit');

        return parent::initialize();
    }

    /**
     * Получает сырой массив категорий
     * @return array
     */
    public function getData()
    {
        $config = [
            'limit' => $this->limit,
            'offset' => $this->start,
            'parents' => $this->parents,
            'depth' => $this->depth,
            'class' => 'msProduct',
            'select' => 'pagetitle,id,parent,uri',
            'groupby' => 'msProduct.pagetitle',
            'sortby' => 'pagetitle',
            'return' => 'data',
            'where' => [
                'class_key' => 'msProduct'
            ]
        ];

        $this->pdoFetch->setConfig($config);
        $out = $this->pdoFetch->run();
        $actions = [
            [
                'cls' => '',
                'icon' => 'icon icon-edit',
                'title' => 'Изменить Предмет',
                'action' => 'updateItem',
                'button' => 1,
                'menu' => 1,
            ],
            [
                'cls' => '',
                'icon' => 'icon icon-power-off action-gray',
                'title' => 'Отключить предмет',
                'action' => 'disableItem',
                'multiple' => 'Отключить Предметы',
                'button' => 1,
                'menu' => 1,
            ],
            [
                'cls' => '',
                'icon' => 'icon icon-trash-o action-red',
                'title' => 'Удалить Предмет',
                'action' => 'removeItem',
                'multiple' => 'Удалить Предметы',
                'button' => 1,
                'menu' => 1,
            ],
        ];


        foreach ($out as $key => $product) {
            $doubles = $this->getDoubles($product['pagetitle'], (int)$product['id']);
            $out[$key]['count'] = count($doubles);
            $out[$key]['doubles'] = $doubles;
            $out[$key]['actions'] = $actions;
            if ($doubles == false) {
//                unset($out[$key]);
                continue;
            }
        }

        return $out;
    }

    /**
     * @param string $name
     * @param int $id
     * @return array|bool
     */
    public function getDoubles($name, $id)
    {
        $config = [
            'parents' => $this->parents,
            'depth' => $this->depth,
            'select' => 'pagetitle,id,parent',
            'sortby' => 'id',
            'limit' => 0,
            'return' => 'data',
            'where' => [
                'pagetitle' => $name,
                'id:!=' => $id, /** TODO: Сделать по опции */
            ]
        ];
        $this->pdoFetch->setConfig($config);
        $doubles = $this->pdoFetch->run();


        return $doubles;
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