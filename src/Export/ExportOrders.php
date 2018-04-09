<?php


namespace BitrixMigration\Export;

use BitrixMigration\CLI;

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
        $orders = $this->getList(['USER_ID' => $ID]);
        while ($order = $orders->Fetch()) {
            $order['PRODUCTS'] = $this->getOrderProducts($order['ID']);
            $order['PROPERTIES'] = $this->getOrderProps($order['ID']);
            $res[] = $order;
        }

        return $res;
    }

    public function getAll($perPage = null, callable $callback = null)
    {
        $res = [];
        $orders = $this->getList([]);
        $count = $orders->SelectedRowsCount();
        $counterPerPage = 0;
        $page = 1;
        $i = 0;
        while ($order = $orders->Fetch()) {
            $counterPerPage++;
            CLI::show_status($i++, $count,30," | page: $page");
            $order['PRODUCTS'] = $this->getOrderProducts($order['ID']);
            $order['PROPERTIES'] = $this->getOrderProps($order['ID']);
            $res[] = $order;

            if ($counterPerPage == $perPage && $callback) {
                $counterPerPage = 0;
                $callback($res,$page);
                $page++;
                $res = [];
            }

        }

        if($callback){
            $callback($res,$page);
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
        $list = \CSaleBasket::GetList([], ['ORDER_ID' => $ID]);
        while ($product = $list->Fetch()) {
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

    /**
     * @param $ID
     *
     * @return mixed
     */
    private function getList($filter)
    {
        $orders = \CSaleOrder::GetList([], $filter);

        return $orders;
    }
}