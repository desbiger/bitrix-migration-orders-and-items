<?php

namespace BitrixMigration;


class Export {
    public $sections_dir = 'sections';
    protected $products_dir_name = 'products';

    public $allFiles = [];
    public $filesPath;

    protected $products;
    private $import_folder_path;

    /**
     * @param $product_iblock_id
     *
     * @return Export
     */
    static function init($product_iblock_id, $import_folder_path)
    {
        return new self($product_iblock_id, $import_folder_path);
    }

    public function __construct($products_iblock_id, $import_folder_path)
    {
        $this->products = new ExportProducts($products_iblock_id);
        $this->import_folder_path = $import_folder_path;

        $this->filesPath = $this->import_folder_path . '/files';
        mkdir($this->filesPath);
    }


    /**
     * Сохраняем все свойства инфоблока товаров
     * @return $this
     */
    public function dumpProperties()
    {
        file_put_contents($this->import_folder_path.'/properties.json', json_encode($this->products->IblockProperties));
        return $this;
    }


    /**
     *  Выгружаем все элементы инфоблока с доп свойствами и картинками
     */
    public function dumpProducts($items_per_file = 1000)
    {
        $productsPath = $this->import_folder_path . '/' . $this->products_dir_name;
        $allFiles = [];
        mkdir($productsPath);


        $this->products->getAllProducts(function ($result, $iterarion, $files) use ($productsPath, $allFiles) {

            file_put_contents($productsPath . "/items_$iterarion.json", json_encode($result));

            $this->copyFiles($files);

        }, $items_per_file);

        $this->dumpFilesList();

        return $this;
    }

    /**
     * Выгружаем разделы заданного инфоблока
     */
    public function dumpSections($items_per_file = 1000)
    {

        $sectionsPath = $this->import_folder_path . '/' . $this->sections_dir;
        mkdir($sectionsPath);


        $this->products->getAllSections(function ($result, $iterarion, $files) use ($sectionsPath) {

            file_put_contents($sectionsPath . "/sections_$iterarion.json", json_encode($result));
            $this->copyFiles($files);

        }, $items_per_file);

        $this->dumpFilesList();

        return $this;
    }

    /**
     * Задаем путь к папке с товарами
     *
     * @param string $products_dir_name
     */
    public function setProductsDirName($products_dir_name)
    {
        $this->products_dir_name = $products_dir_name;
    }

    /**
     * Выгружаем список файлов с привязкой к ID файла
     *
     * @param $filesPath
     */
    protected function dumpFilesList()
    {

        file_put_contents($this->filesPath . '/allFiles.json', json_encode($this->allFiles));
    }

    /**
     * Копируем файлы во временную папку
     *
     * @param $files
     */
    public function copyFiles($files)
    {
        foreach ($files as $file) {
            $newImgDir = $this->filesPath . dirname($file);
            mkdir($newImgDir, 0777, true);
            copy($_SERVER['DOCUMENT_ROOT'] . $file, $this->filesPath . $file);
        }
        $this->allFiles = $this->allFiles + $files;
    }

}