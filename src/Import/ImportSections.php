<?php


namespace BitrixMigration\Import;


class ImportSections {
    public $xml_id;
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
    public function __construct($sections, $iblock_id)
    {
        $this->iblock_id = $iblock_id;
        $this->sections = $this->updateSections($sections);
        $this->getXmlID($this->sections);
    }

    /**
     * @return $this
     */
    public function import()
    {
        foreach ($this->sections as $section) {
            $id = $this->createSection($section);
            if (count($section['SUBSECTIONS'])) {
                $this->createSubsections($id, $section['SUBSECTIONS']);
            }
        }

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
    private function createSection($section)
    {
        $CIBlockSection = new \CIBlockSection;
        if (!$id = $CIBlockSection->Add($section)) {
            echo($CIBlockSection->LAST_ERROR);
        }

        return $id;
    }

    /**
     * @param $parent_id
     * @param $subsections
     */
    private function createSubsections($parent_id, $subsections)
    {
        foreach ($subsections as $section) {
            $section['IBLOCK_SECTION_ID'] = $parent_id;
            $id = $this->createSection($section);
            if ($section['SUBSECTIONS']) {
                $this->createSubsections($id, $section['SUBSECTIONS']);
            }
        }
    }

    /**
     * @param $sections
     *
     * @return mixed
     */
    private function updateSections($sections)
    {
        $UpdatedSections = [];
        foreach ($sections as &$section) {
            $section['IBLOCK_ID'] = $this->iblock_id;
            $section['IBLOCK_SECTION_ID'] = null;
            if (count($section['SUBSECTIONS'])) {
                $section['SUBSECTIONS'] = $this->updateSections($section['SUBSECTIONS']);
            }
            $UpdatedSections[] = $section;
        }

        return $UpdatedSections;
    }

    /**
     * @param $sections
     */
    private function getXmlID($sections)
    {
        foreach ($sections as $section) {
            $this->xml_id[$section['ID']] = $section['XML_ID'];
            if (count($section['SUBSECTIONS'])) {
                $this->getXmlID($section['SUBSECTIONS']);
            }
        }
    }
}