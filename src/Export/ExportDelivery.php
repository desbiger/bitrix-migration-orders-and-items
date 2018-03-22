<?php


namespace BitrixMigration\Export;


use BitrixMigration\BitrixMigrationHelper;

class ExportDelivery {

    use BitrixMigrationHelper;

    public $filesExporter;

    public function __construct()
    {
        $this->filesExporter = new ExportFiles();
    }

    /**
     * Возвращем массив всех служб доставки с выполнением callback функции с передаваемым параметром
     * путь к логотипу службы
     *
     * @param callable|null $callback
     *
     * @return array
     */
    public function getAll(callable $callback = null)
    {

        return $this->FetchAll(\CSaleDelivery::GetList(), function ($item) use ($callback) {
            $fileId = null;
            if ($callback && $item['LOGOTIP']) {
                $fileId = $item['LOGOTIP'];
                $callback([$fileId => $this->filesExporter->getFilePathByID($item['LOGOTIP'])]);
            }

            return $item;
        });
    }


}