<?php


namespace BitrixMigration;


use BitrixMigration\Import\ImportUsers;

class Import {
    use JsonReader;

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

    }

    public function iblockItems()
    {

    }


}