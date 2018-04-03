<?php


namespace BitrixMigration\Import;


trait PriceHelper {
    protected $defaultFields = [
        'NAME'           => '',
        'BASE'           => '',
        'SORT'           => '',
        'XML_ID'         => '',
        'NAME_LANG'      => '',
        'USER_GROUP'     => ['1'],
        'USER_GROUP_BUY' => ['1'],
        'USER_LANG'      => '',
    ];

    public function createPrice($fields)
    {


    }

    /**
     * @param $fields
     *
     * @return mixed
     */
    public function createPriceType($fields)
    {
        $ob = new \CCatalogGroup();
        $clearFields = $this->clearFields($fields);
        $res = $ob->add($clearFields);
        if (!$res) {
            return false;
        }

        return $res;
    }

    /**
     * @param $fields
     *
     * @return array
     */
    public function clearFields($fields)
    {
        $fields['USER_LANG'] = ['ru' => $fields['NAME_LANG']];

        return array_replace_recursive($this->defaultFields, $fields);
    }


    /**
     * @param $XML_ID
     *
     * @return mixed
     */
    protected function priceTypeExists($XML_ID)
    {
        return \CCatalogGroup::GetList([], ['XML_ID' => $XML_ID])->Fetch()['ID'];
    }

}