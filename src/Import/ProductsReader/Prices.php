<?php

namespace BitrixMigration\Import\ProductsReader;


use BitrixMigration\Import\Container;

class Prices extends FilesReader {
    public $folder = '/prices/';
    protected $containerIDsFieldName = 'newPriceIDs';

    private $default = [
        'PRODUCT_ID'       => '',
        'CATALOG_GROUP_ID' => '',
        'EXTRA_ID'         => '',
        'PRICE'            => '',
        'CURRENCY'         => '',
        'QUANTITY_FROM'    => '',
        'QUANTITY_TO'      => '',
    ];

    /**
     * @param $element
     *
     * @return mixed
     */
    public function updateElement($element)
    {
        foreach ($element as $price) {
            $prices[] = $this->setNewIDs($price);
        }

        return $prices;
    }

    /**
     * @param $element
     *
     * @return array
     */
    public function setNewIDs($element)
    {
        $element['PRODUCT_ID'] = Container::instance()->newProductsIDs[$element['PRODUCT_ID']];
        $element['CATALOG_GROUP_ID'] = Container::instance()->newPriceTypesIDs[$element['CATALOG_GROUP_ID']];

        $array = array_replace_recursive($this->default, $element);

        return $this->filterKeys($array);
    }

    /**
     * @param $array
     *
     * @return array
     */
    private function filterKeys($array)
    {
        return collect($array)->only(array_keys($this->default))->toArray();
    }


}