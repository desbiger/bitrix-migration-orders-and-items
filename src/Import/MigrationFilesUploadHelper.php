<?php


namespace BitrixMigration\Import;


use BitrixMigration\CLI;
use BitrixMigration\Import\ProductsReader\Files;

trait MigrationFilesUploadHelper {

    public $allFilesArray = [];

    /**
     * Загрузка всех картинок
     */
    public function loadFiles()
    {
        $list = new Files();

        echo "\n Loading Files IDs";
        while ($array = $list->getNextFile()) {
            $this->allFilesArray += $array;
        }
    }


    /**
     * @param $oldID
     *
     * @return null
     */
    protected function getFileArray($oldID)
    {
        if ($oldID) {
            $path = Container::instance()->import_path . '/files' . $this->allFilesArray[$oldID];

            return \CFile::MakeFileArray($path);
        }

        return null;
    }

}