<?php


namespace BitrixMigration\Export;


use BitrixMigration\CLI;

trait FilesSaveHelper {


    private $filesDumps = 1;

    /**
     * Копируем файлы во временную папку
     *
     * @param $files
     */
    public function copyFiles($files, $path = null, $resave = true)
    {
        $i = 0;
        $total = count($files);
        $this->allFiles = $files;
        $path = $path ?: $this->filesPath();

        echo "\n";

        foreach ($files as $id => $file) {
            echo "\r copy files " . $this->indicator($total, $i++) . " | current file: $file";

            $newImgDir = $path . dirname($file);
            mkdir($newImgDir, 0777, true);

            copy($_SERVER['DOCUMENT_ROOT'] . $file, $path . $file);
        }

        $this->dumpFilesList();
        echo "\n";
    }

    public function indicator($total, $current)
    {
        $procent = round($current / ($total / 100));

        return $procent . "%";
    }


    /**
     * Выгружаем список файлов с привязкой к ID файла
     *
     * @param $filesPath
     */
    protected function dumpFilesList()
    {

        $filesPath = $this->filesPath();

        mkdir($filesPath, 0777);
        file_put_contents($filesPath . 'allFiles_' . $this->filesDumps . '.json', json_encode($this->allFiles));
        $this->filesDumps++;
        $this->allFiles = [];
    }

    private function filesPath()
    {
        return container()->exportPath . '/files/';
    }
}