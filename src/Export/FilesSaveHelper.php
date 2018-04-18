<?php


namespace BitrixMigration\Export;


use BitrixMigration\CLI;

trait FilesSaveHelper {

    /**
     * Копируем файлы во временную папку
     *
     * @param $files
     */
    public function copyFiles($files, $path = null)
    {
        $i = 0;
        $total = count($files);
        $this->allFiles = $files;
        $path = $path ?: $this->filesPath;

        foreach ($files as $id => $file) {
            CLI::show_status($i++, $total, 30, ' | copy files');
            $newImgDir = $path . dirname($file);
            mkdir($newImgDir, 0777, true);
            copy($_SERVER['DOCUMENT_ROOT'] . $file, $path . $file);
        }

        $this->dumpFilesList();
    }

    /**
     * Выгружаем список файлов с привязкой к ID файла
     *
     * @param $filesPath
     */
    protected function dumpFilesList()
    {
        mkdir(container()->exportPath . '/files/', 0777);
        file_put_contents($this->filesPath . $this->allFilesJson . '_' . $this->filesDumps . '.json', json_encode($this->allFiles));
        $this->filesDumps++;
        $this->allFiles = [];
    }
}