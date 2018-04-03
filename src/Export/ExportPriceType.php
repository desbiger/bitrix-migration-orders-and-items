<?php


namespace BitrixMigration\Export;


use BitrixMigration\BitrixMigrationHelper;

class ExportPriceType {
    use BitrixMigrationHelper;

    public $CCatotalogGroup;

    public function __construct()
    {
        $this->CCatotalogGroup = new \CCatalogGroup;
    }

    public function getAll()
    {
        return $this->FetchAll($this->CCatotalogGroup->GetList());
    }

}