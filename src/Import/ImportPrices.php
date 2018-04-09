<?php

namespace BitrixMigration\Import;

use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\CLI;
use BitrixMigration\Import\Contracts\Importer;
use BitrixMigration\JsonReader;

class ImportPrices implements Importer {
    use JsonReader, BitrixMigrationHelper, PriceHelper;


    public $pricesPath;
    public $data;
    public $files;
    public $priceTypes;
    public $newPriceTypes;
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
        foreach ($this->files as $file) {
            $this->data = $this->read('prices/'. $file);
            $this->importPrices();
        }
    }

    /**
     * Получаем список файлов импорта
     * @return array
     */
    private function getFilesList()
    {
        $this->files = $this->scanDir($this->pricesPath);
    }

    /**
     *  Импортируем все цены одного файла
     */
    private function importPrices()
    {
        $i = 0;
        foreach ($this->data as $elementID => $itemPrices) {
            CLI::show_status($i++, count($this->data));

            if (array_key_exists($elementID, $this->elementsNewIDs))
                $this->addPriceIfNotExists($elementID, $itemPrices);
        }
    }

    /**
     * Создаем набор цен для одного товара если не созданы
     *
     * @param $elementID
     * @param $itemPrices
     */
    private function addPriceIfNotExists($elementID, $itemPrices)
    {
        $diff = [
            'ID'         => '',
            'CAN_BUY'    => '',
            'CAN_ACCESS' => ''
        ];

        $itemPrices = array_map(function ($price) use ($diff) {

            $clearFields = array_diff_key($price, $diff);

            $changedFields = $this->changeProductAndGroupID($clearFields);
            $changedFields = array_replace_recursive($this->default, $changedFields);

            return $changedFields;

        }, $itemPrices);

        $this->ProductAddPrices($itemPrices);
    }

    /**
     * Заменяем ID товара на новосозданный
     *
     * @param $clearFields
     *
     * @return array
     */
    private function changeProductAndGroupID($clearFields)
    {
        $clearFields = array_replace_recursive($clearFields, ['PRODUCT_ID' => $this->elementsNewIDs[$clearFields['PRODUCT_ID']]]);
        $clearFields = array_replace_recursive($clearFields, ['CATALOG_GROUP_ID' => $this->newPriceTypes[$clearFields['CATALOG_GROUP_ID']]]);

        return $clearFields;
    }

    private function importPriceTypes()
    {
        $this->priceTypes = new ImportPriceType($this->importPath);
        $this->newPriceTypes = $this->priceTypes->import()->newIDs;
    }

    private function ProductAddPrices($itemPrices)
    {
        foreach ($itemPrices as $price) {
            \CPrice::Add($price);
        }
    }

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
        $this->pricesPath = $this->importPath . '/prices/';
        $this->importPriceTypes();
        $this->getFilesList();
    }

    public function after()
    {

    }
}