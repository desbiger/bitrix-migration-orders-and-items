<?php


namespace BitrixMigration\Export;


use BitrixMigration\BitrixMigrationHelper;
use Sprint\Migration\HelperManager;
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;

class IblockProperty {
    use BitrixMigrationHelper;
    public $files;
    public $ImportRelativeIblocks;
    /**
     * @var mixed
     */
    private $property;

    /**
     * IblockProperty constructor.
     *
     * @param mixed $property
     */
    public function __construct($property)
    {
        \CModule::IncludeModule('highloadblock');
        $this->property = $property;
        $this->getValues();
    }

    private function getValues()
    {
        $helper = new HelperManager();
        if ($this->property['PROPERTY_TYPE'] == 'L') {
            $this->property['VALUES'] = $this->FetchAll(\CIBlockPropertyEnum::GetList([], ['PROPERTY_ID' => $this->property['ID']]));
        }
        if ($tableName = $this->property['USER_TYPE_SETTINGS']['TABLE_NAME']) {

            $HLBlockByTableName = $this->getHLBlockByTableName($tableName);

            $this->property['USER_TYPE_SETTINGS']['NAME'] = $HLBlockByTableName['NAME'];
            $this->property['HLBT']['USER_FIELDS'] = $this->getHLBTUserFields($HLBlockByTableName['ID']);
            $this->property['HILOAD'] = $this->getHiloadTable($tableName);
        }
        if ($this->property['PROPERTY_TYPE'] == 'E') {
            if ($this->property['LINK_IBLOCK_ID'] != $this->property['IBLOCK_ID']) {
                $this->ImportRelativeIblocks[] = (new ExportIblock($this->property['LINK_IBLOCK_ID']))->export();
            }
        }
    }

    /**
     * @param $tableName
     *
     * @return array
     */
    private function getHiloadTable($tableName)
    {
        global $DB;
        $res = [];

        foreach ($this->FetchAll($DB->Query('SELECT * FROM ' . $tableName)) as $record) {
            $record['UF_FILE'] = \CFIle::GetPath($record['UF_FILE']);

            if ($record['UF_FILE'] != null) {

                $this->files[] = $record['UF_FILE'];
            }
            $res[] = $record;
        };

        return $res;
    }

    /**
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }

    private function getHLBlockByTableName($tableName)
    {
        global $DB;

        return $DB->Query("SELECT * from b_hlblock_entity WHERE `TABLE_NAME` = '$tableName'")->Fetch();
    }

    /**
     * @param $HlbtID
     *
     * @return array
     */
    private function getHLBTUserFields($HlbtID)
    {
        $name = "HLBLOCK_$HlbtID";

        return $this->FetchAll(\CUserTypeEntity::getList([], ['ENTITY_ID' => $name]));
    }
}