<?php

namespace BitrixMigration;

use BitrixMigration\Import\Container;
use BitrixMigration\Import\ProductsReader\Orders;

class ImportOrders {

    public function export()
    {
        $this->before();
        $this->import();
        $this->after();
    }

    private function before()
    {
    }

    private function import()
    {
        $import_path = Container::instance()->import_path;
        $list = new Orders($import_path . '/orders', $import_path);

        while(list($element,$count,$counter,$file) = $list->getNextElement()){
            dd($element);
        }
    }

    private function after()
    {
    }


}