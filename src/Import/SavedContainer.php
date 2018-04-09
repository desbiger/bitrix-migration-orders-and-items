<?php


namespace BitrixMigration\Import;


trait SavedContainer {

    protected $fileName = 'container.json';

    /**
     * Загружаем данные прошлых импортов
     * @param $path
     */
    public function load_container($path)
    {
        $data = file_get_contents($path . '/' . $this->fileName);
        $array = json_decode($data, true);

        foreach ($this as $key=>$vol) {
            if ($this->$key == null)
                $this->$key = @$array[$key];
        }
    }

    public function saveContainer()
    {
        $res = json_encode((array)Container::instance());
        file_put_contents($this->import_path . '/' . $this->fileName, $res);
    }

    public function validKey($key)
    {
        $res = !preg_match("\\", $key);

        return $res;
    }


}