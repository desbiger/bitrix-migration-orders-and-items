<?php


namespace BitrixMigration;


use BitrixMigration\Import\Container;

trait JsonReader {

    /**
     * @param $string
     *
     * @return mixed
     */
    protected function read($string)
    {
        $importPath = Container::instance()->getImportPath();
        return (array)json_decode(file_get_contents($importPath . "/$string.json"), true);
    }
}