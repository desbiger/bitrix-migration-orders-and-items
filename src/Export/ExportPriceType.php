<?php


namespace BitrixMigration\Export;


use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\Export\Contracts\Exporter;
use BitrixMigration\JsonReader;

class ExportPriceType implements Exporter {
    use BitrixMigrationHelper, JsonReader;

    public $CCatotalogGroup;

    public function __construct()
    {
        $this->CCatotalogGroup = new \CCatalogGroup;
    }

    public function getAll()
    {
        return $this->FetchAll($this->CCatotalogGroup->GetList());
    }

    /**
     * @return $this;
     */
    public function before()
    {
        // TODO: Implement before() method.
        return $this;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $list = $this->getAll();
        mkdir(container()->exportPath . '/priceTypes/');
        file_put_contents(container()->exportPath . '/priceTypes/list.json', json_encode($list));

        return $this;
    }

    public function after()
    {
        // TODO: Implement after() method.
    }
}