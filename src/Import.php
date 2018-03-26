<?php


namespace BitrixMigration;


use BitrixMigration\Import\ImportOrders;
use BitrixMigration\Import\ImportSections;
use BitrixMigration\Import\ImportUsers;

class Import {
    public $iblock_id = 5;
    use JsonReader, BitrixMigrationHelper;
    /**
     * @var ImportSections
     */
    public $sectionImportResult;

    /**
     * @param $import_path
     *
     * @param $iblock_id
     *
     * @return Import
     */
    static function init($import_path,$iblock_id)
    {
        return new self($import_path,$iblock_id);
    }

    /**
     * Import constructor.
     *
     * @param $import_path
     */
    public function __construct($import_path,$iblock_id)
    {
        $this->import_path = $import_path;
        $this->iblock_id = $iblock_id;
    }

    public function users()
    {
        $users = $this->read('users');

        ImportUsers::init($users)->import();
    }

    /**
     *
     */
    public function orders()
    {
        $orders = $this->read('orders');
        (new ImportOrders($this->iblock_id))->import($orders);
    }

    public function iblockSections()
    {
        $sections = $this->read('sections/sections_1');
        $this->sectionImportResult = (new Import\ImportSections($sections, $this->iblock_id))->import();
    }

    public function iblockItems()
    {

    }


}