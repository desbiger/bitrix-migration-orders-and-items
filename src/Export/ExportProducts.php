<?php

namespace BitrixMigration\Export;

use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\CLI;
use BitrixMigration\Export\Contracts\Exporter;

class ExportProducts implements Exporter {
    use BitrixMigrationHelper, FilesSaveHelper;
    /**
     * Id инфолока переноса
     * @var
     */
    public $iblock_id;
    public $IblockProperties;
    public $CatalogPrices;
    public $products_dir_name = '/products/';
    public $from;
    public $prices;
    public $exportPath;
    /**
     * Массив переносимых файлов формата ID => путь
     * @var array
     */
    protected $files = [];
    /**
     * Ключи, по которым будем переносить картинки
     * @var array
     */
    protected $files_keys = [
        'DETAIL_PICTURE',
        'PREVIEW_PICTURE',
        'PICTURE'
    ];
    /**
     * поля которые будем переносить
     * @var array
     */
    protected $fields = [
        'ID',
        'CREATED_BY',
        'IBLOCK_SECTION_ID',
        'ACTIVE',
        'SORT',
        'NAME',
        'PREVIEW_PICTURE',
        'PREVIEW_TEXT',
        'DETAIL_PICTURE',
        'DETAIL_TEXT',
        'LOCK_STATUS',
        'CODE',
        'XML_ID',
        'EXTERNAL_ID',
        'BP_PUBLISHED'
    ];
    /**
     * @var
     */
    private $perPage;


    /**
     * ImportProducts constructor.
     *
     * @param $iblock_id
     */
    private $propertyKeys;

    public function __construct($iblock_id, $perPage, $from)
    {
        $this->propertyKeys = [
            'ID',
            'NAME',
            'VALUE',
            'CODE',
            'PROPERTY_TYPE',
            'LINK_IBLOCK_ID',
            'DESCRIPTION',
            'SEARCHABLE',
            'PROPERTY_VALUE_ID',
            'VALUE_XML_ID'
        ];

        $this->exportPath = container()->exportPath;
        $this->iblock_id = $iblock_id;
        $this->IblockProperties = $this->getIblockProperties();
        $this->CatalogPrices = ExportPrices::init($this->iblock_id);
        $this->perPage = $perPage;
        $this->from = $from;
    }

    /**
     * Сеттер id инфоблока
     *
     * @param mixed $iblock_id
     */
    public function setIblockId($iblock_id)
    {
        $this->iblock_id = $iblock_id;
    }


    /**
     * Массив всех элементов инфоблока
     *
     * @param $callback - функция, исполняемая каждые $chunks элементов
     * @param $chunks - количество элементов за один шаг
     */
    public function getAllProducts($callback, $chunks, $from = 0)
    {
        $items = [];
        $list = \CIblockElement::getList([], ['IBLOCK_ID' => $this->iblock_id]);
        $count = $list->SelectedRowsCount();

        $i = 0;
        $page = 1;
        while ($item = $list->GetNextElement()) {
            $props = [];
            $fields = [];

            $i++;

            CLI::show_status(($page - 1) * $chunks + $i, $count);

            if (($i + ($page * $chunks)) < $from) {
                if ($i == $chunks) {
                    $i = 0;
                    $page++;
                }
                continue;
            }

            if (!$this->NextStep($callback, $chunks, $i, $items, $page))
                return;

            $fields = $this->arrayOnly($item->getFields(), $this->fields);

            file_put_contents($this->exportPath . '/log.log', $fields['ID']);


            $props = $item->getProperties();

            $this->dumpPropertyFiles($props);
            foreach ($props as $prop){
                $fields['PROPS'][$prop['CODE']] = $this->arrayOnly($prop, $this->propertyKeys);
            }
            //            $fields['OFFERS'] = $this->getOffers($fields['ID']);

            $this->dumpFiles($fields);

            $items[] = $fields;
            unset($fields, $props);
        }

        $callback($items, $page, $this->files);
        $this->files = [];

    }

    /**
     * Получаем пути к файлам, если таковые есть
     *
     * @param $fields
     */
    private function dumpFiles($fields)
    {
        foreach ($this->files_keys as $key) {
            $ID = $fields[$key];
            $this->dumpFileByID($ID);
        }
    }

    /**
     * Делаем сброс данных
     *
     * @param $callback
     * @param $chunks
     * @param $i
     * @param $items
     * @param $page
     *
     * @return bool
     */
    protected function NextStep($callback, $chunks, &$i, &$items, &$page)
    {
        if ($i == $chunks) {

            $i = 0;
            if ($callback($items, $page, $this->files) === false)
                return false;

            $this->files = [];
            $page++;
            $items = [];
        }

        return true;
    }


    /**
     * Комерческие предожения
     *
     * @param $elementId
     *
     * @return mixed
     */
    public function getOffers($elementId)
    {
        //TODO перенсти на уровень дампа продукции
        $offersArray = \CIBlockPriceTools::GetOffersArray($this->iblock_id, $elementId);

        return $offersArray;
    }

    /**
     * Получаем все свойства инфоблока
     *
     * @return array
     */
    public function getIblockProperties()
    {
        $res = [];
        $list = \CIBlockProperty::GetList([], ['IBLOCK_ID' => $this->iblock_id]);
        while ($p = $list->GetNext()) {
            $res[] = $p;
        }

        return $res;
    }

    public function dumpPropertyFiles($properties)
    {
        foreach ($properties as $property) {
            if ($property['PROPERTY_TYPE'] == 'F') {
                if (is_array($property['VALUE'])) {
                    foreach ($property['VALUE'] as $id) {
                        $this->dumpFileByID($id);
                    }
                    continue;
                }
                if ($property['VALUE'])
                    $this->dumpFileByID($property['VALUE']);
            }
        }
    }

    /**
     * @param $ID
     */
    private function dumpFileByID($ID)
    {
        $filePath = \CFIle::GetPath($ID);
        if ($filePath) {
            $this->files[$ID] = $filePath;
        }
    }

    /**
     * @param $PRODUCT_XML_ID
     *
     * @return mixed
     */
    public function getProductIdByXMLID($PRODUCT_XML_ID)
    {
        return \CIBlockElement::GetList([], [
            'IBLOCK_ID' => $this->iblock_id,
            'XML_ID'    => $PRODUCT_XML_ID
        ])->Fetch()['ID'];
    }


    /**
     * @return $this;
     */
    public function before()
    {
        $productsPath = container()->exportPath . $this->products_dir_name;
        $allFiles = [];
        mkdir($productsPath);

        return $this;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $this->getAllProducts(function ($result, $iterarion, $files) {
            echo "\n";
            foreach ($result as $key => $item) {

                $exportPrices = new ExportPrices($this->iblock_id);

                $this->prices[$item['ID']] = $exportPrices->getPrices($item['ID']);

                unset($exportPrices);


                CLI::show_status($key + 1, count($result), 30, " | Export Prices");
            }

            $this->dumpPrices($iterarion);

            file_put_contents($this->exportPath . $this->products_dir_name . "/items_$iterarion.json", json_encode($result));

            $this->copyFiles($files,null,false);

        }, $this->perPage, $this->from);

        return $this;

    }

    public function after()
    {

        $this->dumpFilesList();
    }

    /**
     *
     */
    private function dumpPrices($iteration)
    {

        mkdir(container()->exportPath . "/prices");
        $path = container()->exportPath . "/prices/prices_$iteration.json";

        file_put_contents($path, json_encode($this->prices));
        $this->prices = [];
    }
}