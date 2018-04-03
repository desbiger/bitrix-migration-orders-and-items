<?php


namespace BitrixMigration\Import;


use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\JsonReader;

class ImportPriceType {
    use JsonReader, BitrixMigrationHelper, PriceHelper;
    public $data;
    public $newIDs;
    private $import_path;

    /**
     * ImportPriceType constructor.
     *
     * @param $import_path
     */
    public function __construct($import_path)
    {
        \CModule::IncludeModule('catalog');
        $this->import_path = $import_path;
        $this->loadTypes();
    }


    /**
     * @return $this
     */
    public function import()
    {
        foreach ($this->data as $type) {
            $this->newIDs[$type['ID']] = $this->createPriceTypeIfNotExists($type);
        }
        return $this;
    }

    /**
     * @param $type
     *
     * @return mixed
     */
    private function createPriceTypeIfNotExists($type)
    {
        if ($id = $this->priceTypeExists($type['XML_ID'])) {
            return $id;
        }

        return $this->createPriceType($type);

    }

    /**
     *
     */
    private function loadTypes()
    {
        $this->data = $this->read('priceTypes');
    }

}