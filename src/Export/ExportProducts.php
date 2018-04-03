<?php

namespace BitrixMigration\Export;

use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\CLI;

class ExportProducts {
    use BitrixMigrationHelper;
    /**
     * Id инфолока переноса
     * @var
     */
    public $iblock_id;
    public $IblockProperties;
    public $CatalogPrices;
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
     * ImportProducts constructor.
     *
     * @param $iblock_id
     */
    public function __construct($iblock_id)
    {
        $this->iblock_id = $iblock_id;
        $this->IblockProperties = $this->getIblockProperties();
        $this->CatalogPrices = ExportPrices::init($this->iblock_id);
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
        $list = \CIBlockSection::GetList([], ['IBLOCK_ID' => $this->iblock_id, 'SECTION_ID' => $section_id],null,['UF_*']);

        $sections = $this->FetchAll($list, function ($section) {
            $this->dumpFiles($section);
            $subsections = $this->getSection($section['ID']);

            if (count($subsections))
                $section['SUBSECTIONS'] = $subsections;

            return $section;

        });

        return count($sections) ? $sections : false;
    }

    /**
     * Массив всех элементов инфоблока
     *
     * @param $callback - функция, исполняемая каждые $chunks элементов
     * @param $chunks - количество элементов за один шаг
     */
    public function getAllProducts($callback, $chunks)
    {
        $items = [];
        $list = \CIblockElement::getList([], ['IBLOCK_ID' => $this->iblock_id]);
        $count = $list->SelectedRowsCount();
        $i = 0;
        $page = 1;

        while ($item = $list->GetNextElement()) {
            $i++;


            CLI::show_status($page * $chunks + $i, $count);

            if(!$this->NextStep($callback, $chunks, $i, $items, $page))
                return;

            $fields = $this->arrayOnly($item->getFields(), $this->fields);
            $props = $item->getProperties();

            $this->dumpPropertyFiles($props);

            $fields['PROPS'] = $props;
            $fields['PRICES'] = $this->CatalogPrices->getPrices($fields['ID']);
            $fields['OFFERS'] = $this->getOffers($fields['ID']);


            $this->dumpFiles($fields);

            $items[] = $fields;
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
            if($callback($items, $page, $this->files) === false)
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


}