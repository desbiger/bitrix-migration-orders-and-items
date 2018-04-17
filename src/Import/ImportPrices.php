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
        $importPath = Container::instance()->getImportPath();

        $list = new Prices($importPath . '/prices', $importPath);

        while (list($element, $count, $counter, $file) = $list->getNextElement()) {

            CLI::show_status($counter, count($count), 30, ' | Import prices | file: ' . $file);
            $this->addPriceIfNotExists($element);
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

        $itemID = $itemPrices[0]['ID'];
        $itemPrices = $this->changeRelationIDs($itemPrices);

        $this->ProductAddPrices($itemID, $itemPrices);
    }

    /**
     * Заменяем ID товара на новосозданный
     *
     * @param $clearFields
     *
     * @return bool
     */
    private function changeProductAndGroupID($clearFields)
    {
        $container = Container::instance();

        $newProductID = $container->newProductsIDs[$clearFields['PRODUCT_ID']];


        if (!$newProductID)
            return false;

        $clearFields = array_replace_recursive($clearFields, ['PRODUCT_ID' => $newProductID]);
        $clearFields = array_replace_recursive($clearFields, ['CATALOG_GROUP_ID' => $container->newPriceTypesIDs[$clearFields['CATALOG_GROUP_ID']]]);

        return $clearFields;
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
            $price = array_diff_key($price, ['CATALOG_GROUP_NAME' => '', 'EXTRA_ID' => '']);

            $id = \CPrice::Add($price);

            Container::instance()->newPriceIDs[$oldID] = $id;
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
        $this->elementsNewIDs = Container::instance()->getNewProductsIDs();
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

    /**
     * @param $itemPrices
     * @param $diff
     *
     * @return array
     */
    private function changeRelationIDs($itemPrices)
    {
        $diff = [
            'ID'         => '',
            'CAN_BUY'    => '',
            'CAN_ACCESS' => ''
        ];
        $itemPrices = array_map(function ($price) use ($diff) {

            $clearFields = array_diff_key($price, $diff);

            if (!$changedFields = $this->changeProductAndGroupID($clearFields)) {
                return false;
            };
            $changedFields = array_replace_recursive($this->default, $changedFields);

            return $changedFields;

        }, $itemPrices);

        return $itemPrices;
    }

    public function setSiteID($id)
    {
        $this->siteID = $id;
    }
}