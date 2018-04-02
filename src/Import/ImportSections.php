<?php


namespace BitrixMigration\Import;


use BitrixMigration\JsonReader;
use Sprint\Migration\HelperManager;

class ImportSections {
    use JsonReader;
    public $xml_id;
    public $newSectionIDS;
    public $replacedFields;
    public $SectionUserFields;
    /**
     * @var mixed
     */
    private $sections;
    private $iblock_id;

    /**
     * ImportSections constructor.
     *
     * @param mixed $sections
     */
    public function __construct($importPath, $iblock_id)
    {
        $this->iblock_id = $iblock_id;
        $this->sections = $this->read($importPath . '/sections/sections_1');
        $this->SectionUserFields = $this->read($importPath . '/sections_uf');

        $this->replacedFields = [
            'IBLOCK_ID' => $this->iblock_id,
        ];
    }

    /**
     * @return $this
     */
    public function import()
    {
        $this->ImportSectionsUserFields();
        $this->ImportSections();

        return $this;
    }

    /**
     * @param $ID
     *
     * @return mixed
     */
    public function GetNewIDByOldID($ID)
    {
        $xmlID = $this->xml_id[$ID];

        return \CIBlockSection::GetList([], ['XML_ID' => $xmlID])->Fetch()['ID'];
    }

    /**
     * @param $section
     *
     * @return mixed
     */
    private function createSectionIfNotExists($section)
    {
        $replaces = null;

        if ($section['IBLOCK_SECTION_ID']) {
            $replaces['IBLOCK_SECTION_ID'] = $this->newSectionIDS[$section['IBLOCK_SECTION_ID']];
        }

        $section = $this->replaceFields($section, $replaces);

        $CIBlockSection = new \CIBlockSection;

        if ($id = $this->sectionExists($section)) {
            $this->newSectionIDS[$section['ID']] = $id;

            return $id;
        }

        if (!$id = $CIBlockSection->Add($section)) {
            echo($CIBlockSection->LAST_ERROR);
        }
        $this->newSectionIDS[$section['ID']] = $id;

        return $id;
    }

    /**
     * @param $parent_id
     * @param $subsections
     */
    private function createSubsections($subsections)
    {
        foreach ($subsections as $section) {

            $this->createSectionIfNotExists($section);
            if ($section['SUBSECTIONS']) {
                $this->createSubsections($section['SUBSECTIONS']);
            }
        }
    }

    /**
     * @param $section
     *
     * @return mixed
     */
    private function sectionExists($section)
    {
        return \CIBlockSection::GetList([], [
            'IBLOCK_ID' => $this->iblock_id,
            'XML_ID'    => $section['XML_ID']
        ])->Fetch()['ID'];
    }

    /**
     * @param $section
     *
     * @return array
     */
    private function replaceFields($section, $replaces = null)
    {
        $section = array_replace_recursive($section, $this->replacedFields);
        if ($replaces) {
            $section = array_replace_recursive($section, $replaces);
        }

        return $section;
    }

    private function ImportSections()
    {
        foreach ($this->sections as $section) {
            $this->createSectionIfNotExists($section);
            if (count($section['SUBSECTIONS'])) {
                $this->createSubsections($section['SUBSECTIONS']);
            }
        }
    }

    /**
     *
     */
    private function ImportSectionsUserFields()
    {
        $helper = new HelperManager();
        foreach ($this->SectionUserFields as $uf) {
            $id = $uf['ENTITY_ID'] = "IBLOCK_{$this->iblock_id}_SECTION";
            $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($id, $uf['FIELD_NAME'], $uf);
        }
    }
}