<?php

namespace Sprint\Migration\Helpers;

use Sprint\Migration\Helper;

class IblockHelper extends Helper {

    public function getIblockType($typeId)
    {
        /** @compatibility filter or $typeId */
        $filter = is_array($typeId) ? $typeId : [
            '=ID' => $typeId
        ];

        $filter['CHECK_PERMISSIONS'] = 'N';
        $aItem = \CIBlockType::GetList(['SORT' => 'ASC'], $filter)->Fetch();

        if ($aItem) {
            $aItem['LANG'] = $this->getIblockTypeLangs($aItem['ID']);
        }

        return $aItem;
    }

    public function getIblockTypeId($typeId)
    {
        $aIblock = $this->getIblockType($typeId);

        return ($aIblock && isset($aIblock['ID'])) ? $aIblock['ID'] : 0;
    }

    public function getIblockTypes($filter = [])
    {
        $filter['CHECK_PERMISSIONS'] = 'N';
        $dbResult = \CIBlockType::GetList(['SORT' => 'ASC'], $filter);

        $list = [];
        while ($aItem = $dbResult->Fetch()) {
            $aItem['LANG'] = $this->getIblockTypeLangs($aItem['ID']);
            $list[] = $aItem;
        }

        return $list;
    }

    public function addIblockTypeIfNotExists($fields = [])
    {
        $this->checkRequiredKeys(__METHOD__, $fields, ['ID']);

        $aIblockType = $this->getIblockType($fields['ID']);
        if ($aIblockType) {
            return $aIblockType['ID'];
        }

        return $this->addIblockType($fields);
    }

    public function addIblockType($fields = [])
    {
        $default = [
            'ID'       => '',
            'SECTIONS' => 'Y',
            'IN_RSS'   => 'N',
            'SORT'     => 100,
            'LANG'     => [
                'ru' => [
                    'NAME'         => 'Catalog',
                    'SECTION_NAME' => 'Sections',
                    'ELEMENT_NAME' => 'Elements'
                ],
                'en' => [
                    'NAME'         => 'Catalog',
                    'SECTION_NAME' => 'Sections',
                    'ELEMENT_NAME' => 'Elements'
                ],
            ]
        ];

        $fields = array_replace_recursive($default, $fields);

        $ib = new \CIBlockType;
        if ($ib->Add($fields)) {
            return $fields['ID'];
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);
    }


    public function deleteIblockTypeIfExists($typeId)
    {
        $aIblockType = $this->getIblockType($typeId);
        if (!$aIblockType) {
            return false;
        }

        return $this->deleteIblockType($aIblockType['ID']);

    }

    public function deleteIblockType($typeId)
    {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        if (\CIBlockType::Delete($typeId)) {
            return true;
        }

        $this->throwException(__METHOD__, 'Could not delete iblock type %s', $typeId);
    }

    public function getIblock($code, $typeId = '')
    {
        /** @compatibility filter or code */
        $filter = is_array($code) ? $code : [
            '=CODE' => $code
        ];

        if (!empty($typeId)) {
            $filter['=TYPE'] = $typeId;
        }

        $filter['CHECK_PERMISSIONS'] = 'N';

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        return \CIBlock::GetList(['SORT' => 'ASC'], $filter)->Fetch();
    }

    public function getIblockId($code, $typeId = '')
    {
        $aIblock = $this->getIblock($code, $typeId);

        return ($aIblock && isset($aIblock['ID'])) ? $aIblock['ID'] : 0;
    }

    public function getIblocks($filter = [])
    {
        $filter['CHECK_PERMISSIONS'] = 'N';

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $dbResult = \CIBlock::GetList(['SORT' => 'ASC'], $filter);
        $list = [];
        while ($aItem = $dbResult->Fetch()) {
            $list[] = $aItem;
        }

        return $list;
    }

    public function addIblockIfNotExists($fields = [], $catalogSettings = [])
    {
        $this->checkRequiredKeys(__METHOD__, $fields, ['CODE']);

        $typeId = false;
        if (!empty($fields['IBLOCK_TYPE_ID'])) {
            $typeId = $fields['IBLOCK_TYPE_ID'];
        }

        $aIblock = $this->getIblock($fields['CODE'], $typeId);

        if ($aIblock) {

            return $aIblock['ID'];
        }

        return $this->addIblock($fields, $catalogSettings);
    }

    public function addIblock($fields, $catalogSettings)
    {

        $default = [
            'ACTIVE'           => 'Y',
            'NAME'             => '',
            'CODE'             => '',
            'LIST_PAGE_URL'    => '',
            'DETAIL_PAGE_URL'  => '',
            'SECTION_PAGE_URL' => '',
            'IBLOCK_TYPE_ID'   => 'main',
            'SITE_ID'          => ['s1'],
            'SORT'             => 500,
            'GROUP_ID'         => ['2' => 'R'],
            'VERSION'          => 2,
            'BIZPROC'          => 'N',
            'WORKFLOW'         => 'N',
            'INDEX_ELEMENT'    => 'N',
            'INDEX_SECTION'    => 'N'
        ];

        $fields = array_replace_recursive($default, $fields);

        $ib = new \CIBlock;
        $iblockId = $ib->Add($fields);

        $this->makeIblockCatalogIfNotCatalog($iblockId, $catalogSettings);

        if ($iblockId) {
            return $iblockId;
        }
        $this->throwException(__METHOD__, $ib->LAST_ERROR);
    }

    public function deleteIblockIfExists($code, $typeId = '')
    {
        $aIblock = $this->getIblock($code, $typeId);
        if (!$aIblock) {
            return false;
        }

        return $this->deleteIblock($aIblock['ID']);
    }

    public function deleteIblock($iblockId)
    {
        if (\CIBlock::Delete($iblockId)) {
            return true;
        }
        $this->throwException(__METHOD__, 'Could not delete iblock %s', $iblockId);
    }

    public function getIblockFields($iblockId)
    {
        return \CIBlock::GetFields($iblockId);
    }

    public function updateIblockFields($iblockId, $fields = [])
    {
        $default = \CIBlock::GetFields($iblockId);
        $fields = array_replace_recursive($default, $fields);
        \CIBlock::SetFields($iblockId, $fields);

        return true;
    }

    public function getProperty($iblockId, $code)
    {
        /** @compatibility filter or code */
        $filter = is_array($code) ? $code : [
            'CODE' => $code
        ];

        $filter['IBLOCK_ID'] = $iblockId;
        $filter['CHECK_PERMISSIONS'] = 'N';

        /* do not use =CODE in filter */

        return \CIBlockProperty::GetList(['SORT' => 'ASC'], $filter)->Fetch();
    }

    public function getPropertyId($iblockId, $code)
    {
        $aItem = $this->getProperty($iblockId, $code);

        return ($aItem && isset($aItem['ID'])) ? $aItem['ID'] : 0;
    }

    public function getProperties($iblockId, $filter = [])
    {
        $filter['IBLOCK_ID'] = $iblockId;
        $filter['CHECK_PERMISSIONS'] = 'N';

        $dbResult = \CIBlockProperty::GetList(['SORT' => 'ASC'], $filter);

        $list = [];
        while ($aItem = $dbResult->Fetch()) {
            $list[] = $aItem;
        }

        return $list;
    }

    public function addPropertyIfNotExists($iblockId, $fields)
    {
        $this->checkRequiredKeys(__METHOD__, $fields, ['CODE']);

        $aProperty = $this->getProperty($iblockId, $fields['CODE']);
        if ($aProperty) {
            return $aProperty['ID'];
        }

        return $this->addProperty($iblockId, $fields);

    }

    public function addProperty($iblockId, $fields)
    {

        $default = [
            'NAME'           => '',
            'ACTIVE'         => 'Y',
            'SORT'           => '500',
            'CODE'           => '',
            'PROPERTY_TYPE'  => 'S',
            'USER_TYPE'      => '',
            'ROW_COUNT'      => '1',
            'COL_COUNT'      => '30',
            'LIST_TYPE'      => 'L',
            'MULTIPLE'       => 'N',
            'IS_REQUIRED'    => 'N',
            'FILTRABLE'      => 'Y',
            'LINK_IBLOCK_ID' => 0
        ];

        if (!empty($fields['VALUES'])) {
            $default['PROPERTY_TYPE'] = 'L';
        }

        if (!empty($fields['LINK_IBLOCK_ID'])) {
            $default['PROPERTY_TYPE'] = 'E';
        }

        $fields = array_replace_recursive($default, $fields);

        if (false !== strpos($fields['PROPERTY_TYPE'], ':')) {
            list($ptype, $utype) = explode(':', $fields['PROPERTY_TYPE']);
            $fields['PROPERTY_TYPE'] = $ptype;
            $fields['USER_TYPE'] = $utype;
        }

        $fields['IBLOCK_ID'] = $iblockId;

        $ib = new \CIBlockProperty;
        $propertyId = $ib->Add($fields);

        if ($propertyId) {
            return $propertyId;
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);
    }


    public function deletePropertyIfExists($iblockId, $code)
    {
        $aProperty = $this->getProperty($iblockId, $code);
        if (!$aProperty) {
            return false;
        }

        return $this->deletePropertyById($aProperty['ID']);

    }

    public function deletePropertyById($propertyId)
    {
        $ib = new \CIBlockProperty;
        if ($ib->Delete($propertyId)) {
            return true;
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);
    }


    public function updatePropertyIfExists($iblockId, $code, $fields)
    {
        $aProperty = $this->getProperty($iblockId, $code);
        if (!$aProperty) {
            return false;
        }

        return $this->updatePropertyById($aProperty['ID'], $fields);
    }

    public function updatePropertyById($propertyId, $fields)
    {
        if (!empty($fields['VALUES']) && !isset($fields['PROPERTY_TYPE'])) {
            $fields['PROPERTY_TYPE'] = 'L';
        }

        if (!empty($fields['LINK_IBLOCK_ID']) && !isset($fields['PROPERTY_TYPE'])) {
            $fields['PROPERTY_TYPE'] = 'E';
        }

        if (false !== strpos($fields['PROPERTY_TYPE'], ':')) {
            list($ptype, $utype) = explode(':', $fields['PROPERTY_TYPE']);
            $fields['PROPERTY_TYPE'] = $ptype;
            $fields['USER_TYPE'] = $utype;
        }

        $ib = new \CIBlockProperty();
        if ($ib->Update($propertyId, $fields)) {
            return true;
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);
    }

    public function getElement($iblockId, $code)
    {
        /** @compatibility filter or code */
        $filter = is_array($code) ? $code : [
            '=CODE' => $code
        ];

        $filter['IBLOCK_ID'] = $iblockId;
        $filter['CHECK_PERMISSIONS'] = 'N';

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        return \CIBlockElement::GetList([
            'SORT' => 'ASC'
        ], $filter, false, [
            'nTopCount' => 1
        ], [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'CODE',
        ])->Fetch();
    }

    public function getElementId($iblockId, $code)
    {
        $aItem = $this->getElement($iblockId, $code);

        return ($aItem && isset($aItem['ID'])) ? $aItem['ID'] : 0;
    }

    public function getElements($iblockId, $filter = [], $select = [])
    {
        $filter['IBLOCK_ID'] = $iblockId;
        $filter['CHECK_PERMISSIONS'] = 'N';

        $select = array_merge([
            'ID',
            'IBLOCK_ID',
            'NAME',
            'CODE',
        ], $select);

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $dbResult = \CIBlockElement::GetList([
            'ID' => 'ASC'
        ], $filter, false, false, $select);

        $list = [];
        while ($aItem = $dbResult->Fetch()) {
            $list[] = $aItem;
        }

        return $list;
    }

    public function addElementIfNotExists($iblockId, $fields, $props = [])
    {
        $this->checkRequiredKeys(__METHOD__, $fields, ['CODE']);

        $aItem = $this->getElement($iblockId, $fields['CODE']);
        if ($aItem) {
            return $aItem['ID'];
        }

        return $this->addElement($iblockId, $fields, $props);
    }

    public function addElement($iblockId, $fields = [], $props = [])
    {
        $default = [
            "NAME"              => "element",
            "IBLOCK_SECTION_ID" => false,
            "ACTIVE"            => "Y",
            "PREVIEW_TEXT"      => "",
            "DETAIL_TEXT"       => "",
        ];

        $fields = array_replace_recursive($default, $fields);
        $fields["IBLOCK_ID"] = $iblockId;

        if (!empty($props)) {
            $fields['PROPERTY_VALUES'] = $props;
        }

        $ib = new \CIBlockElement;
        $id = $ib->Add($fields);

        if ($id) {
            return $id;
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);
    }

    public function updateElementIfExists($iblockId, $fields = [], $props = [])
    {
        $this->checkRequiredKeys(__METHOD__, $fields, ['CODE']);

        $aItem = $this->getElement($iblockId, $fields['CODE']);
        if (!$aItem) {
            return false;
        }

        $fields['IBLOCK_ID'] = $iblockId;
        unset($fields['CODE']);

        return $this->updateElement($aItem['ID'], $fields, $props);
    }


    public function updateElement($elementId, $fields = [], $props = [])
    {
        $iblockId = !empty($fields['IBLOCK_ID']) ? $fields['IBLOCK_ID'] : false;
        unset($fields['IBLOCK_ID']);

        if (!empty($fields)) {
            $ib = new \CIBlockElement;
            if (!$ib->Update($elementId, $fields)) {
                $this->throwException(__METHOD__, $ib->LAST_ERROR);
            }
        }

        if (!empty($props)) {
            \CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, $props);
        }

        return true;
    }

    public function deleteElementIfExists($iblockId, $code)
    {
        $aItem = $this->getElement($iblockId, $code);

        if (!$aItem) {
            return false;
        }

        return $this->deleteElement($aItem['ID']);
    }

    public function deleteElement($elementId)
    {
        $ib = new \CIBlockElement;
        if ($ib->Delete($elementId)) {
            return true;
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);
    }

    public function getSection($iblockId, $code)
    {
        /** @compatibility filter or code */
        $filter = is_array($code) ? $code : [
            '=CODE' => $code
        ];

        $filter['IBLOCK_ID'] = $iblockId;
        $filter['CHECK_PERMISSIONS'] = 'N';

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        return \CIBlockSection::GetList([
            'ID' => 'ASC'
        ], $filter, false, [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'CODE',
        ])->Fetch();
    }

    public function getSectionId($iblockId, $code)
    {
        $aItem = $this->getSection($iblockId, $code);

        return ($aItem && isset($aItem['ID'])) ? $aItem['ID'] : 0;
    }

    public function getSections($iblockId, $filter = [])
    {
        $filter['IBLOCK_ID'] = $iblockId;
        $filter['CHECK_PERMISSIONS'] = 'N';

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $dbResult = \CIBlockSection::GetList([
            'SORT' => 'ASC'
        ], $filter, false, [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'CODE',
        ]);

        $list = [];
        while ($aItem = $dbResult->Fetch()) {
            $list[] = $aItem;
        }

        return $list;
    }

    public function addSectionIfNotExists($iblockId, $fields)
    {
        $this->checkRequiredKeys(__METHOD__, $fields, ['CODE']);

        $aItem = $this->getSection($iblockId, $fields['CODE']);
        if ($aItem) {
            return $aItem['ID'];
        }

        return $this->addSection($iblockId, $fields);

    }

    public function addSection($iblockId, $fields = [])
    {
        $default = [
            "ACTIVE"            => "Y",
            "IBLOCK_SECTION_ID" => false,
            "NAME"              => 'section',
            "CODE"              => '',
            "SORT"              => 100,
            "PICTURE"           => false,
            "DESCRIPTION"       => '',
            "DESCRIPTION_TYPE"  => 'text'
        ];

        $fields = array_replace_recursive($default, $fields);
        $fields["IBLOCK_ID"] = $iblockId;

        $ib = new \CIBlockSection;
        $id = $ib->Add($fields);

        if ($id) {
            return $id;
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);
    }

    public function updateSectionIfExists($iblockId, $fields)
    {
        $this->checkRequiredKeys(__METHOD__, $fields, ['CODE']);

        $aItem = $this->getSection($iblockId, $fields['CODE']);
        if (!$aItem) {
            return false;
        }

        unset($fields['CODE']);

        return $this->updateSection($aItem['ID'], $fields);

    }

    public function updateSection($sectionId, $fields)
    {
        $ib = new \CIBlockSection;
        if ($ib->Update($sectionId, $fields)) {
            return true;
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);
    }

    public function deleteSectionIfExists($iblockId, $code)
    {
        $aItem = $this->getSection($iblockId, $code);
        if (!$aItem) {
            return false;
        }

        return $this->deleteSection($aItem['ID']);

    }

    public function deleteSection($sectionId)
    {
        $ib = new \CIBlockSection;
        if ($ib->Delete($sectionId)) {
            return true;
        }

        $this->throwException(__METHOD__, $ib->LAST_ERROR);
    }

    /* @deprecated */
    public function deleteProperty($iblockId, $code)
    {
        return $this->deletePropertyIfExists($iblockId, $code);
    }

    /* @deprecated */
    public function updateProperty($iblockId, $code, $fields)
    {
        return $this->updatePropertyIfExists($iblockId, $code, $fields);
    }

    /** @deprecated */
    public function mergeIblockFields($iblockId, $fields)
    {
        self::updateIblockFields($iblockId, $fields);
    }

    public function getIblockTypeLangs($typeId)
    {
        $result = [];
        $dbRes = \CLanguage::GetList($lby = "sort", $lorder = "asc");
        while ($aItem = $dbRes->GetNext()) {
            $values = \CIBlockType::GetByIDLang($typeId, $aItem['LID'], false);
            if (!empty($values)) {
                $result[$aItem['LID']] = [
                    'NAME'         => $values['NAME'],
                    'SECTION_NAME' => $values['SECTION_NAME'],
                    'ELEMENT_NAME' => $values['ELEMENT_NAME']
                ];
            }
        }

        return $result;
    }

    /**
     * Делает инфоблок торговым каталогом
     *
     * @param $iblock_id
     *
     * @return mixed
     */
    private function makeIblockCatalogIfNotCatalog($iblock_id, $catalogSettings)
    {
        if ($catalogSettings) {

            \CModule::IncludeModule('catalog');
            $default = [
                'IBLOCK_ID'         => $iblock_id,
            ];


            $fields = array_replace_recursive($catalogSettings,$default);

            $fields = collect($fields)->only([
                'IBLOCK_ID',
                'SUBSCRIPTION',
                'YANDEX_EXPORT',
                'VAT_ID',
                'PRODUCT_IBLOCK_ID',
                'SKU_PROPERTY_ID'
            ])->toArray();

            return \CCatalog::Add($fields);
        }
    }
}