<?php


namespace BitrixMigration;


trait BitrixMigrationHelper {


    /**
     * @param $array
     * @param $keys
     *
     * @return array
     */
    protected function arrayOnly($array, $keys)
    {
        $fields = [];
        foreach ($keys as $key) {
            $fields[$key] = $array[$key];
        }

        return $fields;
    }
}