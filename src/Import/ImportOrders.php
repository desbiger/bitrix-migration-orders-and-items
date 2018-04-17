<?php

namespace BitrixMigration\Import;


use BitrixMigration\BitrixMigrationHelper;
use BitrixMigration\CLI;
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
    public $siteID;
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
            $this->createOrders($element);

        }

    }

    /**
     * @param $user_orders
     */
    private function createOrders($user_orders)
    {

        $oldID = $user_orders['ID'];
        $newOrdersIDs = Container::instance()->newOrdersIDs;
        if (in_array($oldID, array_keys($newOrdersIDs))) {
            return false;
        }

        $this->updateOrderData($user_orders);
        $newOrderID = $this->createOrder($user_orders);
        if (@$newOrderID) {
            Container::instance()->addNewOrderIDS($oldID, $newOrderID);
            $this->createOrderBasket($user_orders['PRODUCTS'], $newOrderID);
            $this->addOrderProperties($newOrderID, $user_orders['DOP_PROPS']);

            return;
        }

        echo 'Order ' . $user_orders['ID'] . ' not created,   ';

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

        $order['PERSON_TYPE_ID'] = Container::instance()->newPersonsTypeIDS[$order['PERSON_TYPE_ID']];
        $order['DELIVERY_ID'] = Container::instance()->newDeliveryIDs[$order['DELIVERY_ID']];

        unset($order['PRODUCTS']);
        unset($order['PROPERTIES']);
        unset($order['DOP_PROPS']);
        unset($order['ACCOUNT_NUMBER']);

        try{

            $id = $orderObject->Add($order);
        }catch(\Exception $e){
            if($ex = $APPLICATION->GetException())
                  $strError = $ex->GetString();
            echo $strError;
//            dd($order);
        }


        $order = null;
        $orderObject = null;

        return $id;

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

        return;
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

    private function addOrderProperties($newOrderID, $DOP_PROPS)
    {
        $default = [
            'ORDER_ID'       => '',
            'ORDER_PROPS_ID' => '',
            'NAME'           => '',
            'VALUE'          => '',
            'CODE'           => '',
        ];
        foreach ($DOP_PROPS as $prop) {
            $propID = $this->createOrderPropertyIfNotExists($prop);

            $prop['ORDER_PROPS_ID'] = $propID;
            $prop['ORDER_ID'] = $newOrderID;

            $array_replace_recursive = array_replace_recursive($default, $prop);
            $propValue = array_only($array_replace_recursive, array_keys($default));


            \CSaleOrderPropsValue::Add($propValue);

            $array_replace_recursive = null;
            $propValue = null;
        }
        $prop = [];
        $DOP_PROPS = [];
        $default = [];

        return;
    }

    /**
     * @param $property
     *
     * @return mixed
     */
    private function createOrderPropertyIfNotExists($property)
    {
        if ($id = \CSaleOrderProps::GetList([], ['CODE' => $property['CODE']])->Fetch()['ID']) {
            return $id;
        }
        $id = \CSaleOrder::add($property);

        $container = Container::instance();
        $container->addOrderProperty($property['ID'], $id);

        $property = [];

        return $id;

    }

    public function setSiteID($id)
    {
        $this->siteID = $id;
    }
}