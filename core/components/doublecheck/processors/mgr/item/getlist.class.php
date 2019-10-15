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
                'deleted:!=' => true,
            ]
        ];

        $this->pdoFetch->setConfig($config);
        $out = $this->pdoFetch->run();
        $actions = [
            [
                'cls' => '',
                'icon' => 'icon icon-wrench',
                'title' => 'Объеденить',
                'action' => 'combineItem',
                'button' => 1,
                'menu' => 1,
            ],
//            [
//                'cls' => '',
//                'icon' => 'icon icon-eye',
//                'title' => 'Посмотреть дубли',
//                'action' => 'showDoubles',
//                'button' => 1,
//                'menu' => 1,
//            ]
        ];


        foreach ($out as $key => $product) {
            $doubles = $this->getDoubles($product['pagetitle'], (int)$product['id']);
            $out[$key]['count'] = count($doubles);
            $out[$key]['doubles'] = $doubles;
            if ($out[$key]['count']) {
                $out[$key]['actions'] = $actions;
            }
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
                'deleted:!=' => true,
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
            'deleted:!=' => true,
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

}

return 'DoubleCheckItemGetListProcessor';