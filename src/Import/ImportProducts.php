<?php

namespace BitrixMigration\Import;

use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\Export\ExportProducts;

class ImportProducts {

    use BitrixMigrationHelper;
    public $iblockElement;
    public $exportProducts;

    public function __construct($iblock_id)
    {
        $this->exportProducts = new ExportProducts($iblock_id);
        $this->iblockElement = new \CIBlockElement();
    }

    /**
     * @param $productXMLID
     * @param $price
     *
     * @return mixed
     */
    public function getProductPriceID($productXMLID, $price)
    {
        $t = $this->iblockElement->GetList([], ['XML_ID' => $productXMLID]);
        $product = $t->Fetch();
        $prices = $this->exportProducts->CatalogPrices->getPrices($product['ID'], ['PRICE' => $price]);

        return count($prices) ? $price[0]['ID'] : null;
    }


}