<?php


namespace BitrixMigration;


use BitrixMigration\Import\Contracts\Importer;

class Import {

    public $importCatalog;
    public $importers;
    private $siteID;


    /**
     * @return Import
     */
    static function init($siteID)
    {
        return new self($siteID);
    }

    /**
     * Import constructor.
     *
     * @param $import_path
     */
    public function __construct($siteID)
    {

        $this->siteID = $siteID;
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