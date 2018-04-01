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
        $this->data = $data;
        $this->import_path = $import_path;
        $this->separate();
        $this->helper = new HelperManager();
        $this->createIBlock();
    }

    /**
     *
     */
    private function separate()
    {
        $this->settings = $this->data['settings'];
        $this->properties = $this->data['properties'];
        $this->files = $this->data['files'];
        $this->RelativeIblocks = $this->data['RelativeIblocks'];
    }

    /**
     *
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
     * @param $IBLOCK_TYPE_ID
     */
    private function createIblockType($IBLOCK_TYPE_ID)
    {
        $this->helper->Iblock()->addIblockTypeIfNotExists(['ID' => $IBLOCK_TYPE_ID]);
    }

    /**
     *
     */
    private function createProperties()
    {
        foreach ($this->properties as $property) {
            if ($property['USER_TYPE_SETTINGS'])
                $hlBlockID = $this->createHiloadBlock($property);
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

    private function createHiloadBlock($property)
    {
        $fields = collect($property['USER_TYPE_SETTINGS'])->only(['NAME', 'TABLE_NAME'])->toArray();
        $id = $this->helper->Hlblock()->addHlblockIfNotExists($fields);
        $this->createUserFields($id, $property['HLBT']['USER_FIELDS']);
        foreach ($property['HILOAD'] as $vol) {
            $this->addHiloadBlockRecord($id, $vol);
        }
    }

    private function addHiloadBlockRecord($id, $vol)
    {
        if ($vol['UF_FILE']) {
            $vol['UF_FILE'] = \CFile::MakeFileArray($this->import_path . '/files/' . $vol['UF_FILE']);
        }
        $vol = collect($vol)->except('ID')->toArray();
        $hlblock = HL\HighloadBlockTable::getById($id)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();

        $result = $entity_data_class::add($vol);
        $ID = $result->getId();
        if ($result->isSuccess()) {
            echo 'В справочник добавлена запись ' . $ID . '<br />';
        } else {
            echo 'Ошибка добавления записи';
        }
    }

    /**
     * @param $hlbtID
     * @param $values
     */
    private function createUserFields($hlbtID, $values)
    {
        foreach ($values as $vol) {
            $vol['ENTITY_ID'] = "HLBLOCK_$hlbtID";
            unset($vol['ID']);
            $this->helper->UserTypeEntity()->addUserTypeEntityIfNotExists($vol['ENTITY_ID'],$vol['FIELD_NAME'], $vol);
        }
    }

}