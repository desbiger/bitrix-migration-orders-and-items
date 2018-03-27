<?php

namespace BitrixMigration\Import;

class ImportPersonType {


    public $NewIDS;
    public $newOrderPropsIDS;
    private $persons;

    /**
     * @param $persons
     *
     * @return ImportPersonType
     */
    static function init($persons)
    {
        return new self($persons);
    }

    /**
     * ImportPersonType constructor.
     *
     * @param $persons
     */
    public function __construct($persons)
    {
        $this->persons = $persons;
    }

    /**
     * @return $this
     */
    public function import()
    {
        foreach ($this->persons as $person) {
            $this->importPersonType($person);
        }

        return $this;
    }

    /**
     * @param $person
     *
     * @return $this
     */
    private function importPersonType($person)
    {
        if (!$this->personTypeExists($person)) {
            $this->cretePersonType($person);

            return $this;
        }

        return $this;
    }

    /**
     * @param $NAME
     *
     * @return mixed
     */
    private function personTypeExists($person)
    {
        $res = \CSalePersonType::GetList([], ['NAME' => $person['NAME']])->Fetch()['ID'];
        if ($res) {
            $this->NewIDS[$person['ID']] = $res;

            return true;
        }

        return false;
    }

    /**
     * @param $person
     *
     * @return $this
     */
    private function cretePersonType($person)
    {

        $res = \CSalePersonType::Add($person);

        if ($res) {
            $this->NewIDS[$person['ID']] = $res;
            $this->newOrderPropsIDS = ImportOrderProps::init($res, $person['PROPS'])->import()->newIDS;

            return $this;
        }

        return $this;
    }

}