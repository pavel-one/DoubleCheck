<?php

class DoubleCheckCombineProcessor extends modProcessor {
    public $id;
    public $pagetitle;
    public $parents;
    public $depth;
    /** @var msProduct $resource */
    public $product;
    /** @var pdoFetch $fetch */
    public $fetch;
    public function initialize()
    {
        $this->id = (int)$this->getProperty('id');
        $this->pagetitle = $this->getProperty('pagetitle');

        if ((!$this->id) || (!$this->pagetitle)) {
            return $this->failure('Не передано одно из значений');
        }

        if (!$this->product = $this->modx->getObject('msProduct', $this->id)) {
            return $this->failure('Не найден продукт');
        }

        $this->fetch = $this->modx->getService('pdoFetch');
        $this->parents = $this->modx->getOption('queueparser_catalog', [], 2);
        $this->depth = $this->modx->getOption('queueparser_depth', [], 50);
        return parent::initialize();
    }

    public function process()
    {
        $this->fetch->setConfig([
            'parents' => $this->parents,
            'depth' => $this->depth,
            'select' => 'pagetitle,id,parent',
            'sortby' => 'id',
            'limit' => 0,
            'return' => 'data',
            'where' => [
                'pagetitle' => $this->pagetitle,
                'deleted:!=' => true,
                'id:!=' => $this->id,
            ]
        ]);
        $products = $this->fetch->run();

        if (!count($products)) {
            return $this->failure('Не найдены дубли');
        }

        foreach ($products as $product) {
            /** @var msCategoryMember $obj */
            $obj = $this->modx->newObject('msCategoryMember');
            $obj->set('product_id', $this->id);
            $obj->set('category_id', $product['parent']);
            if (!$obj->save()) {
                return $this->failure('Ошибка сохранения, смотрите логи');
            };

            /** @var msProduct $removeProduct */
            $removeProduct = $this->modx->getObject('msProduct', $product['id']);
            $removeProduct->set('deleted', true);
            $removeProduct->save();
        }

        return $this->success('Успешно');
    }
}

return 'DoubleCheckCombineProcessor';