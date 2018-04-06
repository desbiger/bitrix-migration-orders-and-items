<?php


namespace BitrixMigration;


use BitrixMigration\Import\Container;
use BitrixMigration\Import\Contracts\Importer;
use BitrixMigration\Import\ImportOrders;
use BitrixMigration\Import\ImportProducts;
use BitrixMigration\Import\ImportSections;

class Import {

    use JsonReader, BitrixMigrationHelper;
    /**
     * @var ImportSections
     */
    public $sectionImportResult;
    public $newIblock;
    /**
     * @var ImportProducts
     */
    public $importCatalog;
    public $importers;

    /**
     * @param $import_path
     *
     * @param $iblock_id
     *
     * @return Import
     */
    static function init()
    {
        return new self();
    }

    /**
     * Import constructor.
     *
     * @param $import_path
     */
    public function __construct()
    {

    }


    /**
     *
     */
    public function orders()
    {
        $orders = $this->read('orders');
        $users = $this->read('users');
        $persons = $this->read('personType');
        $paySystems = $this->read('paySystem');
        $delivery = $this->read('delivery');

        (new ImportOrders($this->iblock_id, $this->importCatalog))->import($orders, $users, $persons, $paySystems, $delivery);
    }

    /**
     * @param Importer $importer
     *
     * @return $this
     */
    public function register(Importer $importer)
    {
        $this->importers[] = $importer;
        return $this;
    }

    public function import()
    {
        /** @var Importer $Importer */
        foreach ($this->importers as $Importer) {
            echo "\n" . $Importer->getImportName();
            $Importer->execute();
        }
    }


}