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

    /**
     * @param $data
     * @param $fileName
     */
    public function saveJson($data, $fileName)
    {
        $exportPath = Container::instance()->getExportPath();

        file_put_contents($exportPath . '/' . $fileName, json_encode($data));
    }

}