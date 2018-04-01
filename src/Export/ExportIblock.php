<?php


namespace BitrixMigration\Export;


use BitrixMigration\BitrixMigrationHelper;

class ExportIblock {
    use BitrixMigrationHelper;

    public $files = [];
    public $properties = [];
    public $settings;
    public $RelativeIblocks = [];
    private $iblock_id;

    public function __construct($iblock_id)
    {

        $this->iblock_id = $iblock_id;
        $this->exportProperties();
        $this->getIblockSettings();
    }

    /**
     *
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     *
     */
    private function exportProperties()
    {
        foreach ($this->IblockProperties() as $property) {
            $iblockProperty = new IblockProperty($property);

            $this->properties[] = $iblockProperty->getProperty();

            if (is_array($iblockProperty->files))
                $this->files = $this->files + $iblockProperty->files;

            if (is_array($iblockProperty->ImportRelativeIblocks))
                $this->RelativeIblocks = $this->RelativeIblocks + $iblockProperty->ImportRelativeIblocks;
        }

    }

    /**
     * @return array
     */
    private function IblockProperties()
    {
        return $this->FetchAll(\CIBlockProperty::GetList([], ['IBLOCK_ID' => $this->iblock_id]));
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    private function getIblockSettings()
    {
        $this->settings = \CIBlock::GetByID($this->iblock_id)->Fetch();
    }

    /**
     * @return mixed
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return array
     */
    public function getRelativeIblocks()
    {
        return $this->RelativeIblocks;
    }

}