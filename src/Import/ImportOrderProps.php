<?php

namespace BitrixMigration\Import;

class ImportOrderProps {

    public $newIDS;
    private $props;
    private $personTypeID;

    public static function init($personTypeID, $props)
    {
        return new self($personTypeID, $props);
    }

    public function __construct($personTypeID, $props)
    {

        $this->props = $props;
        $this->personTypeID = $personTypeID;
    }

    /**
     * @return $this
     */
    public function import()
    {
        foreach ($this->props as $prop) {
            $prop['PERSON_TYPE_ID'] = $this->personTypeID;
            $this->newIDS[$prop['ID']] = $this->createOrderProp($prop);
        }

        return $this;
    }

    /**
     * @param $prop
     *
     * @return mixed
     */
    private function createOrderProp($prop)
    {
        $id = \CSaleOrderProps::Add($prop);

        return $id;
    }

}