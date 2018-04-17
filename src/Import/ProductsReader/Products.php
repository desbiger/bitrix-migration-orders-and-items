<?php

namespace BitrixMigration\Import\ProductsReader;


use BitrixMigration\Import\Container;
use BitrixMigration\Import\MigrationFilesUploadHelper;

class Products extends FilesReader {

    use MigrationFilesUploadHelper;

    protected $PropertyLinkedItems;
    public $folder = '/products/';

    protected $containerIDsFieldName = 'newProductsIDs';

    public function updateElement($nextElement)
    {
        $Element = collect($nextElement)->except(['PRICES', 'OFFERS'])->toArray();

        $Element = $this->correctFieldsValues($Element);

        return $Element;
    }

    /**
     * @param $Element
     *
     * @return array
     */
    public function correctFieldsValues($Element)
    {
        $container = Container::instance();
        $replace = [
            'IBLOCK_ID'         => $container->newIblock,
            'IBLOCK_SECTION_ID' => $container->getSectionImportResult()[$Element['IBLOCK_SECTION_ID']],
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
                if ($res = $this->PrepareByPropertyType($item['PROPERTY_TYPE'], $item))
                    return $res;

                return $item['VALUE'];
            }
        }, $Element['PROPS']);

        unset($Element['PROPS']);

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

    /**
     * @param $item
     *
     * @return mixed
     */
    public function getListPropertyIDByValue($item)
    {
        return \CIBlockProperty::GetPropertyEnum($this->getNewPropertyID($item['ID']), [], ['XML_ID' => $item['VALUE_XML_ID'][0]])->Fetch();
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
}