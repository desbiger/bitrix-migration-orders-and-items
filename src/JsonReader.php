<?php


namespace BitrixMigration;


trait JsonReader {
    private $import_path;
    /**
     * @param $string
     *
     * @return mixed
     */
    protected function read($string)
    {
        return (array)json_decode(file_get_contents($this->import_path . "/$string.json"), true);
    }
}