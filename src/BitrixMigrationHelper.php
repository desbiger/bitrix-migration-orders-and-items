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

    /**
     * @param \CDBResult $result
     *
     * @return array
     */
    public function FetchAll(\CDBResult $result, callable $callback = null)
    {
        $res = [];
        while ($t = $result->Fetch()) {
            if ($callback) {
                $res[] = $callback($t);
            } else {
                $res[] = $t;
            }
        }

        return $res;
    }

}