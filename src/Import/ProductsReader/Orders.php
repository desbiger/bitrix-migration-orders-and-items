<?php

namespace BitrixMigration\Import\ProductsReader;


class Orders extends FilesReader {
    public $folder = '/orders/';
    protected $containerIDsFieldName = 'newOrdersIDs';
}