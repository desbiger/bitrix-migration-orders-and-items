<?php

namespace BitrixMigration\Import\ProductsReader;

use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\CLI;
use BitrixMigration\Import\Container;
use BitrixMigration\JsonReader;

class FilesReader implements DevidedFilesInterface {
    public $folder = '';
    public $arrayFinish = false;
    protected $containerIDsFieldName;
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
    public function __construct()
    {

        $this->import_path = Container::instance()->import_path;
        $this->filesPath = $this->import_path . $this->folder;

        if ($this->containerIDsFieldName) {
            $list = Container::instance()->{$this->containerIDsFieldName};
            $this->setLoadedIDS(array_keys($list));
        }
    }


    /**
     * @return array|bool
     *
     * возвращает массив вида
     * [
     *  Текущий элемент,
     *  количество элементов текущего массива,
     *  счетчик,
     *  названияе файла,
     *  ключ текущего элемента массива.
     * ]
     */
    public function getNextElement()
    {
        if (count($this->Elements) && !$this->arrayFinish) {
            $currentElement = current($this->Elements);

            if ($this->isLoaded($currentElement['ID'])) {
                return $this->passElement($currentElement);
            }

            $currentElement = $this->updateElement($currentElement);

            $return = [
                $currentElement,
                count($this->Elements),
                $this->counter++,
                $this->currentFile,
                key($this->Elements)
            ];

            $currentElement = null;
            $this->next();

            return $return;
        }


        if ($this->getNextChunk()) {

            return $this->getNextElement();
        }

        return false;
    }

    /**
     * @return array|bool
     */
    public function getNextFile()
    {
        if ($this->getNextChunk()) {
            return $this->Elements;
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

            $file = str_replace(".json", '', $file);
            if (!$this->isReaded($file)) {
                $this->readedChunks[] = $file;
                $this->currentFile = $file;
                $this->counter = 0;
                $this->Elements = $this->read($this->folder . $file);
                $this->arrayFinish = false;

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

    /**
     * Кастумизация обработки
     *
     * @param $nextElement
     *
     * @return mixed
     */
    public function updateElement($nextElement)
    {
        return $nextElement;
    }

    private function next()
    {
        if (next($this->Elements) === false)
            $this->arrayFinish = true;
    }

    /**
     * @param $currentElement
     *
     * @return array|bool
     */
    private function passElement(&$currentElement)
    {
        $this->counter++;
        CLI::show_status($this->counter, count($this->Elements), 30, " | pass element ." . $currentElement['ID']);
        $currentElement = null;
        $this->next();

        return $this->getNextElement();
    }
}