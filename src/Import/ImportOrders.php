<?php

namespace BitrixMigration\Import;


use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\Export\ExportProducts;
use BitrixMigration\JsonReader;

class ImportOrders {
    public $orders;
    public $importProducts;
    public $newUserIDS;
    public $newPersonsTypeIDS;
    public $newOrderPropsIDS;
    public $newPaySystemIDS;
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

    /**
     * @param $orders
     * @param $users
     */
    public function import($orders, $users, $persons, $paySystem, $delivery)
    {
        $importPersonType = ImportPersonType::init($persons)->import();
        $ImportPaySystem = ImportPaySystem::init($paySystem, $this->import_path, $importPersonType->NewIDS)->import();

        $this->newUserIDS = ImportUsers::init($users)->import()->ids;
        $this->newPersonsTypeIDS = $importPersonType->NewIDS;
        $this->newOrderPropsIDS = $importPersonType->newOrderPropsIDS;
        $this->newPaySystemIDS = $ImportPaySystem->newPaySystemIDS;


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
        $order['USER_ID'] = $this->newUserIDS[$order['USER_ID']];
        $order['EMP_ALLOW_DELIVERY_ID'] = $this->newUserIDS[$order['EMP_ALLOW_DELIVERY_ID']];
        $order['EMP_PAYED_ID'] = $this->newUserIDS[$order['EMP_PAYED_ID']];
        $order['PAY_SYSTEM_ID'] = $this->newPaySystemIDS[$order['PAY_SYSTEM_ID']];
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
        unset($order['ID']);
        $id = $orderObject->Add($order);
        $this->createOrderBasket($order['PRODUCTS'], $id);
        if ($ex = $APPLICATION->GetException())
            echo $ex->GetString() . ' ' . $id;


    }

    /**
     * @param $PRODUCTS
     * @param $order_id
     */
    private function createOrderBasket($PRODUCTS, $order_id)
    {
        foreach ($PRODUCTS as $product) {
            $product['ORDER_ID'] = $order_id;
            \CSaleBasket::Add($product);
        }
    }


}