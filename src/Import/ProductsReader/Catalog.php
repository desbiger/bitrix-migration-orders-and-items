<?php

namespace BitrixMigration\Import\ProductsReader;

use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\JsonReader;

class File implements DevidedFilesInterface {
    use BitrixMigrationHelper, JsonReader;
    public $Elements = [];
    public $readedChunks;
    public $counter = 0;
    public $currentFile;
    private $filesPath;

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
        if (count($this->Elements) && $nextElement = next($this->Elements))
            return [$nextElement, count($this->Elements),$this->counter++,$this->currentFile];


        if ($this->getNextChunk()) {
            if (count($this->Elements) && $nextElement = next($this->Elements))
                return [$nextElement, count($this->Elements),$this->counter++,$this->currentFile];

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

        $path = $this->filesPath;
        $files = $this->scanDir($path);
        $this->Elements = [];
        foreach ($files as $file) {

            if (!$this->isReaded($file)) {
                $this->readedChunks[] = $file;
                $this->currentFile = $file;
                $this->counter = 0;
                $this->Elements = $this->read('/products/' . $file);

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
}