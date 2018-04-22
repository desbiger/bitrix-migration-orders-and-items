<?php


namespace BitrixMigration\Import;


use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\CLI;
use BitrixMigration\Import\Contracts\Importer;
use BitrixMigration\Import\ProductsReader\PriceTypes;
use BitrixMigration\JsonReader;

class ImportPriceType implements Importer {
    use JsonReader, BitrixMigrationHelper, PriceHelper;
    public $data;
    public $newIDs;
    private $import_path;

    /**
     * ImportPriceType constructor.
     *
     * @param $import_path
     */
    public function __construct()
    {
        \CModule::IncludeModule('catalog');
        $this->import_path = Container::instance()->import_path;
    }


    /**
     * @return $this
     */
    public function import()
    {
        $list = new PriceTypes();

        while (list($element, $count, $counter) = $list->getNextElement()) {
            $id = $this->createPriceTypeIfNotExists($element);
            Container::instance()->setNewPriceTypesIDs($element['ID'], $id);

            CLI::show_status($counter, $count,30,'  | import price Types');
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


    public function setSiteID($id)
    {
        // TODO: Implement setSiteID() method.
    }

    public function execute()
    {
        $this->import();
    }

    /**
     * @return string
     */
    public function getImportName()
    {
        // TODO: Implement getImportName() method.
    }

    public function before()
    {
        // TODO: Implement before() method.
    }

    public function after()
    {
        // TODO: Implement after() method.
    }
}