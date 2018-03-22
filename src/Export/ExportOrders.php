<?php


namespace BitrixMigration\Export;


class ExportOrders {

    /**
     * ExportOrders constructor.
     */
    public function __construct()
    {
    }

    /**
     * Получаем список заказов пользователя
     *
     * @param $ID
     *
     * @return array
     */
    public function getUserOrders($ID)
    {
        $res = [];
        $orders = \CSaleOrder::GetList([], ['USER_ID' => $ID]);
        while ($order = $orders->Fetch()) {
            $order['PRODUCTS'] = $this->getOrderProducts($order['ID']);
            $order['PROPERTIES'] = $this->getOrderProps($order['ID']);
            $res[] = $order;
        }

        return $res;
    }

    /**
     * Получаем список товаров заказа
     *
     * @param $ID
     *
     * @return array
     */
    private function getOrderProducts($ID)
    {
        $res = [];
        $list = \CSaleBasket::GetList([],['ORDER_ID' => $ID]);
        while($product = $list->Fetch()){
            $res[] = $product;
        }
        return $res;
    }


    /**
     * Список свойств заказа
     *
     * @param $id
     *
     * @return mixed
     */
    public function getOrderProps($id)
    {
        return \CSaleOrderProps::GetByID($id);
    }
}