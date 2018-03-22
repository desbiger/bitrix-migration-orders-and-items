<?php


namespace BitrixMigration\Export;


use BitrixMigration\BitrixMigrationHelper;

class ExportPrices {
    use BitrixMigrationHelper;
    public $prices;
    private $iblock_id;

    /**
     * Массив типов цен
     * @var array
     */
    public $priceTypes = ['BASE', 'Закупочная', 'своя продажная'];

    static function init($iblock_id)
    {
        if (\CModule::IncludeModule('catalog')) {
            return new self($iblock_id);
        };

        return false;
    }

    /**
     * ImportPrices constructor.
     *
     * @param $ID
     */
    public function __construct($iblock_id)
    {
        $this->iblock_id = $iblock_id;
        $this->getCatalogPrices($iblock_id);
    }

    /**
     * @param $elementId
     *
     * @return mixed
     */
    public function getPrices($elementId)
    {
        $itemPrices = \CPrice::GetList([], ['PRODUCT_ID' => $elementId]);

        return $this->FetchAll($itemPrices);

    }

    /**
     * @param $iblock_id
     */
    protected function getCatalogPrices($iblock_id)
    {
        $catalogPrices = \CIBlockPriceTools::GetCatalogPrices($iblock_id, $this->priceTypes);
        $this->prices = $catalogPrices;
    }

    /**
     * @param array $priceTypes
     */
    public function setPriceTypes($priceTypes)
    {
        $this->priceTypes = $priceTypes;
    }

}