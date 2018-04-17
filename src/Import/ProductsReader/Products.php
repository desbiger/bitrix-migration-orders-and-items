<?php

namespace BitrixMigration\Import\ProductsReader;


class Products extends FilesReader {
    public $folder = '/products/';
    protected $containerIDsFieldName = 'newProductsIDs';
}