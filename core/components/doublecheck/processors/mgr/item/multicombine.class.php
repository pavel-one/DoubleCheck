<?php

class DoubleCheckMultiCombineProcessor extends modProcessor
{
    public $ids;
    public $pagetitle;
    public $parents;
    public $depth;
    /** @var pdoFetch $fetch */
    public $fetch;

    public function initialize()
    {
        $this->ids = json_decode($this->getProperty('ids'));
        if (!count($this->ids)) {
            return $this->failure('Нет товаров для объединения');
        }
        if (!is_array($this->ids)) {
            return $this->failure('IDS не массив');
        }

        $this->fetch = $this->modx->getService('pdoFetch');
        $this->parents = $this->modx->getOption('queueparser_catalog', [], 2);
        $this->depth = $this->modx->getOption('queueparser_depth', [], 50);
        return parent::initialize();
    }

    public function process()
    {
        foreach ($this->ids as $id) {
            /** @var msProduct $product */
            $product = $this->modx->getObject('msProduct', $id);
            if (!$product) {
                return $this->failure('Один или несколько товаров получить не удалось');
            }

            $pagetitle = $product->pagetitle;

            $this->fetch->setConfig([
                'parents' => $this->parents,
                'depth' => $this->depth,
                'select' => 'pagetitle,id,parent',
                'sortby' => 'id',
                'limit' => 0,
                'return' => 'data',
                'where' => [
                    'pagetitle' => $pagetitle,
                    'deleted:!=' => true,
                    'id:!=' => $id,
                ]
            ]);
            $doubles = $this->fetch->run();

            if (!count($doubles)) {
                return $this->failure('Не найдены дубли');
            }

            foreach ($doubles as $product_double) {
                if (!$this->modx->getCount('msCategoryMember', [
                    'product_id' => $id,
                    'category_id' => $product_double['parent'],
                ])) {
                    /** @var msCategoryMember $obj */
                    $obj = $this->modx->newObject('msCategoryMember');
                    $obj->set('product_id', $id);
                    $obj->set('category_id', $product_double['parent']);
                    if (!$obj->save()) {
                        return $this->failure('Ошибка сохранения, смотрите логи');
                    };
                };

                /** @var msProduct $removeProduct */
                $removeProduct = $this->modx->getObject('msProduct', $product_double['id']);
                $removeProduct->set('deleted', true);
                if (!$removeProduct->save()) {
                    return $this->failure('Ошибка удаления мусора');
                };
            }
        }
        return $this->success('Успешно');
    }
}

return 'DoubleCheckMultiCombineProcessor';