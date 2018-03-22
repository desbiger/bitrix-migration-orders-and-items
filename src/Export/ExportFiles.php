<?php


namespace BitrixMigration\Export;


class ExportFiles {

    /**
     * @param $ID
     *
     * @return mixed
     */
    public function getFilePathByID($ID)
    {
        return \CFile::getPath($ID);
    }

    /**
     * @param $ID
     * @param $destination
     *
     * @return mixed
     */
    public function copyFile($ID, $destination)
    {
        $path = $this->getFilePathByID($ID);
        copy(DOCROOT . $path, $destination);

        return $path;
    }


}