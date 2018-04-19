<?php


namespace BitrixMigration;


class Exporter {

    public $repo;

    static function init()
    {
        return new self;
    }

    /**
     * @param \Exporter $object
     *
     * @return $this
     */
    public function register(\BitrixMigration\Export\Contracts\Exporter $object)
    {
        $this->repo[] = $object;
        return $this;
    }

    /**
     * @param array $array
     *
     * @return $this
     */
    public function registerMany(array $array)
    {
        $this->repo = $this->repo + $array;

        return $this;
    }


    /**
     * Выполняем экспорт всех подключенных экспортеров
     */
    public function export()
    {
        /** @var \BitrixMigration\Export\Contracts\Exporter $exporter */
        foreach ($this->repo as $exporter) {
            $exporter->before()->execute()->after();
        }
    }


}