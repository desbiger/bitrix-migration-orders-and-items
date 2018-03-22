<?php


use BitrixMigration\JsonReader;

class ImportFile {
    use JsonReader;

    public $filesArrayFile = 'files/allFiles';

    public $files;

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getFileArray($id)
    {
        $path = $this->import_path . '/' . $this->files[$id];

        return \CFile::MakeFileArray($path);
    }


    /**
     * ImportFile constructor.
     */
    public function __construct()
    {
        $this->loadFiles();
    }

    /**
     *
     */
    public function loadFiles()
    {
        $this->files = $this->read($this->filesArrayFile);
    }

}