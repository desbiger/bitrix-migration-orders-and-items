<?php

namespace BitrixMigration;

class ImportProducts {

    public $iblock_id;

    public function __construct($iblock_id)
    {
        $this->iblock_id = $iblock_id;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $products = $this->getAllProducts();
//        $sections = $this->getSection();


        return $products;
    }

    /**
     * @param mixed $iblock_id
     */
    public function setIblockId($iblock_id)
    {
        $this->iblock_id = $iblock_id;
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
        $sections = [];
        $list = \CIBlockSection::GetList([], ['IBLOCK_ID' => $this->iblock_id, 'SECTION_ID' => $section_id]);

        while ($section = $list->Fetch()) {
            $subsections = $this->getSection($section['ID']);

            if (count($subsections))
                $section['SUBSECTIONS'] = $subsections;

            $sections[] = $section;
        }

        return count($sections) ? $sections : false;
    }

    /**
     * @return array
     */
    public function getAllProducts()
    {
        $items = [];
        $list = \CIblockElement::getList([], ['IBLOCK_ID' => $this->iblock_id]);
        while ($item = $list->GetNextElement()) {
            $fields = $item->getFields();
            $props = $item->getProperties();
            $fields['PROPS'] = $props;

            if ($fields['IBLOCK_SECTION_ID']) {

                $items['SECTIONS'][$fields['IBLOCK_SECTION_ID']][] = $fields;
                continue;
            }
            $items['ROOT'][] = $fields;
        }

        return $items;
    }

}