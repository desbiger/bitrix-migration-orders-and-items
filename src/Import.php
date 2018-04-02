<?php


namespace BitrixMigration;


use BitrixMigration\Import\ImportIblock;
use BitrixMigration\Import\ImportOrders;
use BitrixMigration\Import\ImportProducts;
use BitrixMigration\Import\ImportSections;
use BitrixMigration\Import\ImportUsers;

class Import {
    public $iblock_id = 5;
    use JsonReader, BitrixMigrationHelper;
    /**
     * @var ImportSections
     */
    public $sectionImportResult;
    public $newIblock;

    /**
     * @param $import_path
     *
     * @param $iblock_id
     *
     * @return Import
     */
    static function init($import_path, $iblock_id)
    {
        return new self($import_path, $iblock_id);
    }

    /**
     * Import constructor.
     *
     * @param $import_path
     */
    public function __construct($import_path, $iblock_id)
    {
        $this->import_path = $import_path;
        $this->iblock_id = $iblock_id;
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

        (new ImportOrders($this->iblock_id))->import($orders, $users, $persons, $paySystems, $delivery);
    }

    /**
     * Импорт инфоблока
     */
    public function iblock()
    {
        $iblock = $this->read('iblock');
        $this->newIblock = new ImportIblock($iblock, $this->import_path);

        $importCatalog = new ImportProducts($this->newIblock, $this->import_path);
    }


}