<?php


namespace BitrixMigration\Import;


trait SectionImportHelper {

    /**
     * @param $section
     *
     * @return mixed
     */
    protected function sectionExists($section)
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
    protected function replaceFields($section, $replaces = null)
    {
        $section = array_replace_recursive($section, $this->replacedFields);
        if ($replaces) {
            $section = array_replace_recursive($section, $replaces);
        }

        return $section;
    }
}