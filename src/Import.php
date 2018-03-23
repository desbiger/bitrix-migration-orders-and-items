<?php


namespace BitrixMigration;


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
     * @return Import
     */
    static function init($import_path)
    {
        return new self($import_path);
    }

    /**
     * Import constructor.
     *
     * @param $import_path
     */
    public function __construct($import_path)
    {
        $this->import_path = $import_path;
    }

    public function users()
    {
        $users = $this->read('users');

        ImportUsers::init($users)->import();
    }

    public function orders()
    {

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