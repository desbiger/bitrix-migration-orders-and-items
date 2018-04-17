<?php

namespace BitrixMigration\Import;

use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\CLI;
use BitrixMigration\Import\Contracts\Importer;
use BitrixMigration\Import\ProductsReader\Products;
use BitrixMigration\JsonReader;

class ImportProducts implements Importer {

    use BitrixMigrationHelper, JsonReader;
    public $iblockElement;
    public $exportProducts;
    public $readedChunks;
    public $OldFilesArray;
    public $newIblockID;
    public $allFilesArray = [];
    /**
     * @var ImportIblock
     */
    private $newIblock;
    private $import_path;

    public $newIds;
    protected $PropertyLinkedItems;

    public function __construct()
    {
        $this->iblockElement = new \CIBlockElement();
    }

    /**
     * @param $productXMLID
     * @param $price
     *
     * @return mixed
     */
    public function getProductPriceID($productXMLID, $price)
    {
        $t = $this->iblockElement->GetList([], ['XML_ID' => $productXMLID]);
        $product = $t->Fetch();
        $prices = $this->exportProducts->CatalogPrices->getPrices($product['ID'], ['PRICE' => $price]);

        return count($prices) ? $price[0]['ID'] : null;
    }

    /**
     *
     */
    private function importCatalog()
    {

        $reader = new Products();

        $list = Container::instance()->newProductsIDs;

        $reader->setLoadedIDS(array_keys($list));

        echo "\n";
        while (list($element, $count, $counter, $file) = $reader->getNextElement()) {

            CLI::show_status($counter, $count, 30, ' | file: ' . $file);
            $newID = $this->createElementIfNotExist($element);
            Container::instance()->addNewProductID($element['ID'], $newID);
        }

    }


    /**
     * @param $Element
     *
     * @return mixed
     */
    private function createElementIfNotExist($Element)
    {
        $element = new \CIBlockElement();
        if ($id = $this->exists($Element)) {
            return $id;
        }

        $Element = collect($Element)->except(['PRICES', 'OFFERS'])->toArray();

        $Element = $this->correctFieldsValues($Element);

        $id = $element->add($Element);
        if (!$id) {
            echo $element->LAST_ERROR;
        }

        return $id;

    }

    /**
     * @param $Element
     *
     * @return mixed
     */
    private function exists($Element)
    {
        return \CIBlockElement::GetList([], [
            'XML_ID' => $Element['XML_ID'],
            'NAME'   => $Element['NAME']
        ])->Fetch()['ID'];
    }

    /**
     * @param $Element
     *
     * @return array
     */
    private function correctFieldsValues($Element)
    {

        $replace = [
            'IBLOCK_ID'         => $this->newIblockID,
            'IBLOCK_SECTION_ID' => Container::instance()->getSectionImportResult()[$Element['IBLOCK_SECTION_ID']],
            'PREVIEW_PICTURE'   => $this->getFileArray($Element['PREVIEW_PICTURE']),
            'DETAIL_PICTURE'    => $this->getFileArray($Element['DETAIL_PICTURE'])
        ];

        $Element = array_replace_recursive($Element, $replace);
        $Element = $this->convertProperties($Element);

        return $Element;
    }

    /**
     * @param $type
     * @param $item
     *
     * @return array|null
     */
    public function PrepareByPropertyType($type, $item)
    {
        switch ($type) {
            case "F":
                return $this->FileProperty($item);

                break;
            case "L":
                return $this->ListProperty($item);

                break;

            case "E":
                $this->PropertyLinkedItems[] = $item['VALUE'];

                return null;
                break;
        }
    }


    /**
     * @param $Element
     */
    private function convertProperties($Element)
    {
        $Element['PROPERTY_VALUES'] = array_map(function ($item) {
            if ($item['VALUE']) {
                if ($res = $this->PrepareByPropertyType($item['PROPERTY_TYPE'], $item))
                    return $res;

                return $item['VALUE'];
            }
        }, $Element['PROPS']);

        unset($Element['PROPS']);

        return $Element;
    }

    /**
     * @param $oldID
     *
     * @return null
     */
    private function getFileArray($oldID)
    {
        if ($oldID) {
            $path = $this->import_path . '/files' . $this->allFilesArray[$oldID];

            return \CFile::MakeFileArray($path);
        }

        return null;
    }

    /**
     * @param $oldID
     *
     * @return mixed
     */
    public function getNewPropertyID($oldID)
    {
        return $this->newIblock->newPropertyIDs[$oldID];
    }

    /**
     * @param $item
     *
     * @return mixed
     */
    public function getListPropertyIDByValue($item)
    {
        return \CIBlockProperty::GetPropertyEnum($this->getNewPropertyID($item['ID']), [], ['XML_ID' => $item['VALUE_XML_ID'][0]])
                               ->Fetch();
    }

    /**
     * @param $item
     *
     * @return array|null
     */
    public function FileProperty($item)
    {
        if (is_array($item['VALUE'])) {
            $res = [];
            foreach ($item['VALUE'] as $picture) {
                $res[] = $this->getFileArray($picture);
            }

            return $res;
        }

        return $this->getFileArray($item['VALUE']);
    }

    /**
     * @param $item
     *
     * @return mixed
     */
    public function ListProperty($item)
    {
        $propValue = $this->getListPropertyIDByValue($item);

        return $propValue['ID'];
    }

    public function before()
    {
        $container = Container::instance();
        $this->import_path = $container->getImportPath();
        $this->newIblock = $container->getNewIblock();
        $this->newIblockID = $this->newIblock->newIblockID;
        $this->loadFiles();
    }

    public function after()
    {
        $this->allFilesArray = [];
    }


    /**
     * Загрузка всех картинок
     */
    private function loadFiles()
    {
        $files = $this->scanDir($this->import_path . '/files');
        foreach ($files as $file) {
            $addArray = $this->read('/files/' . $file);
            if (count($addArray))
                $this->allFilesArray = $this->allFilesArray + $addArray;
        }
        $addArray = null;
    }


    public function execute()
    {
        $this->before();
        $this->importCatalog();
        $this->after();
    }

    /**
     * @return string
     */
    public function getImportName()
    {
        return 'Import IBlock Elements';
    }

    public function setSiteID($id)
    {
        // TODO: Implement setSiteID() method.
    }
}