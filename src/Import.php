<?php


namespace BitrixMigration;


use BitrixMigration\Import\Contracts\Importer;

class Import {

    public $importCatalog;
    public $importers;


    /**
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