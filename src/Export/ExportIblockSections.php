<?php


namespace BitrixMigration\Export;


use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\Export\Contracts\Exporter;

class ExportIblockSections implements Exporter {

    use BitrixMigrationHelper, FilesSaveHelper;
    public $exportPath;
    public $items_per_file;

    /**
     * @var
     */
    private $iblockID;

    public function __construct($iblockID, $items_per_file)
    {
        $this->iblockID = $iblockID;
        $this->exportPath = container()->exportPath . '/sections/';
        $this->items_per_file = $items_per_file;
    }

    /**
     * Иерархический массив разделов инфоблока
     * с выполнение колбэк функции каждые $iteration элементов
     *
     * @param $callback
     * @param $chunks
     */
    public function getAllSections($callback, $chunks)
    {
        $res = [];
        $i = 0;
        $page = 1;
        $sections = $this->getSection();
        foreach ($sections as $section) {
            $res[] = $section;
            $i++;
            if ($i == $chunks) {
                $i = 0;
                $callback($res, $page, $this->files);
                $page++;

                $res = [];
            }
        }
        $callback($res, $page, $this->files);
        $this->files = [];
    }

    /**
     * Разделы инфоблока разбитые по иерархии
     *
     * @param null $section_id
     *
     * @return array
     */
    protected function getSection($section_id = null)
    {
        $list = \CIBlockSection::GetList([], [
            'IBLOCK_ID'  => $this->iblockID,
            'SECTION_ID' => $section_id
        ], null, ['UF_*']);

        $sections = $this->FetchAll($list, function ($section) {
            $subsections = $this->getSection($section['ID']);

            if (count($subsections))
                $section['SUBSECTIONS'] = $subsections;

            return $section;

        });

        return count($sections) ? $sections : false;
    }

    /**
     * @return $this;
     */
    public function before()
    {
        mkdir($this->exportPath);

        return $this;
    }

    /**
     * @return $this
     */
    public function execute()
    {


        $this->getAllSections(function ($result, $iterarion, $files) {
            file_put_contents($this->exportPath . "/sections_$iterarion.json", json_encode($result));
            $this->copyFiles($files);

        }, $this->items_per_file);

        return $this;
    }

    public function after()
    {
        mkdir($this->exportPath . '/user_fields/', 0777);

        $UFs = new ExportUserFields("IBLOCK_{$this->iblockID}_SECTION");
        $list = json_encode($UFs->getAll());
        file_put_contents($this->exportPath . '/user_fields/sections_' . $this->iblockID . '_uf.json', $list);
    }
}