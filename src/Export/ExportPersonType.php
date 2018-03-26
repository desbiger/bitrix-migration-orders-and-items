<?php


namespace BitrixMigration\Export;


use BitrixMigration\BitrixMigrationHelper;

class ExportPersonType {

    use BitrixMigrationHelper;

    static function init()
    {
        return new self();
    }

    public function __construct()
    {

    }

    public function export()
    {
        $all = $this->getList();
        dd($all);
    }

    /**
     * @return array
     */
    private function getList()
    {
        return $this->FetchAll(\CSalePersonType::GetList([], ['LID' => 's1']));
    }


}