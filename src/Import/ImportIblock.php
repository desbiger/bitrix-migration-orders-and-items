<?php

namespace BitrixMigration\Import;

use Sprint\Migration\HelperManager;
use \Bitrix\Highloadblock as HL;

class ImportIblock {

    public $settings;
    public $newIblockID;
    public $properties;
    public $files;
    public $RelativeIblocks;
    public $newPropertyIDs;
    public $FilesPath;
    private $data;
    private $import_path;

    public function __construct($data, $import_path)
    {
        \CModule::IncludeModule('highloadblock');
        $this->data = $data;
        $this->import_path = $import_path;
        $this->separate();
        $this->helper = new HelperManager();
        $this->createIBlock();
    }

    /**
     * Разделяем входные данные
     */
    private function separate()
    {
        $this->settings = $this->data['settings'];
        $this->properties = $this->data['properties'];
        $this->files = $this->data['files'];
        $this->RelativeIblocks = $this->data['RelativeIblocks'];
    }

    /**
     * Создаем инфоблок, если нет одноименного
     */
    private function createIBlock()
    {
        $IBLOCK_TYPE_ID = $this->settings['IBLOCK_TYPE_ID'];

        $this->createIblockType($IBLOCK_TYPE_ID);
        $id = $this->helper->Iblock()->addIblockIfNotExists($this->settings);
        $this->newIblockID = $id;

        $this->createProperties();
    }

    /**
     * Создаем тип инфоблока если не существует
     *
     * @param $IBLOCK_TYPE_ID
     */
    private function createIblockType($IBLOCK_TYPE_ID)
    {
        $this->helper->Iblock()->addIblockTypeIfNotExists(['ID' => $IBLOCK_TYPE_ID]);
    }

    /**
     * Создаем дополнительное свойство инфоблока если нет
     */
    private function createProperties()
    {
        foreach ($this->properties as $property) {
            if ($property['USER_TYPE_SETTINGS'])
                $this->createHiloadBlock($property);

            $this->newPropertyIDs[$property['ID']] = $this->helper->Iblock()->addPropertyIfNotExists($this->newIblockID, $property);
        }
    }

    /**
     * @return mixed
     */
    public function getNewPropertyIDs($oldID)
    {
        return $this->newPropertyIDs[$oldID];
    }

    /**
     * Создаем Хайлоад блок если такового нет
     *
     * @param $property
     */
    private function createHiloadBlock($property)
    {
        $fields = collect($property['USER_TYPE_SETTINGS'])->only(['NAME', 'TABLE_NAME'])->toArray();

        $id = $this->helper->Hlblock()->addHlblockIfNotExists($fields);

        $this->createUserFieldsIfNotExists($id, $property['HLBT']['USER_FIELDS']);

        foreach ($property['HILOAD'] as $vol) {
            $this->addHiloadBlockRecordIfNotExists($id, $vol);
        }
    }

    /**
     * Добавляем запись в хайлоад блок если нет
     *
     * @param $id
     * @param $vol
     *
     * @return bool
     */
    private function addHiloadBlockRecordIfNotExists($id, $vol)
    {
        if ($this->HlbbtRecordExists($vol, $id))
            return true;

        if ($vol['UF_FILE']) {
            $vol['UF_FILE'] = \CFile::MakeFileArray($this->import_path . '/files/' . $vol['UF_FILE']);
        }
        $vol = collect($vol)->except('ID')->toArray();

        $entity_data_class = $this->GetHLBlockEntityClass($id);

        $result = $entity_data_class::add($vol);
        $ID = $result->getId();
        if ($result->isSuccess()) {
            echo 'В справочник добавлена запись ' . $ID . "\n";
        } else {
            echo 'Ошибка добавления записи';
        }
    }

    /**
     * Создание пользовательского поля для хайлоад блока если не существует
     *
     * @param $hlbtID
     * @param $values
     */
    private function createUserFieldsIfNotExists($hlbtID, $values)
    {
        foreach ($values as $vol) {
            $vol['ENTITY_ID'] = "HLBLOCK_$hlbtID";
            unset($vol['ID']);
            $this->helper->UserTypeEntity()->addUserTypeEntityIfNotExists($vol['ENTITY_ID'], $vol['FIELD_NAME'], $vol);
        }
    }

    /**
     * Наличие записи в хайлоад блоке
     *
     * @param $name
     * @param $id
     *
     * @return mixed
     */
    private function HlbbtRecordExists($name, $id)
    {

        $entity_data_class = $this->GetHLBlockEntityClass($id);
        $res = $entity_data_class::GetList(["select" => [], "filter" => ['UF_NAME' => $name]]);

        $HLBlockTableNameByBlockID = $this->GetHLBlockTableNameByBlockID($id);

        $Result = new \CDBResult($res, $HLBlockTableNameByBlockID);


        return $Result->SelectedRowsCount();
    }

    /**
     * Экземпляр класса хайлоад блока по его ID
     *
     * @param $id
     *
     * @return mixed
     */
    private function GetHLBlockEntityClass($id)
    {
        $hlblock = HL\HighloadBlockTable::getById($id)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();

        return $entity_data_class;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    private function GetHLBlockTableNameByBlockID($id)
    {
        $hlblock = HL\HighloadBlockTable::getById($id)->fetch();

        return $hlblock['TABLE_NAME'];
    }

}