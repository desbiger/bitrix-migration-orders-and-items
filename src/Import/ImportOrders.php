<?php

namespace BitrixMigration\Import;


use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\Export\ExportProducts;
use BitrixMigration\Import\Contracts\Importer;
use BitrixMigration\JsonReader;

class ImportOrders implements Importer {
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
    public function __construct()
    {
        \CModule::IncludeModule('catalog');
        \CModule::IncludeModule('sale');
    }

    /**
     * @param $orders
     * @param $users
     */
    public function import()
    {
        foreach ($this->orders as $user => $user_orders) {
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


    public function execute()
    {
        $this->before();
        $this->import();
        $this->after();
    }

    /**
     * @return string
     */
    public function getImportName()
    {
        return "Import Orders";
    }

    public function before()
    {

        $container = Container::instance();

        $this->orders = $this->read('orders');
        $this->catalog_iblock_id = $container->getNewIblock();
        $this->importProducts = $container->getProductsImportResult();
        $import_path = $container->getImportPath();

        $persons = $this->read('PersonType');
        $importPersonType = ImportPersonType::init($persons)->import();

        $paySystem = $this->read('paySystem');

        $ImportPaySystem = ImportPaySystem::init($paySystem, $import_path, $importPersonType->NewIDS)->import();

        $users = $this->read('users');
        $this->newUserIDS = ImportUsers::init($users)->import()->newIDs;


        $this->newDeliveryIDs = ImportDelivery::init()->import()->newIDs;
        dd();

        $this->newPersonsTypeIDS = $importPersonType->NewIDS;
        $this->newOrderPropsIDS = $importPersonType->newOrderPropsIDS;
        $this->newPaySystemIDS = $ImportPaySystem->newPaySystemIDS;

    }

    public function after()
    {
        $this->orders = [];
    }
}