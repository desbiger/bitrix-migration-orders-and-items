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
        $this->importPath = Container::instance()->getImportPath();
    }

    /**
     * Импорт пофайлово
     */
    public function import()
    {
        $list = new Prices();

        while (list($element, $count, $counter, $file) = $list->getNextElement()) {
            CLI::show_status($counter, count($count), 30, ' | Import prices | file: ' . $file);
            $this->addPriceIfNotExists($element);
            dd('123');
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
            if (!$price) {
                echo "\n" . $oldID . ' passed';
                continue;
            }

            if ($id = $this->productPriceExists($price)) {
                Container::instance()->newPriceIDs[$oldID] = $id;
                continue;
            }
            $CPrice = new \CPrice();
            $id = $CPrice->Add($price);

            if($id){
                Container::instance()->newPriceIDs[$oldID] = $id;
            }else{
                dump($price);
                dd('Ошибка добавления цены : '.$CPrice->LAST_ERROR);
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
        $this->importPriceTypes();
    }

    public function after()
    {

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
}