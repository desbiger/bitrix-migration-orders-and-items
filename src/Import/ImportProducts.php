<?php

namespace BitrixMigration\Import;

use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\CLI;
use BitrixMigration\Export\ExportProducts;
use BitrixMigration\JsonReader;

class ImportProducts {

    use BitrixMigrationHelper, JsonReader;
    public $iblockElement;
    public $exportProducts;
    public $readedChunks;
    public $sectionImportResult;
    public $OldFilesArray;
    /**
     * @var ImportIblock
     */
    private $newIblock;
    private $import_path;

    public function __construct(ImportIblock $newIblock, $import_path)
    {
        $this->import_path = $import_path;
        $this->newIblock = $newIblock;
        $this->OldFilesArray = $this->read('/files/allFiles');


        $this->iblockElement = new \CIBlockElement();


        $this->sectionImportResult = (new ImportSections($import_path, $this->newIblock->newIblockID))->import()->newSectionIDS;


        $this->importCatalog();
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
        while ($file = $this->getNextChunk()) {
            $this->createElements($file);
        }
    }

    /**
     * Получаем слудующий файл
     * @return bool|mixed
     */
    private function getNextChunk()
    {
        $excluded = ['.', '..'];
        $path = $this->import_path . '/products/';
        $files = scandir($path);
        $files = array_diff($files, $excluded);
        foreach ($files as $file) {
            if (!$this->isReaded($file)) {
                $this->readedChunks[] = $file;

                return $this->read('/products/' . str_replace(".json", '', $file));
            }

        }

        return false;
    }

    /**
     * Проверка открывался ли уже переданный файл
     *
     * @param $file
     *
     * @return bool
     */
    private function isReaded($file)
    {
        return in_array($file, $this->readedChunks);
    }

    /**
     * @param $file
     */
    private function createElements($file)
    {
        foreach ($file as $i => $Element) {
            CLI::show_status($i+1, count($file));
            $this->newIds[$Element['ID']] = $this->createElementIfNotExist($Element);
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
        $prices = $Element['PRICES'];
        unset($Element['PRICES']);

        $offers = $Element['OFFERS'];
        unset($Element['OFFERS']);
        unset($Element['ID']);

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
            'IBLOCK_ID'         => $this->newIblock->newIblockID,
            'IBLOCK_SECTION_ID' => $this->sectionImportResult[$Element['IBLOCK_SECTION_ID']],
            'PREVIEW_PICTURE'   => $this->getFileArray($Element['PREVIEW_PICTURE']),
            'DETAIL_PICTURE'    => $this->getFileArray($Element['DETAIL_PICTURE'])
        ];

        $Element = array_replace_recursive($Element, $replace);
        $Element = $this->convertProperties($Element);

        return $Element;
    }

    /**
     * @param $Element
     */
    private function convertProperties($Element)
    {
        $Element['PROPERTY_VALUES'] = array_map(function ($item) {
            if ($item['VALUE']) {
                if ($item['PROPERTY_TYPE'] == 'F') {
                    if (is_array($item['VALUE'])) {
                        $res = [];
                        foreach ($item['VALUE'] as $picture) {
                            $res[] = $this->getFileArray($picture);
                        }

                        return $res;
                    }


                    return $this->getFileArray($item['VALUE']);
                }

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
            $path = $this->import_path . '/files' . $this->OldFilesArray[$oldID];

            return \CFile::MakeFileArray($path);
        }

        return null;
    }


}