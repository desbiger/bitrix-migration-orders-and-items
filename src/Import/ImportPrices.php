<?php

namespace BitrixMigration\Import;

use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\CLI;
use BitrixMigration\Import\Contracts\Importer;
use BitrixMigration\Import\ProductsReader\Prices;
use BitrixMigration\JsonReader;

class ImportPrices implements Importer {
    use JsonReader, BitrixMigrationHelper, PriceHelper;


    public $pricesPath;
    public $data;
    public $files;
    public $priceTypes;
    public $newPriceTypes;
    public $siteID;
    private $importPath;
    private $elementsNewIDs;


    /**
     * ImportPrices constructor.
     *
     * @param $importPath
     * @param $elementsNewIDs
     */
    public function __construct()
    {
        \CModule::IncludeModule('catalog');
        $this->importPath = Container::instance()->getImportPath();
    }

    /**
     * Импорт пофайлово
     */
    public function import()
    {
        $list = new Prices();

        while (list($element, $count, $counter, $file, $oldProductID) = $list->getNextElement()) {
            if (count($element))
                $this->addPriceIfNotExists($element);

            CLI::show_status($counter, $count, 30, ' | Import prices | file: ' . $file);
        }
    }

    /**
     * Создаем набор цен для одного товара если не созданы
     *
     * @param $elementID
     * @param $itemPrices
     */
    private function addPriceIfNotExists($itemPrices)
    {

        $itemID = $itemPrices[0]['PRODUCT_ID'];
        $this->ProductAddPrices($itemID, $itemPrices);
    }


    /**
     *
     */
    private function importPriceTypes()
    {
        (new ImportPriceType($this->importPath))->import();
    }

    /**
     * @param $itemPrices
     */
    private function ProductAddPrices($oldID, $itemPrices)
    {
        foreach ($itemPrices as $price) {

            if ($id = $this->productPriceExists($price)) {
                Container::instance()->newPriceIDs[$oldID] = $id;
                continue;
            }
            $CPrice = new \CPrice();
            $id = $CPrice->Add($price);

            if ($id) {
                Container::instance()->newPriceIDs[$oldID] = $id;
            } else {
                dump($price);
                dump('Ошибка добавления цены : ' . $CPrice->LAST_ERROR);
            }
        }
        Container::instance()->trySaveContainer();
    }

    /**
     *
     */
    public function execute()
    {
        $this->before();
        $this->import();
        $this->after();
    }

    /**
     * @return string
     */
    public function getImportName()
    {
        return 'Import Prices';
    }

    public function before()
    {

    }

    public function after()
    {
        $this->setDefaultRatio();
    }

    /**
     * @param $oldID
     *
     * @return bool
     */
    private function isLoaded($oldID)
    {
        $newPriceIDs = Container::instance()->newPriceIDs;

        return in_array($oldID, array_keys($newPriceIDs));
    }

    /**
     * @param $price
     *
     * @return mixed
     */
    private function productPriceExists($price)
    {
        return \CPrice::getList([], [
            'PRODUCT_ID'       => $price['PRODUCT_ID'],
            'PRICE'            => $price['PRICE'],
            'CATALOG_GROUP_ID' => $price['CATALOG_GROUP_ID']
        ])->Fetch()['ID'];
    }

    public function setSiteID($id)
    {
        $this->siteID = $id;
    }

    /**
     *
     */
    private function setDefaultRatio()
    {
        $count = count(Container::instance()->newProductsIDs);
        $i = 1;
        foreach (Container::instance()->newProductsIDs as $id) {
            $arFields = [
                'ID' => $id,
                'AVAILABLE' => 'Y'
            ];
            \CCatalogProduct::Add($arFields);
            CLI::show_status($i++, $count, 30, " product $id to catalog");
        }
    }

}