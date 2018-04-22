<?php

namespace BitrixMigration\Import\ProductsReader;


use BitrixMigration\Import\Container;

class PriceTypes extends FilesReader {
    public $folder = '/priceTypes/';
    protected $containerIDsFieldName = '$newPriceTypesIDs';

    private $default = [
        'ID'         => '',
        'NAME'       => '',
        'BASE'       => '',
        'SORT'       => '',
        'XML_ID'     => '',
        'NAME_LANG'  => '',
        'CAN_ACCESS' => '',
        'CAN_BUY'    => '',
    ];

    public function updateElement($element)
    {
        return $this->filterKeys($element);
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