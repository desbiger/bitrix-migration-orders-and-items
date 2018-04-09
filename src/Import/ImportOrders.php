<?php

namespace BitrixMigration\Import;


use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\CLI;
use BitrixMigration\Export\ExportProducts;
use BitrixMigration\Import\Contracts\Importer;
use BitrixMigration\Import\ProductsReader\Orders;
use BitrixMigration\JsonReader;

class ImportOrders implements Importer {
    public $orders;
    public $importProducts;
    public $newUserIDS;
    public $newOrderIDS;
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

        $importPath = Container::instance()->getImportPath();
        $reader = new Orders($importPath . '/orders', $importPath);

        while (list($element, $count, $counter, $file) = $reader->getNextElement()) {
            CLI::show_status($counter, $count, 30, ' | file: ' . $file);
            $this->newOrderIDS[$element['ID']] = $this->createOrders($element);
        }
        Container::instance()->newOrdersIDs = $this->newOrderIDS;
        Container::instance()->trySaveContainer();

    }

    /**
     * @param $user_orders
     */
    private function createOrders($user_orders)
    {
        $this->updateOrderData($user_orders);
        $this->createOrder($user_orders);
    }

    /**
     * @param $order
     */
    private function updateOrderData(&$order)
    {
        $container = Container::instance();
        $order['FUSER_ID'] = $container->usersImportResult[$order['FUSER_ID']];
        $order['LID'] = 's1';
        unset($order['STATUS_ID']);
        $order['PAY_SYSTEM_ID'] = $container->newPaySystemIDS[$order['PAY_SYSTEM_ID']];

        foreach ($order['PRODUCTS'] as &$product) {
            if (count($product)) {

                $newProductID = $container->newProductsIDs[$product['PRODUCT_ID']];
                if ($newProductID) {
                    $product['PRODUCT_ID'] = $newProductID;
                    $product['PRODUCT_PRICE_ID'] = $container->newPriceIDs[$product['PRODUCT_PRICE_ID']];
                    continue;
                }
                unset($product);
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
        //        $this->before();
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

        $this->catalog_iblock_id = $container->getNewIblock();
        $this->importProducts = $container->getProductsImportResult();
        $import_path = $container->getImportPath();


        $persons = $this->read('personType');
        $importPersonType = ImportPersonType::init($persons)->import();
        $container->setNewPersonsTypeIDS($importPersonType->NewIDS);


        $paySystem = $this->read('paySystem');
        $ImportPaySystem = ImportPaySystem::init($paySystem, $import_path, $importPersonType->NewIDS)->import();
        $container->setNewPaySystemIDS($ImportPaySystem->newPaySystemIDS);

        $users = $this->read('users');
        $container->setUsersImportResult(ImportUsers::init($users)->import()->newIDs);


        $container->setNewDeliveryIDs(ImportDelivery::init()->import()->newIDs);


    }

    public function after()
    {
        $this->orders = [];
    }
}