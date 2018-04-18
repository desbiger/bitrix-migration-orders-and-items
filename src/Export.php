<?php

namespace BitrixMigration;

//test
use BitrixMigration\Export\ExportDelivery;
use BitrixMigration\Export\ExportIblock;
use BitrixMigration\Export\ExportOrders;
use BitrixMigration\Export\ExportPaySystems;
use BitrixMigration\Export\ExportPersonType;
use BitrixMigration\Export\ExportPrices;
use BitrixMigration\Export\ExportPriceType;
use BitrixMigration\Export\ExportProducts;
use BitrixMigration\Export\ExportUserFields;
use BitrixMigration\Export\ExportUsers;
use BitrixMigration\Export\FilesSaveHelper;
use Sprint\Migration\HelperManager;

class Export {
    use BitrixMigrationHelper, FilesSaveHelper;

    public $sections_dir = 'sections';
    public $ExportOrders;
    public $paySystem = '/paySystem.json';
    public $prices;
    public $filesDumps = 0;
    protected $products_dir_name = 'products';

    public $allFiles = [];
    public $filesPath;

    protected $products;
    private $export_folder_path;
    public $deliveryJson = '/delivery.json';
    public $personType = '/personType.json';
    public $allFilesJson = '/allFiles';
    public $ordersJson = '/orders/orders';
    private $products_iblock_id;

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
        $this->products_iblock_id = $products_iblock_id;
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
    public function dumpProducts($items_per_file = 1000, $from)
    {
        $productsPath = $this->export_folder_path . '/' . $this->products_dir_name;
        $allFiles = [];
        mkdir($productsPath);


        $this->products->getAllProducts(function ($result, $iterarion, $files) use ($productsPath, $allFiles) {


            foreach ($result as $key => $item) {
                CLI::show_status($key + 1, count($result));
                $this->prices[$item['ID']] = (new ExportPrices($this->products_iblock_id))->getPrices($item['ID']);
            }
            //TODO сделать выгрузку  ком предложений $this->dumpOffers();
            $this->dumpPrices($iterarion);

            file_put_contents($productsPath . "/items_$iterarion.json", json_encode($result));

            $this->copyFiles($files);

        }, $items_per_file, $from);


        $this->dumpFilesList();

        return $this;
    }

    /**
     * @param $iblock_id
     *
     * @return $this
     */
    public function export($productsPerPage = 1000, $from = 0)
    {
        $this->dumpPriceTypes();
        $this->dumpIblock();
        $this->dumpProducts($productsPerPage, $from);
        $this->dumpSections();
        $this->dumpSectionsUserFields();
        $this->dumpPaySystems();
        $this->dumpPersonType();
        $this->dumpDelivery();
        $this->dumpUsers();
        $this->dumpOrders();

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

        mkdir($this->export_folder_path . '/orders');

        $this->ExportOrders->getAll(500, function ($list, $page) {
            $res = json_encode($list);
            file_put_contents($this->export_folder_path . $this->ordersJson . "_$page.json", $res);
        });


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

    /**
     * Сохраняем пользовательские поля для разделов инфоблока
     */
    private function dumpSectionsUserFields()
    {
        $UFs = new ExportUserFields("IBLOCK_{$this->products_iblock_id}_SECTION");
        $list = json_encode($UFs->getAll());
        file_put_contents($this->export_folder_path . '/sections_uf.json', $list);
    }

    /**
     *
     */
    private function dumpPrices($iteration)
    {

        mkdir($this->export_folder_path . "/prices");
        $path = $this->export_folder_path . "/prices/prices_$iteration.json";

        file_put_contents($path, json_encode($this->prices));
        $this->prices = [];
    }

    private function dumpIblock()
    {
        $Exporter = new ExportIblock($this->products_iblock_id);
        if ($skuID = $Exporter->SKUIblockID) {
            $SKUIblock = new ExportIblock($skuID);
            file_put_contents($this->export_folder_path . '/SKUiblock.json', json_encode($SKUIblock));
        }
        file_put_contents($this->export_folder_path . '/iblock.json', json_encode($Exporter));

        $this->copyFiles($Exporter->getFiles());
    }

    private function dumpPriceTypes()
    {
        $exporter = new ExportPriceType();
        $this->saveFile('priceTypes.json', $exporter->getAll());
    }

    /**
     * @param $fileName
     * @param $data
     */
    private function saveFile($fileName, $data)
    {
        $this->makeDirs($fileName);

        file_put_contents($this->export_folder_path . '/' . $fileName, json_encode($data));
    }

    /**
     * @param $fileName
     */
    private function makeDirs($fileName)
    {
        if (count($dirs = explode("/", $fileName)) > 1) {
            unset($dirs[count($dirs) - 1]);

            mkdir($this->export_folder_path . '/' . implode("/", $dirs), 0777, true);
        };
    }

}