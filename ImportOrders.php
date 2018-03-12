<?php

namespace BitrixMigration;

class ImportOrders {

    public function saveToJson($path)
    {
        return file_put_contents($path, json_encode($this->getAll()));
    }

    /**
     * Список всех заказов
     *
     * @return array
     */
    public function getAll()
    {
        $ordersList = \CSaleOrder::GetList();
        while ($order = $ordersList->Fetch()) {
            $order['PROPS'] = $this->orderProps($order['ID']);
            $order['ITEMS'] = $this->orderProducts($order['ID']);
            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * Список свойств заказа
     *
     * @param $id
     *
     * @return mixed
     */
    public function orderProps($id)
    {
        return \CSaleOrderProps::GetByID($id);
    }

    /**
     * Список товаров заказа
     *
     * @param $order_id
     *
     * @return array
     */
    public function orderProducts($order_id)
    {
        $list = \CSaleBasket::GetList([], ['ORDER_ID' => $order_id]);
        while ($item = $list->Fetch()) {
            $products[] = $item;
        }

        return $products;
    }


}