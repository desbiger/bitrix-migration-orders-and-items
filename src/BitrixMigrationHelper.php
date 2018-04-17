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

    /**
     * @param $path
     *
     * @return array
     */
    public function scanDir($path, $without_extension = true)
    {
        $list = scandir($path);

        $array_diff = array_diff($list, ['..', '.']);
        if ($without_extension) {
            $array_diff = array_map(function ($file) use ($path) {
                if (!is_dir($path . $file))
                    return $file;
                unset($file);
            }, $array_diff);
        }

        return array_diff($array_diff, [null]);
    }


}