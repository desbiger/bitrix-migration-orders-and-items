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
        return $this->getList();
    }

    /**
     * @return array
     */
    private function getList()
    {
        return $this->FetchAll(\CSalePersonType::GetList([], ['LID' => 's1']), function ($item) {
            $item['PROPS'] = $this->FetchAll(\CSaleOrderProps::GetList([], ['PERSON_TYPE_ID' => $item['ID']]));
            return $item;
        });
    }


}