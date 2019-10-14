<?php

class DoubleCheckItemGetListProcessor extends modProcessor
{
    /** @var pdoFetch $pdoFetch */
    public $pdoFetch;
    public $parents;
    public $depth;

    public $start;
    public $limit;

    public $sort = 'pagetitle';
    public $dir = 'ASC';

    /**
     * Инициализация
     * @return bool
     */
    public function initialize()
    {
        $this->pdoFetch = $this->modx->getService('pdoFetch');
        $this->parents = $this->modx->getOption('queueparser_catalog', [], 2);
        $this->depth = $this->modx->getOption('queueparser_depth', [], 50);

        $this->start = (int)$this->getProperty('start');
        $this->limit = (int)$this->getProperty('limit');

        if ($sort = $this->getProperty('sort')) {
            $this->sort = $sort;
        }

        if ($dir = $this->getProperty('dir')) {
            $this->dir = $dir;
        }

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
            'sortby' => $this->sort,
            'sortdir' => $this->dir,
            'return' => 'data',
            'where' => [
                'class_key' => 'msProduct',
                'pagetitle:!=' => '',
            ]
        ];

        $this->pdoFetch->setConfig($config);
        $out = $this->pdoFetch->run();
        $actions = [
            [
                'cls' => '',
                'icon' => 'icon icon-wrench',
                'title' => 'Объеденить',
                'action' => 'updateItem',
                'button' => 1,
                'menu' => 1,
            ],
            [
                'cls' => '',
                'icon' => 'icon icon-eye',
                'title' => 'Посмотреть дубли',
                'action' => 'showDubles',
                'button' => 1,
                'menu' => 1,
            ]
        ];


        foreach ($out as $key => $product) {
            $doubles = $this->getDoubles($product['pagetitle'], (int)$product['id']);
            $out[$key]['count'] = count($doubles);
            $out[$key]['doubles'] = $doubles;
            $out[$key]['actions'] = $actions;
            if ($doubles == false) {
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
                'id:!=' => $id,/** TODO: Сделать по опции */
            ]
        ];
        $this->pdoFetch->setConfig($config);
        $doubles = $this->pdoFetch->run();


        return $doubles;
    }

    /**
     * Получение всех продуктов
     * @return int
     */
    public function getTotal()
    {
        $query = $this->modx->newQuery('msProduct');
        $query->where([
            'class_key' => 'msProduct',
            'pagetitle:!=' => '',
        ]);
        $query->groupby('pagetitle');
        return $this->modx->getCount('msProduct', $query);
    }

    public function process()
    {
        $items = $this->getData();
        $total = $this->getTotal();

        return $this->success($items, $total);
    }

    public function success($results = '', $total = null)
    {
        $outArr = [
            'results' => $results,
            'success' => true,
            'total' => $total,
        ];
        return json_encode($outArr);
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