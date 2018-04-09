<?php

namespace BitrixMigration\Import\ProductsReader;

use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\Import\Container;
use BitrixMigration\JsonReader;

class FilesReader implements DevidedFilesInterface {
    public $folder = '';
    use BitrixMigrationHelper, JsonReader;
    public $Elements = [];
    public $readedChunks;
    public $counter = 0;
    public $currentFile;
    public $import_path;
    private $filesPath;

    public $loadedIDs = [];

    /**
     * ProductsReaderInterface constructor.
     *
     * @param $filesPath
     */
    public function __construct($filesPath, $import_path)
    {
        $this->filesPath = $filesPath;
        $this->import_path = $import_path;
    }


    /**
     * @return array|bool
     */
    public function getNextElement()
    {
        if (count($this->Elements) && $nextElement = next($this->Elements)) {
            if ($this->isLoaded($nextElement['ID'])) {
                echo "\rpass ".$nextElement['ID'] . ' file: '.$this->currentFile;
                $this->counter++;
                $nextElement = null;

                return $this->getNextElement();
            }

            return [$nextElement, count($this->Elements), $this->counter++, $this->currentFile];

        }


        if ($this->getNextChunk()) {

            return $this->getNextElement();
        }

        return false;
    }


    /**
     * Получаем слудующий файл
     * @return bool|mixed
     */
    private function getNextChunk()
    {
        echo "\n";
        $this->Elements = [];
        $path = $this->filesPath;
        $files = $this->scanDir($path);


        foreach ($files as $file) {
            if (!$this->isReaded($file)) {
                $this->readedChunks[] = $file;
                $this->currentFile = $file;
                $this->counter = 0;
                $this->Elements = $this->read($this->folder . $file);

                return true;
            }

        }

        return false;
    }

    /**
     * Проверка открывался ли уже переданный файл
     *
     * @param $file
     *
     * @return bool
     */
    private function isReaded($file)
    {
        return in_array($file, $this->readedChunks);
    }

    /**
     * @param $ID
     *
     * @return bool
     */
    private function isLoaded($ID)
    {
        $res = in_array($ID, $this->loadedIDs);
        return $res;
    }
    public function setLoadedIDS($list)
    {
        $this->loadedIDs = $list;
    }
}