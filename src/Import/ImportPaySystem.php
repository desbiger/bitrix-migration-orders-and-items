<?php


namespace BitrixMigration\Import;


class ImportPaySystem {

    public $import_path;
    private $list;
    public $newPaySystemIDS = [];
    private $persontypes;
    public $excludedFields = ['ACTION' => '', 'ID' => ''];

    /**
     * @param $list
     *
     * @return ImportPaySystem
     */
    static function init($list, $import_path, $persontypes)
    {
        return new self($list, $import_path, $persontypes);
    }

    /**
     * ImportPaySystem constructor.
     *
     * @param $list
     */
    public function __construct($list, $import_path, $persontypes)
    {

        $this->list = $list;
        $this->import_path = $_SERVER['DOCUMENT_ROOT'] . 'import_export' . $import_path;
        $this->persontypes = $persontypes;
    }

    /**
     * @return $this
     */
    public function import()
    {
        foreach ($this->list as $paySystem) {
            $this->createIfNotExists($paySystem);
        }

        return $this;
    }

    private function createIfNotExists($paySystem)
    {
        if (!$res = $this->getPaySystemByName($paySystem['NAME'])) {
            return $this->createPaySystem($paySystem);
        }

        return $this->newPaySystemIDS[$paySystem['ID']] = $res['ID'];
    }

    /**
     * @param $NAME
     *
     * @return mixed
     */
    private function getPaySystemByName($NAME)
    {
        return \CSalePaySystem::GetList([], ['NAME' => $NAME])->Fetch();
    }

    /**
     * @param $paySystem
     *
     * @return mixed
     */
    private function createPaySystem($paySystem)
    {
        $action = $paySystem['ACTION'];

        $paySystem = array_diff_key($paySystem, $this->excludedFields);

        $id = \CSalePaySystem::Add($paySystem);
        if ($id) {
            $this->newPaySystemIDS[$paySystem['ID']] = $id;

            $action['PAY_SYSTEM_ID'] = $id;

            $this->createPaySystemAction($action);

        }


    }

    /**
     * @param $action
     *
     * @return mixed
     */
    private function createPaySystemAction($action)
    {

        $path = $this->import_path . '/files' . $action['LOGOTIP'];
        $action['LOGOTIP'] = \CFile::MakeFileArray($path);
        $action['PERSON_TYPE_ID'] = $action['PERSON_TYPE_ID'] ? $this->persontypes[$action['PERSON_TYPE_ID']] : $action['PERSON_TYPE_ID'];

        $action = array_diff_key($action,$this->excludedFields);

        return \CSalePaySystemAction::Add($action);
    }


}