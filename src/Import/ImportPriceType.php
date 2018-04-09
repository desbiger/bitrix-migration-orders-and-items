<?php


namespace BitrixMigration\Import;


use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\CLI;
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
        $i = 0;

        foreach ($this->data as $type) {
            CLI::show_status($i++, count($this->data));
            $this->newIDs[$type['ID']] = $this->createPriceTypeIfNotExists($type);
        }

        Container::instance()->newPriceTypesIDs = $this->newIDs;
        Container::instance()->trySaveContainer();

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
        echo "\n check loaded priceTypes";
        foreach ($this->read('priceTypes') as $item) {
            if (!$this->isLoaded($item))
                $this->data[] = $item;
        };
    }

    private function isLoaded($item)
    {
        return in_array($item['ID'], array_keys(Container::instance()->newPriceTypesIDs));
    }

}