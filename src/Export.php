<?php

namespace BitrixMigration;

//test
use BitrixMigration\Export\ExportDelivery;
use BitrixMigration\Export\ExportOrders;
use BitrixMigration\Export\ExportPaySystems;
use BitrixMigration\Export\ExportPersonType;
use BitrixMigration\Export\ExportProducts;
use BitrixMigration\Export\ExportUsers;

class Export {
    public $sections_dir = 'sections';
    public $ExportOrders;
    public $paySystem = '/paySystem.json';
    protected $products_dir_name = 'products';

    public $allFiles = [];
    public $filesPath;

    protected $products;
    private $export_folder_path;
    public $deliveryJson = '/delivery.json';
    public $personType = '/personType.json';
    public $allFilesJson = '/allFiles.json';
    public $ordersJson = '/orders.json';

    /**
     * @param $product_iblock_id
     *
     * @return Export
     */
    static function init($product_iblock_id, $export_folder_path)
    {
        return new self($product_iblock_id, $export_folder_path);
    }


    /**
     * Export constructor.
     *
     * @param $products_iblock_id
     * @param $export_folder_path
     */
    public function __construct($products_iblock_id, $export_folder_path)
    {
        $this->ExportOrders = new ExportOrders();
        $this->products = new ExportProducts($products_iblock_id);
        $this->users = new ExportUsers();
        $this->export_folder_path = $export_folder_path;

        $this->filesPath = $this->export_folder_path . '/files';
        mkdir($this->filesPath);
    }

    /**
     * импорт свойств, товаров, пользователей, заказов, разделов
     */
    public function export()
    {
        $this->dumpProperties()->dumpProducts()->dumpUsers()->dumpOrders()->dumpSections();
    }


    /**
     * Сохраняем все свойства инфоблока товаров
     * @return $this
     */
    public function dumpProperties()
    {
        file_put_contents($this->export_folder_path . '/properties.json', json_encode($this->products->IblockProperties));

        return $this;
    }


    /**
     *  Выгружаем все элементы инфоблока с доп свойствами и картинками
     */
    public function dumpProducts($items_per_file = 1000)
    {
        dd(123);
        $productsPath = $this->export_folder_path . '/' . $this->products_dir_name;
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
     * @return $this
     */
    public function dumpUsers()
    {
        $users = $this->users->getAllUsers();
        file_put_contents($this->export_folder_path . '/users.json', json_encode($users));

        return $this;
    }

    /**
     * Скидываем все заказы в формате User_id => orders
     *
     * @return $this
     */
    public function dumpOrders()
    {
        $res = [];
        foreach ($this->users->getAllUsers() as $user) {
            $res[$user['ID']] = $this->ExportOrders->getUserOrders($user['ID']);
        }
        $res = json_encode($res);
        file_put_contents($this->export_folder_path . $this->ordersJson, $res);

        return $this;
    }


    /**
     * Выгружаем разделы заданного инфоблока
     */
    public function dumpSections($items_per_file = 1000)
    {

        $sectionsPath = $this->export_folder_path . '/' . $this->sections_dir;
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

        file_put_contents($this->filesPath . $this->allFilesJson, json_encode($this->allFiles));
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


    /**
     * Список всех дооставок
     *
     * @return array
     */
    public function dumpDelivery()
    {
        $deliveryExporter = new ExportDelivery();

        $delivery = $deliveryExporter->getAll(function ($file) {
            $this->copyFiles($file);
        });
        $this->dumpFilesList();

        file_put_contents($this->export_folder_path . $this->deliveryJson, json_encode($delivery));
    }

    /**
     *
     */
    public function dumpPaySystems()
    {
        $paySystems = new ExportPaySystems();

        $list = $paySystems->getAll();

        $files = [];
        foreach ($list as $ps) {
            if ($ps['ACTION']['LOGOTIP'])
                $files[] = $ps['ACTION']['LOGOTIP'];
        }

        if ($files) {
            $this->copyFiles($files);
            $this->dumpFilesList();
        }


        file_put_contents($this->export_folder_path . $this->paySystem, json_encode($list));
    }


    /**
     *
     */
    public function dumpPersonType()
    {
        $PersonTypeExport = ExportPersonType::init();
        $list = $PersonTypeExport->export();

        file_put_contents($this->export_folder_path . $this->personType, json_encode($list));
    }

}