<?php

namespace BitrixMigration\Import;


use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\Export\ExportProducts;
use BitrixMigration\JsonReader;

class ImportOrders {
    public $orders;
    public $importProducts;
    use BitrixMigrationHelper, JsonReader;

    private $catalog_iblock_id;


    /**
     * ImportOrders constructor.
     *
     * @param $catalog_iblock_id
     */
    public function __construct($catalog_iblock_id)
    {
        \CModule::IncludeModule('catalog');
        \CModule::IncludeModule('sale');
        $this->catalog_iblock_id = $catalog_iblock_id;
        $this->importProducts = new ImportProducts($catalog_iblock_id);
    }

    public function import($orders)
    {
        foreach ($orders as $user => $user_orders) {
            if (count($user_orders)) {
                $this->createOrders($user_orders);
            }
        }
    }

    /**
     * @param $user_orders
     */
    private function createOrders($user_orders)
    {
        foreach ($user_orders as $order) {
            $this->updateOrderData($order);
            $this->createOrder($order);
        }
    }

    /**
     * @param $order
     */
    private function updateOrderData(&$order)
    {

        foreach ($order['PRODUCTS'] as &$product) {
            if (count($product)) {
                $product['PRODUCT_ID'] = (new ExportProducts($this->catalog_iblock_id))->getProductIdByXMLID($product['PRODUCT_XML_ID']);
                $product['PRODUCT_PRICE_ID'] = $this->importProducts->getProductPriceID($product['PRODUCT_XML_ID'], $product['PRICE']);
            }
        }
    }

    /**
     * @param $order
     */
    private function createOrder($order)
    {
        global $APPLICATION;
        $orderObject = new \CSaleOrder();
        $id = $orderObject->Add($order);
        if ($ex = $APPLICATION->GetException())
            echo $ex->GetString() . ' ' . $id;


    }


}