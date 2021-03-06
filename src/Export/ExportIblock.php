<?php


namespace BitrixMigration\Export;


use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\CLI;
use BitrixMigration\Export\Contracts\Exporter;
use BitrixMigration\Import\MigrationFilesUploadHelper;
use BitrixMigration\JsonReader;

class ExportIblock implements Exporter {
    use BitrixMigrationHelper, JsonReader, FilesSaveHelper;

    public $files = [];
    public $properties = [];
    public $settings;
    public $RelativeIblocks = [];
    public $iblockHasSKU;
    public $catalogSettings;
    public $SKUIblockID;
    public $filesPath;
    private $iblock_id;
    private $fileToSave = '/iblocks/iblock';
    private $dirToSave = '/iblocks/';

    public function __construct($iblock_id)
    {
        mkdir(container()->exportPath . $this->dirToSave, 0777);
        $this->iblock_id = $iblock_id;
        $this->filesPath = container()->exportPath . '/files';
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
        $iblockProperties = $this->IblockProperties();


        $i = 1;
        foreach ($iblockProperties as $property) {
            CLI::show_status($i++, count($iblockProperties));

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

    private function isHasSKU()
    {
        if ($this->iblockIsCatalog()) {
            $this->iblockHasSKU = \CCatalogSKU::GetInfoByProductIBlock();
        }
    }

    private function iblockIsCatalog()
    {
        if ($res = \CCatalog::GetByID($this->iblock_id)) {
            $this->catalogSettings = $res;
            $this->SKUIblockID = $res['PRODUCT_IBLOCK_ID'];

            return true;
        };

        return false;
    }

    /**
     * @return $this;
     */
    public function before()
    {
        $this->exportProperties();
        $this->getIblockSettings();
        $this->isHasSKU();

        return $this;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $res = json_encode($this);

        file_put_contents(container()->getExportPath() . $this->fileToSave . "_" . $this->iblock_id . '.json', $res);

        $this->copyFiles($this->getFiles());

        return $this;
    }

    public function after()
    {

    }


}