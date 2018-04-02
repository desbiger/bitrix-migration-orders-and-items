<?php


namespace BitrixMigration\Export;


use BitrixMigration\BitrixMigrationHelper;

class ExportUserFields {
    use BitrixMigrationHelper;
    private $object;

    /**
     * ExportUserFields constructor.
     *
     * @param string $string
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    public function getAll()
    {
        return $this->FetchAll(\CUserTypeEntity::GetList([], ['ENTITY_ID' => $this->object]));
    }

}