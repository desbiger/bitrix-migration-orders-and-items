<?php


namespace BitrixMigration\Export;


use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\Export\Contracts\Exporter;

class ExportUserFields implements Exporter {
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

    /**
     * @return $this;
     */
    public function before()
    {
        // TODO: Implement before() method.
    }

    /**
     * @return $this
     */
    public function execute()
    {
        // TODO: Implement execute() method.
    }

    public function after()
    {
        // TODO: Implement after() method.
    }
}